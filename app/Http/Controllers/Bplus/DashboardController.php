<?php

namespace App\Http\Controllers\Bplus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the Bplus Dashboard
     */
    public function index()
    {
        $company = auth()->user()?->getCurrentCompany();

        // Log for debugging
        \Log::info('Bplus Dashboard accessed', [
            'user_id' => auth()->id(),
            'company_id' => $company?->id,
            'company_name' => $company?->label,
        ]);

        return view('bplus.dashboard', compact('company'));
    }
}
