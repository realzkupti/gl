<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        foreach (['roles','user_roles','role_menu_permissions','menus','users'] as $t) {
            if (!$schema->hasTable($t)) return; // required tables missing; skip
        }

        // Ensure admin role
        $roleId = DB::connection('pgsql')->table('roles')->where('name','admin')->value('id');
        if (!$roleId) {
            $roleId = DB::connection('pgsql')->table('roles')->insertGetId([
                'name' => 'admin',
                'description' => 'ผู้ดูแลระบบ',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure menus
        $menus = [
            ['key' => 'cheque', 'label' => 'ระบบเช็ค', 'route' => 'cheque.ui'],
            ['key' => 'trial_balance_plain', 'label' => 'งบทดลอง (ธรรมดา)', 'route' => 'trial-balance.plain'],
            ['key' => 'trial_balance_branch', 'label' => 'งบทดลอง (แยกสาขา)', 'route' => 'trial-balance.branch'],
        ];

        $hasNameTh = $schema->hasColumn('menus','name_th');
        foreach ($menus as $m) {
            $exists = DB::connection('pgsql')->table('menus')->where('key',$m['key'])->exists();
            if (!$exists) {
                $row = [
                    'key' => $m['key'],
                    ($hasNameTh ? 'name_th' : 'label') => $m['label'],
                    'route_name' => $hasNameTh ? $m['route'] : null,
                    'route' => $hasNameTh ? null : $m['route'],
                    'parent_id' => null,
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                DB::connection('pgsql')->table('menus')->insert($row);
            }
        }

        // Map menu ids
        $menuMap = DB::connection('pgsql')->table('menus')->whereIn('key', array_column($menus,'key'))
            ->pluck('id','key');

        // Grant admin all permissions
        foreach ($menuMap as $key => $menuId) {
            $has = DB::connection('pgsql')->table('role_menu_permissions')
                ->where(['role_id'=>$roleId,'menu_id'=>$menuId])->exists();
            $data = [
                'role_id' => $roleId,
                'menu_id' => $menuId,
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'can_export' => true,
                'can_approve' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($has) {
                DB::connection('pgsql')->table('role_menu_permissions')->where([
                    'role_id'=>$roleId,'menu_id'=>$menuId
                ])->update($data);
            } else {
                DB::connection('pgsql')->table('role_menu_permissions')->insert($data);
            }
        }

        // Attach admin user to admin role
        $adminId = DB::connection('pgsql')->table('users')->where('email','admin@local')->value('id');
        if ($adminId) {
            $exists = DB::connection('pgsql')->table('user_roles')->where(['user_id'=>$adminId,'role_id'=>$roleId])->exists();
            if (!$exists) {
                DB::connection('pgsql')->table('user_roles')->insert([
                    'user_id' => $adminId,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // no-op (keeping seeded data)
    }
};

