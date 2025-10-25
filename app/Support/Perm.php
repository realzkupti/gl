<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Perm
{
    public static function can(string $menuKey, string $action = 'view'): bool
    {
        if (!Auth::check()) return false;
        if ((Auth::user()->email ?? '') === 'admin@local') return true;

        try {
            $userId = Auth::id();
            $conn = DB::connection('pgsql');

            // 1. ตรวจสอบสิทธิ์รายบุคคลก่อน (user_menu_permissions) - สิทธิ์เฉพาะตัว
            $userPerm = $conn->table('user_menu_permissions as up')
                ->join('menus as m', 'm.id', '=', 'up.menu_id')
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

            // 2. ตรวจสอบสิทธิ์จาก role (role_menu_permissions) - สิทธิ์ตามกลุ่ม
            $roles = $conn->table('user_roles')->where('user_id', $userId)->pluck('role_id');
            if ($roles->isNotEmpty()) {
                $rows = $conn->table('role_menu_permissions as p')
                    ->join('menus as m', 'm.id', '=', 'p.menu_id')
                    ->whereIn('p.role_id', $roles->all())
                    ->where('m.key', $menuKey)
                    ->select('p.can_view','p.can_create','p.can_update','p.can_delete','p.can_export','p.can_approve')
                    ->get();

                foreach ($rows as $row) {
                    $ok = match ($action) {
                        'view' => (bool)($row->can_view ?? false),
                        'create' => (bool)($row->can_create ?? false),
                        'update' => (bool)($row->can_update ?? false),
                        'delete' => (bool)($row->can_delete ?? false),
                        'export' => (bool)($row->can_export ?? false),
                        'approve' => (bool)($row->can_approve ?? false),
                        default => false,
                    };
                    if ($ok) return true;
                }
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    /**
     * Get user accessible menus with permissions, grouped by menu_group from DB
     * @param string|null $menuGroup Filter by menu group key or null for all
     */
    public static function getUserMenus(?string $menuGroup = null): array
    {
        if (!Auth::check()) return [];

        try {
            $userId = Auth::id();
            $isAdmin = (Auth::user()->email ?? '') === 'admin@local';
            $conn = DB::connection('pgsql');

            // ดึงกลุ่มเมนูจากฐานข้อมูล
            $groupQuery = $conn->table('menu_groups')->where('is_active', true)->orderBy('sort_order');
            if ($menuGroup !== null) {
                $groupQuery->where('key', $menuGroup);
            }
            $groups = $groupQuery->get()->keyBy('id'); // key by id สำหรับ join

            if ($isAdmin) {
                // Admin sees all active menus
                $query = $conn->table('menus as m')
                    ->leftJoin('menu_groups as g', 'g.id', '=', 'm.menu_group_id')
                    ->where('m.is_active', true)
                    ->where(function($q) {
                        $q->where('g.is_active', true)
                          ->orWhereNull('g.id');
                    });

                if ($menuGroup !== null) {
                    $query->where('g.key', $menuGroup);
                }

                $menus = $query->select('m.id', 'm.key', 'm.label', 'm.route', 'm.url', 'm.icon', 'm.parent_id', 'm.sort_order', 'g.key as menu_group_key', 'g.label as menu_group_label', 'g.sort_order as group_sort_order')
                    ->orderBy('group_sort_order')
                    ->orderBy('m.sort_order')
                    ->orderBy('m.id')
                    ->get();
            } else {
                // Get user's roles
                $roles = $conn->table('user_roles')->where('user_id', $userId)->pluck('role_id');

                // รวมเมนูจาก 2 แหล่ง: role permissions และ user permissions
                $menuIds = collect();

                // 1. เมนูจาก role permissions
                if ($roles->isNotEmpty()) {
                    $roleMenuIds = $conn->table('role_menu_permissions as p')
                        ->whereIn('p.role_id', $roles->all())
                        ->where('p.can_view', true)
                        ->pluck('p.menu_id');
                    $menuIds = $menuIds->merge($roleMenuIds);
                }

                // 2. เมนูจาก user permissions (สิทธิ์รายบุคคล)
                $userMenuIds = $conn->table('user_menu_permissions as up')
                    ->where('up.user_id', $userId)
                    ->where('up.can_view', true)
                    ->pluck('up.menu_id');
                $menuIds = $menuIds->merge($userMenuIds);

                // Debug log
                Log::info('getUserMenus: Menu IDs collected', [
                    'user_id' => $userId,
                    'role_menu_count' => isset($roleMenuIds) ? $roleMenuIds->count() : 0,
                    'user_menu_count' => $userMenuIds->count(),
                    'total_menu_ids' => $menuIds->unique()->count(),
                    'menu_ids' => $menuIds->unique()->all()
                ]);

                // ถ้าไม่มีเมนูเลย ให้ return ว่าง
                if ($menuIds->isEmpty()) {
                    Log::warning('getUserMenus: No menu IDs found', [
                        'user_id' => $userId,
                        'role_count' => $roles->count(),
                        'role_ids' => $roles->all()
                    ]);
                    return [];
                }

                // ดึงเมนูที่ผู้ใช้มีสิทธิ์ดู
                $query = $conn->table('menus as m')
                    ->leftJoin('menu_groups as g', 'g.id', '=', 'm.menu_group_id')
                    ->whereIn('m.id', $menuIds->unique()->all())
                    ->where('m.is_active', true)
                    ->where(function($q) {
                        $q->where('g.is_active', true)
                          ->orWhereNull('g.id');
                    });

                if ($menuGroup !== null) {
                    $query->where('g.key', $menuGroup);
                }

                $menus = $query->select('m.id', 'm.key', 'm.label', 'm.route', 'm.url', 'm.icon', 'm.parent_id', 'm.sort_order', 'g.key as menu_group_key', 'g.label as menu_group_label', 'g.sort_order as group_sort_order')
                    ->distinct()
                    ->orderBy('group_sort_order')
                    ->orderBy('m.sort_order')
                    ->orderBy('m.id')
                    ->get();

                // Debug log - ดูว่าดึงเมนูได้กี่ตัว
                Log::info('getUserMenus: Menus fetched from DB', [
                    'menu_count' => $menus->count(),
                    'sample_menus' => $menus->take(3)->toArray()
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
                    'menu_group' => $menu->menu_group_label ?? 'เมนู', // ใช้ label จากฐานข้อมูล
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

            // Group by menu_group_label
            $groupedMenus = [];
            foreach ($menuArray as $menu) {
                $group = $menu['menu_group'];
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
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}

