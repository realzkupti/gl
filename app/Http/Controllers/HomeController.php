<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyManager;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Redirect to TailAdmin Dashboard (all features now in TailAdmin)
        return redirect()->route('tailadmin.dashboard');
    }

    public function saveCompanies(Request $request)
    {
        // This method is deprecated - companies are now managed via admin/companies page
        return back()->with('error', 'Companies are now managed via the Admin > Companies page.');
    }

    public function dashboardDemo()
    {
        // Redirect to new TailAdmin dashboard
        return redirect()->route('tailadmin.dashboard');
    }

    public function tailadminDashboard()
    {
        // Real stats for summary cards
        $stats = [
            'users_total' => \App\Models\User::count(),
            'users_active' => \App\Models\User::where('is_active', true)->count(),
            'cheques' => \App\Models\Cheque::count(),
            'companies_total' => \App\Models\Company::count(),
            'companies_active' => \App\Models\Company::where('is_active', true)->count(),
        ];

        // Recent activities from logs (latest 10)
        $activities = \App\Models\ActivityLog::with('user')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'user' => $log->user->name ?? 'ระบบ',
                    'action' => $log->action ?? ($log->method.' '.$log->url),
                    'time' => optional($log->created_at)->diffForHumans() ?? '',
                ];
            })->toArray();

        // Company selection data
        $companies = CompanyManager::listCompanies();
        $selectedCompany = CompanyManager::getSelectedKey();

        // Companies are now managed in database, not JSON file
        $companiesJson = json_encode($companies, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Pass page identifier for active menu state
        $page = 'dashboard';

        // Get user's accessible menus grouped by system_type
        $userMenus = $this->getUserMenus();

        return view('tailadmin.pages.dashboard', compact('stats', 'activities', 'page', 'companies', 'selectedCompany', 'companiesJson', 'userMenus'));
    }

    /**
     * Get user's accessible menus grouped by system_type with parent-child support
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

        // Process System menus (system_type = 1)
        $systemMenus = $accessibleMenus->where('system_type', 1);
        if ($systemMenus->count() > 0) {
            $grouped['ระบบ'] = $this->buildMenuTree($systemMenus);
        }

        // Process Bplus menus (system_type = 2)
        $bplusMenus = $accessibleMenus->where('system_type', 2);
        if ($bplusMenus->count() > 0) {
            $grouped['Bplus'] = $this->buildMenuTree($bplusMenus);
        }

        return $grouped;
    }

    /**
     * Build menu tree structure from flat menu collection
     */
    protected function buildMenuTree($menus)
    {
        $tree = [];
        $lookup = [];

        // First pass: create lookup array
        foreach ($menus as $menu) {
            $lookup[$menu->id] = [
                'key' => $menu->key,
                'label' => $menu->label,
                'route' => $menu->route,
                'url' => $menu->url,
                'icon' => $menu->icon,
                'parent_id' => $menu->parent_id,
                'sort_order' => $menu->sort_order,
                'children' => [],
            ];
        }

        // Second pass: build tree
        foreach ($lookup as $id => $item) {
            if ($item['parent_id'] && isset($lookup[$item['parent_id']])) {
                // This is a child menu
                $lookup[$item['parent_id']]['children'][] = $item;
            } else {
                // This is a parent menu
                $tree[] = $lookup[$id];
            }
        }

        // Sort children by sort_order
        foreach ($tree as &$item) {
            if (!empty($item['children'])) {
                usort($item['children'], function($a, $b) {
                    return $a['sort_order'] <=> $b['sort_order'];
                });
            }
        }

        // Sort top-level items by sort_order
        usort($tree, function($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return $tree;
    }
}
