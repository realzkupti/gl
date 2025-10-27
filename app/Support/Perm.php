<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Perm
{
    /**
     * Check if user has permission for menu action
     *
     * Priority:
     * 1. Admin override (admin@local)
     * 2. User-specific permissions
     * 3. Department permissions
     * 4. Deny
     *
     * @param string $menuKey Menu key to check
     * @param string $action Action to check (view, create, update, delete, export, approve)
     * @return bool
     */
    public static function can(string $menuKey, string $action = 'view'): bool
    {
        if (!Auth::check()) return false;

        // 1. Admin override
        if ((Auth::user()->email ?? '') === 'admin@local') return true;

        try {
            $userId = Auth::id();
            $conn = DB::connection('pgsql');

            // 2. Check user-specific permissions first (highest priority)
            $userPerm = $conn->table('sys_user_menu_permissions as up')
                ->join('sys_menus as m', 'm.id', '=', 'up.menu_id')
                ->where('up.user_id', $userId)
                ->where('m.key', $menuKey)
                ->select('up.can_view','up.can_create','up.can_update','up.can_delete','up.can_export','up.can_approve')
                ->first();

            if ($userPerm) {
                $ok = match ($action) {
                    'view' => (bool)($userPerm->can_view ?? false),
                    'create' => (bool)($userPerm->can_create ?? false),
                    'update' => (bool)($userPerm->can_update ?? false),
                    'delete' => (bool)($userPerm->can_delete ?? false),
                    'export' => (bool)($userPerm->can_export ?? false),
                    'approve' => (bool)($userPerm->can_approve ?? false),
                    default => false,
                };
                if ($ok) return true;
            }

            // 3. Check department permissions (fallback)
            $user = Auth::user();
            if ($user->department_id) {
                $deptPerm = $conn->table('sys_department_menu_permissions as dp')
                    ->join('sys_menus as m', 'm.id', '=', 'dp.menu_id')
                    ->where('dp.department_id', $user->department_id)
                    ->where('m.key', $menuKey)
                    ->select('dp.can_view','dp.can_create','dp.can_update','dp.can_delete','dp.can_export','dp.can_approve')
                    ->first();

                if ($deptPerm) {
                    $ok = match ($action) {
                        'view' => (bool)($deptPerm->can_view ?? false),
                        'create' => (bool)($deptPerm->can_create ?? false),
                        'update' => (bool)($deptPerm->can_update ?? false),
                        'delete' => (bool)($deptPerm->can_delete ?? false),
                        'export' => (bool)($deptPerm->can_export ?? false),
                        'approve' => (bool)($deptPerm->can_approve ?? false),
                        'default' => false,
                    };
                    if ($ok) return true;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Perm::can exception', [
                'menu_key' => $menuKey,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return false;
        }

        return false;
    }

    /**
     * Get user accessible menus with permissions, grouped by department
     *
     * @param string|null $departmentKey Filter by department key or null for all
     * @return array Grouped menus by department
     */
    public static function getUserMenus(?string $departmentKey = null): array
    {
        if (!Auth::check()) return [];

        try {
            $userId = Auth::id();
            $user = Auth::user();
            $isAdmin = ($user->email ?? '') === 'admin@local';
            $conn = DB::connection('pgsql');

            // ดึงแผนกจากฐานข้อมูล
            $deptQuery = $conn->table('sys_departments')->where('is_active', true)->orderBy('sort_order');
            if ($departmentKey !== null) {
                $deptQuery->where('key', $departmentKey);
            }
            $departments = $deptQuery->get()->keyBy('id');

            if ($isAdmin) {
                // Admin sees all active menus
                $menus = $conn->table('sys_menus as m')
                    ->where('m.is_active', true)
                    ->select(
                        'm.id', 'm.key', 'm.label', 'm.route', 'm.url', 'm.icon',
                        'm.parent_id', 'm.sort_order', 'm.connection_type', 'm.system_type',
                        DB::raw("CASE WHEN m.system_type = 1 THEN 'ระบบ' WHEN m.system_type = 2 THEN 'Bplus' ELSE 'เมนู' END as department_label"),
                        DB::raw("CASE WHEN m.system_type = 1 THEN 'system' WHEN m.system_type = 2 THEN 'bplus' ELSE 'default' END as department_key"),
                        DB::raw("m.system_type as dept_sort_order")
                    )
                    ->orderBy('m.system_type')
                    ->orderBy('m.sort_order')
                    ->orderBy('m.id')
                    ->get();
            } else {
                // รวมเมนูจาก 2 แหล่ง: department permissions และ user permissions
                $menuIds = collect();

                // 1. เมนูจาก department permissions
                if ($user->department_id) {
                    $deptMenuIds = $conn->table('sys_department_menu_permissions as dp')
                        ->where('dp.department_id', $user->department_id)
                        ->where('dp.can_view', true)
                        ->pluck('dp.menu_id');
                    $menuIds = $menuIds->merge($deptMenuIds);
                }

                // 2. เมนูจาก user permissions (สิทธิ์รายบุคคล - override)
                $userMenuIds = $conn->table('sys_user_menu_permissions as up')
                    ->where('up.user_id', $userId)
                    ->where('up.can_view', true)
                    ->pluck('up.menu_id');
                $menuIds = $menuIds->merge($userMenuIds);

                // Debug log
                Log::info('getUserMenus: Menu IDs collected', [
                    'user_id' => $userId,
                    'department_id' => $user->department_id,
                    'dept_menu_count' => isset($deptMenuIds) ? $deptMenuIds->count() : 0,
                    'user_menu_count' => $userMenuIds->count(),
                    'total_menu_ids' => $menuIds->unique()->count(),
                ]);

                // ถ้าไม่มีเมนูเลย ให้ return ว่าง
                if ($menuIds->isEmpty()) {
                    Log::warning('getUserMenus: No menu IDs found', [
                        'user_id' => $userId,
                        'department_id' => $user->department_id,
                    ]);
                    return [];
                }

                // ดึงเมนูที่ผู้ใช้มีสิทธิ์ดู
                $menus = $conn->table('sys_menus as m')
                    ->whereIn('m.id', $menuIds->unique()->all())
                    ->where('m.is_active', true)
                    ->select(
                        'm.id', 'm.key', 'm.label', 'm.route', 'm.url', 'm.icon',
                        'm.parent_id', 'm.sort_order', 'm.connection_type', 'm.system_type',
                        DB::raw("CASE WHEN m.system_type = 1 THEN 'ระบบ' WHEN m.system_type = 2 THEN 'Bplus' ELSE 'เมนู' END as department_label"),
                        DB::raw("CASE WHEN m.system_type = 1 THEN 'system' WHEN m.system_type = 2 THEN 'bplus' ELSE 'default' END as department_key"),
                        DB::raw("m.system_type as dept_sort_order")
                    )
                    ->distinct()
                    ->orderBy('m.system_type')
                    ->orderBy('m.sort_order')
                    ->orderBy('m.id')
                    ->get();

                // Debug log
                Log::info('getUserMenus: Menus fetched from DB', [
                    'menu_count' => $menus->count(),
                ]);
            }

            // Build tree structure first
            $menuArray = [];
            $lookup = [];

            foreach ($menus as $menu) {
                $menuItem = [
                    'id' => $menu->id,
                    'key' => $menu->key,
                    'label' => $menu->label,
                    'route' => $menu->route,
                    'url' => $menu->url,
                    'icon' => $menu->icon,
                    'parent_id' => $menu->parent_id,
                    'sort_order' => $menu->sort_order,
                    'connection_type' => $menu->connection_type ?? 'pgsql',
                    'department' => $menu->department_label ?? 'เมนู',
                    'department_key' => $menu->department_key ?? 'default',
                    'children' => [],
                ];
                $lookup[$menu->id] = $menuItem;
            }

            foreach ($lookup as $id => $item) {
                if ($item['parent_id'] && isset($lookup[$item['parent_id']])) {
                    $lookup[$item['parent_id']]['children'][] = &$lookup[$id];
                } else {
                    $menuArray[] = &$lookup[$id];
                }
            }

            // Group by department
            $groupedMenus = [];
            foreach ($menuArray as $menu) {
                $group = $menu['department'];
                if (!isset($groupedMenus[$group])) {
                    $groupedMenus[$group] = [];
                }
                $groupedMenus[$group][] = $menu;
            }

            return $groupedMenus;
        } catch (\Throwable $e) {
            Log::error('getUserMenus: Exception occurred', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return [];
        }
    }
}
