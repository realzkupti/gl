<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $conn = DB::connection('pgsql');
        if (!$conn->getSchemaBuilder()->hasTable('menus')) return;
        if (!$conn->getSchemaBuilder()->hasTable('menu_groups')) return;

        $adminGroupId = $conn->table('menu_groups')->where('key','admin')->value('id');
        $now = now();
        $row = [
            'label' => 'จัดการกลุ่มผู้ใช้',
            'route' => 'admin.roles',
            'icon' => 'users',
            'parent_id' => null,
            'sort_order' => 98,
            'is_active' => true,
            'is_system' => false,
            'menu_group_id' => $adminGroupId,
            'updated_at' => $now,
        ];
        $exists = $conn->table('menus')->where('key','admin_roles')->exists();
        if ($exists) {
            $conn->table('menus')->where('key','admin_roles')->update($row);
        } else {
            $row['key'] = 'admin_roles';
            $row['created_at'] = $now;
            $conn->table('menus')->insert($row);
        }
    }

    public function down(): void
    {
        DB::connection('pgsql')->table('menus')->where('key','admin_roles')->delete();
    }
};

