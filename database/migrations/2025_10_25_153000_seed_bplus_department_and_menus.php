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

        // 1. สร้างแผนก Bplus (ถ้ายังไม่มี)
        $bplusDept = $conn->table('sys_departments')
            ->where('key', 'bplus')
            ->first();

        if (!$bplusDept) {
            $bplusDeptId = $conn->table('sys_departments')->insertGetId([
                'key' => 'bplus',
                'label' => 'Bplus',
                'sort_order' => 2,
                'is_active' => true,
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $bplusDeptId = $bplusDept->id;
        }

        // 2. ดึง ID ของแผนกระบบ
        $departments = $conn->table('sys_departments')->pluck('id', 'key');
        $systemDeptId = $departments['system'] ?? $departments['default'] ?? null;

        // 3. เพิ่มเมนู Bplus ทั้งหมด
        $bplusMenus = [
            // งบทดลอง (ย้ายมาจาก accounting) - เมนูหลัก
            [
                'key' => 'bplus_trial_balance',
                'label' => 'งบทดลอง',
                'route' => 'trial-balance',
                'icon' => 'calculator',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => false,
                'department_id' => $bplusDeptId,
                'connection_type' => 'company', // ต้องใช้ company database
            ],
            // ผังบัญชี
            [
                'key' => 'bplus_chart_of_accounts',
                'label' => 'ผังบัญชี',
                'route' => 'bplus.chart-of-accounts',
                'icon' => 'document-text',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => false,
                'department_id' => $bplusDeptId,
                'connection_type' => 'company',
            ],
            // สมุดรายวันทั่วไป
            [
                'key' => 'bplus_general_journal',
                'label' => 'สมุดรายวันทั่วไป',
                'route' => 'bplus.general-journal',
                'icon' => 'book-open',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => false,
                'department_id' => $bplusDeptId,
                'connection_type' => 'company',
            ],
            // สมุดบัญชีแยกประเภท
            [
                'key' => 'bplus_general_ledger',
                'label' => 'สมุดบัญชีแยกประเภท',
                'route' => 'bplus.general-ledger',
                'icon' => 'clipboard-list',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => false,
                'department_id' => $bplusDeptId,
                'connection_type' => 'company',
            ],
            // งบการเงิน
            [
                'key' => 'bplus_financial_statements',
                'label' => 'งบการเงิน',
                'route' => 'bplus.financial-statements',
                'icon' => 'chart-bar',
                'parent_id' => null,
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => false,
                'department_id' => $bplusDeptId,
                'connection_type' => 'company',
            ],
            // รายงาน
            [
                'key' => 'bplus_reports',
                'label' => 'รายงาน',
                'route' => 'bplus.reports',
                'icon' => 'document-report',
                'parent_id' => null,
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => false,
                'department_id' => $bplusDeptId,
                'connection_type' => 'company',
            ],
            // จัดการบริษัท (ย้ายมาจาก admin) - ใช้ pgsql
            [
                'key' => 'bplus_companies',
                'label' => 'การตั้งค่า Bplus',
                'route' => 'admin.companies',
                'icon' => 'cog',
                'parent_id' => null,
                'sort_order' => 7,
                'is_active' => true,
                'is_system' => false,
                'department_id' => $bplusDeptId,
                'connection_type' => 'pgsql', // ใช้ pgsql เพราะเป็นการจัดการ connection
            ],
        ];

        // Insert Bplus menus
        foreach ($bplusMenus as $menu) {
            $conn->table('sys_menus')->updateOrInsert(
                ['key' => $menu['key']],
                array_merge($menu, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 4. ซ่อนเมนูเก่าที่ไม่ใช้แล้ว
        // - trial_balance_plain, trial_balance_branch, trial_balance (เก่า)
        $conn->table('sys_menus')
            ->whereIn('key', ['trial_balance_plain', 'trial_balance_branch', 'trial_balance'])
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $conn = DB::connection('pgsql');

        // ลบเมนู Bplus ทั้งหมด
        $bplusMenuKeys = [
            'bplus_trial_balance',
            'bplus_companies',
            'bplus_chart_of_accounts',
            'bplus_general_journal',
            'bplus_general_ledger',
            'bplus_financial_statements',
            'bplus_reports',
        ];

        $conn->table('sys_menus')->whereIn('key', $bplusMenuKeys)->delete();

        // เปิดเมนูเก่ากลับมา
        $conn->table('sys_menus')
            ->whereIn('key', ['trial_balance_plain', 'trial_balance_branch', 'trial_balance'])
            ->update(['is_active' => true, 'updated_at' => now()]);

        // ลบแผนก Bplus (ถ้าไม่มี user)
        $hasUsers = $conn->table('sys_users')
            ->join('sys_departments', 'sys_users.department_id', '=', 'sys_departments.id')
            ->where('sys_departments.key', 'bplus')
            ->exists();

        if (!$hasUsers) {
            $conn->table('sys_departments')->where('key', 'bplus')->delete();
        }
    }
};
