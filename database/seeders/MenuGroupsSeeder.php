<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'key' => 'default',
                'label' => 'เมนู',
                'sort_order' => 1,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'key' => 'accounting',
                'label' => 'บัญชี',
                'sort_order' => 2,
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'key' => 'demo',
                'label' => 'Demo Components',
                'sort_order' => 3,
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'key' => 'admin',
                'label' => 'ผู้ดูแล',
                'sort_order' => 4,
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($groups as $group) {
            \App\Models\MenuGroup::updateOrCreate(
                ['key' => $group['key']],
                $group
            );
        }
    }
}
