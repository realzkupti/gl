<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserPermissionController extends Controller
{
    protected function ensureAdmin()
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $users = User::with('department')->orderBy('name')->get();

        return view('admin.user-permissions', compact('users'));
    }

    public function edit($userId)
    {
        $this->ensureAdmin();

        $user = User::with('department')->findOrFail($userId);

        // Show only active menus in permission editor
        $menus = Menu::where('is_active', true)
            ->with('department')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Get all active companies
        $companies = Company::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Get user's current permissions (user-specific)
        $conn = DB::connection('pgsql');

        $userPermissions = $conn->table('sys_user_menu_permissions')
            ->where('user_id', $userId)
            ->get()
            ->keyBy('menu_id');

        // Get department's permissions (as reference)
        $departmentPermissions = collect();
        if ($user->department_id) {
            $departmentPermissions = $conn->table('sys_department_menu_permissions')
                ->where('department_id', $user->department_id)
                ->get()
                ->keyBy('menu_id');
        }

        // Get user's company access
        $userCompanyAccess = $conn->table('sys_user_menu_company_access')
            ->where('user_id', $userId)
            ->get()
            ->groupBy('menu_id')
            ->map(function ($items) {
                return $items->pluck('company_id')->toArray();
            });

        return view('admin.user-permissions-edit', compact(
            'user',
            'menus',
            'userPermissions',
            'departmentPermissions',
            'companies',
            'userCompanyAccess'
        ));
    }

    public function update(Request $request, $userId)
    {
        $this->ensureAdmin();

        User::findOrFail($userId); // Validate user exists
        $conn = DB::connection('pgsql');

        DB::beginTransaction();
        try {
            // Clear existing user-specific permissions
            $conn->table('sys_user_menu_permissions')->where('user_id', $userId)->delete();

            // Insert new permissions
            $permissionsData = $request->input('permissions', []);

            foreach ($permissionsData as $perm) {
                if (empty($perm['menu_id'])) continue;

                // ถ้าไม่มีสิทธิ์อะไรเลย ข้าม (ใช้สิทธิ์แผนกแทน)
                $hasAnyPermission = !empty($perm['can_view']) ||
                                   !empty($perm['can_create']) ||
                                   !empty($perm['can_update']) ||
                                   !empty($perm['can_delete']) ||
                                   !empty($perm['can_export']) ||
                                   !empty($perm['can_approve']);

                if (!$hasAnyPermission) continue;

                $conn->table('sys_user_menu_permissions')->insert([
                    'user_id' => $userId,
                    'menu_id' => $perm['menu_id'],
                    'can_view' => !empty($perm['can_view']),
                    'can_create' => !empty($perm['can_create']),
                    'can_update' => !empty($perm['can_update']),
                    'can_delete' => !empty($perm['can_delete']),
                    'can_export' => !empty($perm['can_export']),
                    'can_approve' => !empty($perm['can_approve']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Handle company access (menu-specific)
            $conn->table('sys_user_menu_company_access')->where('user_id', $userId)->delete();
            $menuCompanyAccess = $request->input('menu_company_access', []);

            foreach ($menuCompanyAccess as $menuId => $companyIds) {
                if (empty($companyIds)) continue;

                foreach ($companyIds as $companyId) {
                    $conn->table('sys_user_menu_company_access')->insert([
                        'user_id' => $userId,
                        'menu_id' => $menuId,
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Handle general company access (user can access which companies)
            $conn->table('sys_user_company_access')->where('user_id', $userId)->delete();
            $companyAccess = $request->input('company_access', []);

            foreach ($companyAccess as $companyId) {
                $conn->table('sys_user_company_access')->insert([
                    'user_id' => $userId,
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('admin.user-permissions.edit', $userId)
                ->with('status', 'บันทึกสิทธิ์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function reset($userId)
    {
        $this->ensureAdmin();

        $conn = DB::connection('pgsql');

        DB::beginTransaction();
        try {
            // Delete all user-specific permissions
            $conn->table('sys_user_menu_permissions')->where('user_id', $userId)->delete();

            // Delete all company access
            $conn->table('sys_user_menu_company_access')->where('user_id', $userId)->delete();
            $conn->table('sys_user_company_access')->where('user_id', $userId)->delete();

            DB::commit();

            return redirect()->route('admin.user-permissions.edit', $userId)
                ->with('status', 'ล้างสิทธิ์ทั้งหมดแล้ว (ผู้ใช้จะใช้สิทธิ์ตามแผนก)');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}
