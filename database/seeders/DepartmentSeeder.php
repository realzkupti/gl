<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'key' => 'system',
                'label' => 'เมนูระบบ',
                'sort_order' => 1,
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'key' => 'bplus',
                'label' => 'Bplus',
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
        ];

        foreach ($departments as $dept) {
            \App\Models\Department::updateOrCreate(
                ['key' => $dept['key']],
                $dept
            );
        }
    }
}
