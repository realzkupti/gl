<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Perm
{
    public static function can(string $menuKey, string $action = 'view'): bool
    {
        if (!Auth::check()) return false;
        if ((Auth::user()->email ?? '') === 'admin@local') return true;

        try {
            $userId = Auth::id();
            $conn = DB::connection('pgsql');
            $roles = $conn->table('user_roles')->where('user_id', $userId)->pluck('role_id');
            if ($roles->isEmpty()) return false;

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
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    /**
     * Get user accessible menus with permissions
     * @param string|null $menuGroup Filter by menu group (default, bplus, etc.) or null for all
     */
    public static function getUserMenus(?string $menuGroup = null): array
    {
        if (!Auth::check()) return [];

        try {
            $userId = Auth::id();
            $isAdmin = (Auth::user()->email ?? '') === 'admin@local';
            $conn = DB::connection('pgsql');

            if ($isAdmin) {
                // Admin sees all active menus
                $query = $conn->table('menus')
                    ->where('is_active', true);

                if ($menuGroup !== null) {
                    $query->where('menu_group', $menuGroup);
                }

                $menus = $query->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();
            } else {
                // Get user's roles
                $roles = $conn->table('user_roles')->where('user_id', $userId)->pluck('role_id');
                if ($roles->isEmpty()) return [];

                // Get menus user has at least 'view' permission for
                $query = $conn->table('menus as m')
                    ->join('role_menu_permissions as p', 'p.menu_id', '=', 'm.id')
                    ->whereIn('p.role_id', $roles->all())
                    ->where('m.is_active', true)
                    ->where('p.can_view', true);

                if ($menuGroup !== null) {
                    $query->where('m.menu_group', $menuGroup);
                }

                $menus = $query->select('m.*')
                    ->distinct()
                    ->orderBy('m.sort_order')
                    ->orderBy('m.id')
                    ->get();
            }

            // Build tree structure
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
                    'menu_group' => $menu->menu_group ?? 'default',
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

            return $menuArray;
        } catch (\Throwable $e) {
            return [];
        }
    }
}

