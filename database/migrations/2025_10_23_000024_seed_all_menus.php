<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $conn = DB::connection('pgsql');
        $schema = Schema::connection('pgsql');

        // Detect which column names are used in the menus table
        $hasNameTh = $schema->hasColumn('menus', 'name_th');
        $hasRouteName = $schema->hasColumn('menus', 'route_name');

        $menus = [
            // Dashboard & Home
            ['key' => 'home', 'name_th' => 'หน้าหลัก', 'route_name' => 'home', 'sort_order' => 1],
            ['key' => 'dashboard', 'name_th' => 'แดชบอร์ด', 'route_name' => 'tailadmin.dashboard', 'sort_order' => 2],

            // Trial Balance (งบทดลอง)
            ['key' => 'trial_balance_plain', 'name_th' => 'งบทดลอง (ธรรมดา)', 'route_name' => 'trial-balance.plain', 'sort_order' => 10],
            ['key' => 'trial_balance_branch', 'name_th' => 'งบทดลอง (แยกสาขา)', 'route_name' => 'trial-balance.branch', 'sort_order' => 11],
            ['key' => 'trial_balance', 'name_th' => 'งบทดลอง (Livewire)', 'route_name' => 'trial-balance', 'sort_order' => 12],

            // Cheque System
            ['key' => 'cheque', 'name_th' => 'ระบบเช็ค', 'route_name' => 'cheque.ui', 'sort_order' => 20],
            ['key' => 'cheque_print', 'name_th' => 'พิมพ์เช็ค', 'route_name' => 'cheque.print', 'sort_order' => 21],
            ['key' => 'cheque_designer', 'name_th' => 'ออกแบบเช็ค', 'route_name' => 'cheque.designer', 'sort_order' => 22],
            ['key' => 'cheque_reports', 'name_th' => 'รายงานเช็ค', 'route_name' => 'cheque.reports', 'sort_order' => 23],
            ['key' => 'cheque_branches', 'name_th' => 'สาขาเช็ค', 'route_name' => 'cheque.branches', 'sort_order' => 24],
            ['key' => 'cheque_settings', 'name_th' => 'ตั้งค่าเช็ค', 'route_name' => 'cheque.settings', 'sort_order' => 25],

            // Admin & Management
            ['key' => 'admin_menus', 'name_th' => 'จัดการเมนู', 'route_name' => 'admin.menus', 'sort_order' => 100],
            ['key' => 'admin_users', 'name_th' => 'จัดการผู้ใช้', 'route_name' => 'admin.users', 'sort_order' => 101],
            ['key' => 'admin_user_approvals', 'name_th' => 'อนุมัติผู้ใช้', 'route_name' => 'admin.user-approvals', 'sort_order' => 102],
            ['key' => 'admin_user_permissions', 'name_th' => 'กำหนดสิทธิ์รายคน', 'route_name' => 'admin.user-permissions', 'sort_order' => 103],
            ['key' => 'admin_cheque', 'name_th' => 'จัดการเช็ค (Admin)', 'route_name' => 'admin.cheque', 'sort_order' => 104],

            // User Profile
            ['key' => 'profile', 'name_th' => 'โปรไฟล์', 'route_name' => 'profile.edit', 'sort_order' => 200],
            ['key' => 'password', 'name_th' => 'เปลี่ยนรหัสผ่าน', 'route_name' => 'user-password.edit', 'sort_order' => 201],
            ['key' => 'two_factor', 'name_th' => 'ยืนยันตัวตนสองขั้นตอน', 'route_name' => 'two-factor.show', 'sort_order' => 202],
            ['key' => 'appearance', 'name_th' => 'การแสดงผล', 'route_name' => 'appearance.edit', 'sort_order' => 203],

            // TailAdmin Demo Pages
            ['key' => 'tailadmin_analytics', 'name_th' => 'Analytics (Demo)', 'route_name' => 'tailadmin.analytics', 'sort_order' => 300],
            ['key' => 'tailadmin_alerts', 'name_th' => 'Alerts (Demo)', 'route_name' => 'tailadmin.alerts', 'sort_order' => 301],
            ['key' => 'tailadmin_buttons', 'name_th' => 'Buttons (Demo)', 'route_name' => 'tailadmin.buttons', 'sort_order' => 302],
            ['key' => 'tailadmin_cards', 'name_th' => 'Cards (Demo)', 'route_name' => 'tailadmin.cards', 'sort_order' => 303],
            ['key' => 'tailadmin_tables', 'name_th' => 'Tables (Demo)', 'route_name' => 'tailadmin.tables', 'sort_order' => 304],
            ['key' => 'tailadmin_forms', 'name_th' => 'Forms (Demo)', 'route_name' => 'tailadmin.forms', 'sort_order' => 305],
        ];

        foreach ($menus as $menu) {
            // Check if menu already exists
            $exists = $conn->table('menus')->where('key', $menu['key'])->exists();

            if (!$exists) {
                // Build insert array based on available columns
                $row = [
                    'key' => $menu['key'],
                    'parent_id' => $menu['parent_id'] ?? null,
                    'sort_order' => $menu['sort_order'] ?? 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Use name_th or label depending on schema
                if ($hasNameTh) {
                    $row['name_th'] = $menu['name_th'];
                } else {
                    $row['label'] = $menu['name_th'];
                }

                // Use route_name or route depending on schema
                if ($hasRouteName) {
                    $row['route_name'] = $menu['route_name'] ?? null;
                } else {
                    $row['route'] = $menu['route_name'] ?? null;
                }

                $conn->table('menus')->insert($row);
            } else {
                // Update existing menu
                $updateRow = [
                    'sort_order' => $menu['sort_order'] ?? 0,
                    'updated_at' => now(),
                ];

                if ($hasNameTh) {
                    $updateRow['name_th'] = $menu['name_th'];
                } else {
                    $updateRow['label'] = $menu['name_th'];
                }

                if ($hasRouteName) {
                    $updateRow['route_name'] = $menu['route_name'] ?? null;
                } else {
                    $updateRow['route'] = $menu['route_name'] ?? null;
                }

                $conn->table('menus')
                    ->where('key', $menu['key'])
                    ->update($updateRow);
            }
        }
    }

    public function down(): void
    {
        // Optional: remove seeded menus (keep for safety)
    }
};
