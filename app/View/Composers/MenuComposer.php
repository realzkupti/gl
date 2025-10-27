<?php

namespace App\View\Composers;

use Illuminate\View\View;

class MenuComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $view->with('userMenus', $this->getUserMenus());
    }

    /**
     * Get user's accessible menus grouped by system_type
     */
    protected function getUserMenus()
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $accessibleMenus = $user->getAccessibleMenus();

        // Group by system_type
        $grouped = [];

        $systemMenus = $accessibleMenus->where('system_type', 1);
        if ($systemMenus->count() > 0) {
            $grouped['ระบบ'] = $systemMenus->map(function($menu) {
                return [
                    'key' => $menu->key,
                    'label' => $menu->label,
                    'route' => $menu->route,
                    'url' => $menu->url,
                    'icon' => $menu->icon,
                    'children' => [], // TODO: Add children support later
                ];
            })->values()->toArray();
        }

        $bplusMenus = $accessibleMenus->where('system_type', 2);
        if ($bplusMenus->count() > 0) {
            $grouped['Bplus'] = $bplusMenus->map(function($menu) {
                return [
                    'key' => $menu->key,
                    'label' => $menu->label,
                    'route' => $menu->route,
                    'url' => $menu->url,
                    'icon' => $menu->icon,
                    'children' => [], // TODO: Add children support later
                ];
            })->values()->toArray();
        }

        return $grouped;
    }
}
