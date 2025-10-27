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
        $json = $request->input('companies_json');

        // Basic validation: must be valid JSON object
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                return back()->with('error', 'Invalid JSON: root must be an object/map.');
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Invalid JSON: ' . $e->getMessage())->withInput();
        }

        $path = base_path('config/companies.json');
        $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($path, $pretty . PHP_EOL);

        // Reset cache and re-apply current selection
        if (method_exists(CompanyManager::class, 'reset')) {
            CompanyManager::reset();
        }
        CompanyManager::apply(CompanyManager::getSelectedKey());

        return back()->with('status', 'Companies configuration saved.');
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

        $path = base_path('config/companies.json');
        $companiesJson = file_exists($path) ? file_get_contents($path) : "{}";

        // Pass page identifier for active menu state
        $page = 'dashboard';

        return view('tailadmin.pages.dashboard', compact('stats', 'activities', 'page', 'companies', 'selectedCompany', 'companiesJson'));
    }
}
