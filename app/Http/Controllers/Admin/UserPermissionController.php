<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UserPermissionController extends Controller
{
    public function index()
    {
        // Mock data for preview only
        $users = [
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@example.com', 'roles' => ['admin']],
            ['id' => 2, 'name' => 'Manager', 'email' => 'manager@example.com', 'roles' => ['manager']],
            ['id' => 3, 'name' => 'User A', 'email' => 'usera@example.com', 'roles' => ['user']],
        ];
        $roles = [
            ['id' => 1, 'name' => 'admin', 'description' => 'ผู้ดูแลระบบ'],
            ['id' => 2, 'name' => 'manager', 'description' => 'ผู้จัดการ'],
            ['id' => 3, 'name' => 'user', 'description' => 'ผู้ใช้งานทั่วไป'],
        ];
        $menus = [
            ['id' => 1, 'key' => 'trial_balance_plain', 'name' => 'งบทดลอง (แบบธรรมดา)'],
            ['id' => 2, 'key' => 'trial_balance_live', 'name' => 'งบทดลอง (แยกสาขา)'],
            ['id' => 3, 'key' => 'cheque', 'name' => 'ระบบเช็ค'],
        ];
        // permissions matrix mock (role -> menu -> flags)
        $permissions = [
            'admin' => [
                'trial_balance_plain' => ['view'=>true,'create'=>true,'update'=>true,'delete'=>true,'export'=>true,'approve'=>true],
                'trial_balance_live'  => ['view'=>true,'create'=>true,'update'=>true,'delete'=>true,'export'=>true,'approve'=>true],
                'cheque'              => ['view'=>true,'create'=>true,'update'=>true,'delete'=>true,'export'=>true,'approve'=>false],
            ],
            'manager' => [
                'trial_balance_plain' => ['view'=>true,'create'=>false,'update'=>false,'delete'=>false,'export'=>true,'approve'=>true],
                'trial_balance_live'  => ['view'=>true,'create'=>false,'update'=>false,'delete'=>false,'export'=>true,'approve'=>true],
                'cheque'              => ['view'=>true,'create'=>true,'update'=>true,'delete'=>false,'export'=>false,'approve'=>false],
            ],
            'user' => [
                'trial_balance_plain' => ['view'=>true,'create'=>false,'update'=>false,'delete'=>false,'export'=>false,'approve'=>false],
                'trial_balance_live'  => ['view'=>true,'create'=>false,'update'=>false,'delete'=>false,'export'=>false,'approve'=>false],
                'cheque'              => ['view'=>true,'create'=>true,'update'=>true,'delete'=>false,'export'=>false,'approve'=>false],
            ],
        ];

        return view('admin.users', compact('users','roles','menus','permissions'));
    }
}

