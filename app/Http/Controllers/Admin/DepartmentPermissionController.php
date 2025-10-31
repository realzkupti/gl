<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentMenuPermission;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentPermissionController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index()
    {
        $departments = Department::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.department-permissions', compact('departments'));
    }

    /**
     * Show the form for editing department permissions
     */
    public function edit($departmentId)
    {
        $department = Department::findOrFail($departmentId);

        // ดึงเมนูทั้งหมด
        $menus = Menu::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // ดึงสิทธิ์ที่มีอยู่ของแผนก
        $permissions = DepartmentMenuPermission::where('department_id', $departmentId)
            ->get()
            ->keyBy('menu_id');

        // ดึงข้อมูล company เพื่อแสดง company access
        $companies = DB::connection('pgsql')
            ->table('sys_companies')
            ->where('is_active', true)
            ->orderBy('key')
            ->get();

        return view('admin.department-permissions-edit', compact('department', 'menus', 'permissions', 'companies'));
    }

    /**
     * Update department permissions
     */
    public function update(Request $request, $departmentId)
    {
        $department = Department::findOrFail($departmentId);

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*.menu_id' => 'required|exists:sys_menus,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_update' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
            'permissions.*.can_export' => 'boolean',
            'permissions.*.can_approve' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->permissions as $perm) {
                $menuId = $perm['menu_id'];

                // ถ้าไม่มีสิทธิ์อะไรเลย ให้ลบ record
                if (
                    empty($perm['can_view']) &&
                    empty($perm['can_create']) &&
                    empty($perm['can_update']) &&
                    empty($perm['can_delete']) &&
                    empty($perm['can_export']) &&
                    empty($perm['can_approve'])
                ) {
                    DepartmentMenuPermission::where('department_id', $departmentId)
                        ->where('menu_id', $menuId)
                        ->delete();
                    continue;
                }

                // Update or create permission
                DepartmentMenuPermission::updateOrCreate(
                    [
                        'department_id' => $departmentId,
                        'menu_id' => $menuId,
                    ],
                    [
                        'can_view' => $perm['can_view'] ?? false,
                        'can_create' => $perm['can_create'] ?? false,
                        'can_update' => $perm['can_update'] ?? false,
                        'can_delete' => $perm['can_delete'] ?? false,
                        'can_export' => $perm['can_export'] ?? false,
                        'can_approve' => $perm['can_approve'] ?? false,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.department-permissions.index')
                ->with('success', "สิทธิ์ของแผนก {$department->label} ถูกอัปเดตแล้ว");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Reset all permissions for a department
     */
    public function reset($departmentId)
    {
        $department = Department::findOrFail($departmentId);

        DepartmentMenuPermission::where('department_id', $departmentId)->delete();

        return redirect()
            ->route('admin.department-permissions.index')
            ->with('success', "สิทธิ์ของแผนก {$department->label} ถูกรีเซ็ตแล้ว");
    }
}
