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
        if (!Auth::check() || Auth::user()->email !== 'admin@local') {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $users = User::orderBy('name')->get();

        return view('admin.user-permissions', compact('users'));
    }

    public function edit($userId)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($userId);

        // Get all active menus from database
        $menus = Menu::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Get all active companies
        $companies = Company::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Get user's current permissions
        $conn = DB::connection('pgsql');

        // First get user's roles
        $userRoles = $conn->table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id');

        // Get all permissions for these roles
        $permissions = collect();
        if ($userRoles->isNotEmpty()) {
            $rolePermissions = $conn->table('role_menu_permissions')
                ->whereIn('role_id', $userRoles)
                ->get();

            foreach ($rolePermissions as $perm) {
                $permissions->put($perm->menu_id, $perm);
            }
        }

        // Get user's BPLUS company access
        $userCompanyAccess = $conn->table('user_menu_company_access')
            ->where('user_id', $userId)
            ->get()
            ->groupBy('menu_id')
            ->map(function ($items) {
                return $items->pluck('company_id')->toArray();
            });

        return view('admin.user-permissions-edit', compact('user', 'menus', 'permissions', 'companies', 'userCompanyAccess'));
    }

    public function update(Request $request, $userId)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($userId);
        $conn = DB::connection('pgsql');

        // Get user's role (we'll use first role for simplicity, or create one if doesn't exist)
        $userRole = $conn->table('user_roles')->where('user_id', $userId)->first();

        if (!$userRole) {
            // Create a personal role for this user
            $roleId = $conn->table('roles')->insertGetId([
                'name' => 'user_' . $userId,
                'description' => 'Personal role for ' . $user->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $conn->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $userRole = (object)['role_id' => $roleId];
        }

        $roleId = $userRole->role_id;

        // Clear existing permissions for this role
        $conn->table('role_menu_permissions')->where('role_id', $roleId)->delete();

        // Insert new permissions
        $permissionsData = $request->input('permissions', []);

        foreach ($permissionsData as $perm) {
            if (empty($perm['menu_id'])) continue;

            $conn->table('role_menu_permissions')->insert([
                'role_id' => $roleId,
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

        // Handle BPLUS company access
        $conn->table('user_menu_company_access')->where('user_id', $userId)->delete();
        $bplusCompanyAccess = $request->input('bplus_company_access', []);

        foreach ($bplusCompanyAccess as $menuId => $companyIds) {
            if (empty($companyIds)) continue;

            foreach ($companyIds as $companyId) {
                $conn->table('user_menu_company_access')->insert([
                    'user_id' => $userId,
                    'menu_id' => $menuId,
                    'company_id' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.user-permissions.edit', $userId)
            ->with('status', 'บันทึกสิทธิ์เรียบร้อยแล้ว');
    }

    public function reset($userId)
    {
        $this->ensureAdmin();

        $conn = DB::connection('pgsql');

        // Get user's roles
        $userRoles = $conn->table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id');

        if ($userRoles->isNotEmpty()) {
            // Delete all permissions for these roles
            $conn->table('role_menu_permissions')
                ->whereIn('role_id', $userRoles)
                ->delete();
        }

        return redirect()->route('admin.user-permissions.edit', $userId)
            ->with('status', 'ล้างสิทธิ์ทั้งหมดแล้ว');
    }
}

