<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $conn = DB::connection('pgsql');

        // 1. สร้างกลุ่มเมนู Bplus
        $bplusGroupId = $conn->table('menu_groups')->insertGetId([
            'key' => 'bplus',
            'label' => 'Bplus',
            'sort_order' => 5,
            'is_active' => true,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. ดึง ID ของกลุ่มเมนู Admin เพื่อหา admin_companies
        $groups = $conn->table('menu_groups')->pluck('id', 'key');
        $adminGroupId = $groups['admin'] ?? null;

        // 3. เพิ่มเมนู Bplus ทั้งหมด
        $bplusMenus = [
            // Parent menu - Bplus Dashboard
            [
                'key' => 'bplus_dashboard',
                'label' => 'Bplus Dashboard',
                'route' => 'bplus.dashboard',
                'icon' => 'home',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // จัดการบริษัท (ย้ายจาก admin)
            [
                'key' => 'bplus_companies',
                'label' => 'จัดการบริษัท',
                'route' => 'admin.companies',
                'icon' => 'building',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // ผังบัญชี
            [
                'key' => 'bplus_chart_of_accounts',
                'label' => 'ผังบัญชี',
                'route' => 'bplus.chart-of-accounts',
                'icon' => 'document-text',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // สมุดรายวันทั่วไป
            [
                'key' => 'bplus_general_journal',
                'label' => 'สมุดรายวันทั่วไป',
                'route' => 'bplus.general-journal',
                'icon' => 'book-open',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // สมุดบัญชีแยกประเภท
            [
                'key' => 'bplus_general_ledger',
                'label' => 'สมุดบัญชีแยกประเภท',
                'route' => 'bplus.general-ledger',
                'icon' => 'clipboard-list',
                'parent_id' => null,
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // งบทดลอง (ย้ายมาจาก accounting)
            [
                'key' => 'bplus_trial_balance',
                'label' => 'งบทดลอง',
                'route' => 'trial-balance',
                'icon' => 'calculator',
                'parent_id' => null,
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // งบการเงิน
            [
                'key' => 'bplus_financial_statements',
                'label' => 'งบการเงิน',
                'route' => 'bplus.financial-statements',
                'icon' => 'chart-bar',
                'parent_id' => null,
                'sort_order' => 7,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // รายงาน
            [
                'key' => 'bplus_reports',
                'label' => 'รายงาน',
                'route' => 'bplus.reports',
                'icon' => 'document-report',
                'parent_id' => null,
                'sort_order' => 8,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
            // ตั้งค่าระบบบัญชี
            [
                'key' => 'bplus_settings',
                'label' => 'ตั้งค่าระบบบัญชี',
                'route' => 'bplus.settings',
                'icon' => 'cog',
                'parent_id' => null,
                'sort_order' => 9,
                'is_active' => true,
                'is_system' => false,
                'menu_group_id' => $bplusGroupId,
            ],
        ];

        // Insert Bplus menus
        foreach ($bplusMenus as $menu) {
            $conn->table('menus')->updateOrInsert(
                ['key' => $menu['key']],
                array_merge($menu, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 4. Update admin_companies menu to be hidden or mark as legacy
        // เราจะเก็บ admin_companies ไว้เพื่อไม่ให้ route เสีย แต่ซ่อนไว้
        $conn->table('menus')
            ->where('key', 'admin_companies')
            ->update([
                'is_active' => false, // ซ่อนเมนูนี้
                'updated_at' => now(),
            ]);

        // 5. ถ้าไม่มี admin_companies ให้สร้างขึ้นมาแต่ปิดไว้ (เพื่อความปลอดภัย)
        $hasAdminCompanies = $conn->table('menus')->where('key', 'admin_companies')->exists();
        if (!$hasAdminCompanies && $adminGroupId) {
            $conn->table('menus')->insert([
                'key' => 'admin_companies',
                'label' => 'จัดการบริษัท (Legacy)',
                'route' => 'admin.companies',
                'icon' => 'building',
                'parent_id' => null,
                'sort_order' => 105,
                'is_active' => false, // ปิดไว้
                'is_system' => false,
                'menu_group_id' => $adminGroupId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $conn = DB::connection('pgsql');

        // ลบเมนู Bplus ทั้งหมด
        $bplusMenuKeys = [
            'bplus_dashboard',
            'bplus_companies',
            'bplus_chart_of_accounts',
            'bplus_general_journal',
            'bplus_general_ledger',
            'bplus_trial_balance',
            'bplus_financial_statements',
            'bplus_reports',
            'bplus_settings',
        ];

        $conn->table('menus')->whereIn('key', $bplusMenuKeys)->delete();

        // เปิด admin_companies กลับมา
        $conn->table('menus')
            ->where('key', 'admin_companies')
            ->update(['is_active' => true, 'updated_at' => now()]);

        // ลบกลุ่มเมนู Bplus
        $conn->table('menu_groups')->where('key', 'bplus')->delete();
    }
};
