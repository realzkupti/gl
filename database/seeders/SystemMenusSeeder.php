<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemMenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seed เมนูระบบ (pgsql) - เช็ค, จัดการผู้ใช้, จัดการสิทธิ์
     */
    public function run(): void
    {
        $conn = DB::connection('pgsql');

        // ใช้ system_type แทน department_id
        // 1 = System, 2 = Bplus, 3 = Admin, 4 = User
        $systemType = 1; // System

        $systemMenus = [
            // แดชบอร์ด
            [
                'key' => 'dashboard',
                'label' => 'แดชบอร์ด',
                'route' => 'tailadmin.dashboard',
                'icon' => 'home',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // ระบบเช็ค
            [
                'key' => 'cheque',
                'label' => 'ระบบเช็ค',
                'route' => 'cheque.ui',
                'icon' => 'document-text',
                'parent_id' => null,
                'sort_order' => 10,
                'is_active' => true,
                'is_system' => false,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // จัดการเมนู
            [
                'key' => 'admin_menus',
                'label' => 'จัดการเมนู',
                'route' => 'admin.menus',
                'icon' => 'menu',
                'parent_id' => null,
                'sort_order' => 100,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // จัดการแผนก
            [
                'key' => 'admin_departments',
                'label' => 'จัดการแผนก',
                'route' => 'admin.departments.index',
                'icon' => 'user-group',
                'parent_id' => null,
                'sort_order' => 101,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // จัดการผู้ใช้
            [
                'key' => 'admin_users',
                'label' => 'จัดการผู้ใช้',
                'route' => 'admin.users',
                'icon' => 'users',
                'parent_id' => null,
                'sort_order' => 102,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // อนุมัติผู้ใช้
            [
                'key' => 'admin_user_approvals',
                'label' => 'อนุมัติผู้ใช้',
                'route' => 'admin.user-approvals',
                'icon' => 'check-circle',
                'parent_id' => null,
                'sort_order' => 103,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // กำหนดสิทธิ์รายคน
            [
                'key' => 'admin_user_permissions',
                'label' => 'กำหนดสิทธิ์รายคน',
                'route' => 'admin.user-permissions',
                'icon' => 'key',
                'parent_id' => null,
                'sort_order' => 104,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // กำหนดสิทธิ์แผนก
            [
                'key' => 'admin_department_permissions',
                'label' => 'กำหนดสิทธิ์แผนก',
                'route' => 'admin.department-permissions.index',
                'icon' => 'shield-check',
                'parent_id' => null,
                'sort_order' => 105,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // จัดการบริษัท
            [
                'key' => 'admin_companies',
                'label' => 'จัดการบริษัท',
                'route' => 'admin.companies',
                'icon' => 'office-building',
                'parent_id' => null,
                'sort_order' => 106,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // Activity Log
            [
                'key' => 'admin_activity_logs',
                'label' => 'Activity Log',
                'route' => 'admin.activity-logs',
                'icon' => 'clipboard-list',
                'parent_id' => null,
                'sort_order' => 107,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // Financial Dashboard
            [
                'key' => 'financial_dashboard',
                'label' => 'Financial Dashboard',
                'route' => 'financial.dashboard',
                'icon' => 'chart-bar',
                'parent_id' => null,
                'sort_order' => 108,
                'is_active' => true,
                'is_system' => true,
                'system_type' => 2, // Bplus system type
                'connection_type' => 'sqlsrv', // Uses company database
            ],
            // โปรไฟล์
            [
                'key' => 'profile',
                'label' => 'โปรไฟล์',
                'route' => 'profile.edit',
                'icon' => 'user',
                'parent_id' => null,
                'sort_order' => 200,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
            // เปลี่ยนรหัสผ่าน
            [
                'key' => 'password',
                'label' => 'เปลี่ยนรหัสผ่าน',
                'route' => 'user-password.edit',
                'icon' => 'lock-closed',
                'parent_id' => null,
                'sort_order' => 201,
                'is_active' => true,
                'is_system' => true,
                'system_type' => $systemType,
                'connection_type' => 'pgsql',
            ],
        ];

        foreach ($systemMenus as $menu) {
            $conn->table('sys_menus')->updateOrInsert(
                ['key' => $menu['key']],
                array_merge($menu, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ เพิ่มเมนูระบบเรียบร้อยแล้ว');
    }
}
