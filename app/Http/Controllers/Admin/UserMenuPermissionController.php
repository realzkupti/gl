<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Menu;
use App\Models\UserMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMenuPermissionController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (!Auth::check() || (Auth::user()->email ?? '') !== 'admin@local') {
            abort(403, 'Forbidden');
        }
    }

    /**
     * Show user permission management page
     */
    public function index()
    {
        $this->ensureAdmin();

        $users = User::orderBy('name')->get();
        return view('admin.user-permissions', compact('users'));
    }

    /**
     * Show permissions for a specific user
     */
    public function show($userId)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($userId);
        $menus = Menu::orderBy('sort_order')->orderBy('id')->get();

        // Get existing user permissions
        $userPermissions = UserMenuPermission::where('user_id', $userId)
            ->get()
            ->keyBy('menu_id');

        return view('admin.user-permissions-edit', compact('user', 'menus', 'userPermissions'));
    }

    /**
     * Update user permissions
     */
    public function update(Request $request, $userId)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($userId);

        $data = $request->validate([
            'permissions' => 'array',
            'permissions.*.menu_id' => 'required|integer|exists:pgsql.menus,id',
            'permissions.*.can_view' => 'boolean',
            'permissions.*.can_create' => 'boolean',
            'permissions.*.can_update' => 'boolean',
            'permissions.*.can_delete' => 'boolean',
            'permissions.*.can_export' => 'boolean',
            'permissions.*.can_approve' => 'boolean',
        ]);

        // Clear existing permissions for this user
        UserMenuPermission::where('user_id', $userId)->delete();

        // Insert new permissions
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            foreach ($data['permissions'] as $perm) {
                // Only create if at least one permission is granted
                if (($perm['can_view'] ?? false) ||
                    ($perm['can_create'] ?? false) ||
                    ($perm['can_update'] ?? false) ||
                    ($perm['can_delete'] ?? false) ||
                    ($perm['can_export'] ?? false) ||
                    ($perm['can_approve'] ?? false)) {

                    UserMenuPermission::create([
                        'user_id' => $userId,
                        'menu_id' => $perm['menu_id'],
                        'can_view' => $perm['can_view'] ?? false,
                        'can_create' => $perm['can_create'] ?? false,
                        'can_update' => $perm['can_update'] ?? false,
                        'can_delete' => $perm['can_delete'] ?? false,
                        'can_export' => $perm['can_export'] ?? false,
                        'can_approve' => $perm['can_approve'] ?? false,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.user-permissions.show', $userId)
            ->with('status', 'อัปเดตสิทธิ์ผู้ใช้เรียบร้อยแล้ว');
    }

    /**
     * Reset user permissions (remove all)
     */
    public function reset($userId)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($userId);
        UserMenuPermission::where('user_id', $userId)->delete();

        return redirect()
            ->route('admin.user-permissions.show', $userId)
            ->with('status', 'ล้างสิทธิ์ผู้ใช้เรียบร้อยแล้ว');
    }
}
