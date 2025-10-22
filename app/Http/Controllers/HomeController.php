<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CompanyManager;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $companies = CompanyManager::listCompanies();
        $selected = CompanyManager::getSelectedKey();

        $path = base_path('config/companies.json');
        $jsonText = file_exists($path) ? file_get_contents($path) : "{}";

        return view('home', [
            'companies' => $companies,
            'selectedCompany' => $selected,
            'companiesJson' => $jsonText,
        ]);
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
}

