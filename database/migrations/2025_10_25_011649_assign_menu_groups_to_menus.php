<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $conn = DB::connection('pgsql');

        // Get group IDs
        $groups = $conn->table('menu_groups')->pluck('id', 'key');

        // Assign groups to menus
        $assignments = [
            'default' => ['home', 'dashboard', 'profile', 'password', 'two_factor', 'appearance'],
            'accounting' => ['trial_balance_plain', 'trial_balance_branch', 'trial_balance'],
            'demo' => ['tailadmin_analytics', 'tailadmin_alerts', 'tailadmin_buttons', 'tailadmin_cards', 'tailadmin_tables', 'tailadmin_forms'],
            'admin' => ['admin_menus', 'admin_users', 'admin_user_approvals', 'admin_user_permissions', 'admin_cheque'],
        ];

        // Also assign cheque menus to default for now
        $assignments['default'] = array_merge($assignments['default'], ['cheque', 'cheque_print', 'cheque_designer', 'cheque_reports', 'cheque_branches', 'cheque_settings']);

        foreach ($assignments as $groupKey => $menuKeys) {
            if (isset($groups[$groupKey])) {
                $conn->table('menus')
                    ->whereIn('key', $menuKeys)
                    ->update(['menu_group_id' => $groups[$groupKey]]);
            }
        }

        // Add menu group management menu
        $adminGroupId = $groups['admin'] ?? null;
        if ($adminGroupId) {
            $conn->table('menus')->updateOrInsert(
                ['key' => 'admin_menu_groups'],
                [
                    'label' => 'จัดการกลุ่มเมนู',
                    'route' => 'admin.menu-groups.index',
                    'icon' => 'settings',
                    'parent_id' => null,
                    'sort_order' => 99,
                    'is_active' => true,
                    'is_system' => false,
                    'menu_group_id' => $adminGroupId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Assign default group to any remaining menus without a group
        $defaultGroupId = $groups['default'] ?? null;
        if ($defaultGroupId) {
            $conn->table('menus')->whereNull('menu_group_id')->update(['menu_group_id' => $defaultGroupId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the menu group management menu
        DB::connection('pgsql')->table('menus')->where('key', 'admin_menu_groups')->delete();

        // Reset menu_group_id to null
        DB::connection('pgsql')->table('menus')->update(['menu_group_id' => null]);
    }
};
