<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Menu;
use App\Models\DepartmentMenuPermission;

class DepartmentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing permissions
        DepartmentMenuPermission::truncate();

        // Admin department (ID: 3) → เห็นทุกเมนู
        $adminDept = Department::find(3);
        if ($adminDept) {
            $allMenus = Menu::all();

            foreach ($allMenus as $menu) {
                DepartmentMenuPermission::create([
                    'department_id' => $adminDept->id,
                    'menu_id' => $menu->id,
                ]);
            }

            $this->command->info("✓ Admin department: {$allMenus->count()} menus granted");
        }

        // User department (ID: 4) → เห็นแค่ Bplus Dashboard
        $userDept = Department::find(4);
        if ($userDept) {
            $bplusDashboard = Menu::where('key', 'bplus_dashboard')->first();

            if ($bplusDashboard) {
                DepartmentMenuPermission::create([
                    'department_id' => $userDept->id,
                    'menu_id' => $bplusDashboard->id,
                ]);

                $this->command->info("✓ User department: 1 menu granted (Bplus Dashboard)");
            } else {
                $this->command->warn("⚠ Bplus Dashboard menu not found. User department has no permissions.");
            }
        }

        $this->command->info("✓ Department permissions seeded successfully!");
    }
}
