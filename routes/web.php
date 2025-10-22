<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\HomeController;
use App\Services\CompanyManager;

Route::get('/', [HomeController::class, 'index'])->middleware(['company.connection'])->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'company.connection'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Trial balance quick view
    Route::get('trial-balance', function () {
        return view('trial_balance');
    })->middleware(['auth'])->name('trial-balance');
});

// Temporary public access for trial balance (no auth) - useful when you don't have auth/session DB available.
Route::get('trial-balance-open', function () {
    return view('trial_balance');
})->middleware(['company.connection'])->name('trial-balance.open');

// Plain server-rendered trial balance for debugging (no Livewire)
Route::get('trial-balance-plain', [TrialBalanceController::class, 'index'])->middleware(['company.connection'])->name('trial-balance.plain');
// PDF export for trial balance
Route::get('trial-balance-pdf', [TrialBalanceController::class, 'pdf'])->middleware(['company.connection'])->name('trial-balance.pdf');
// Excel export for trial balance
Route::get('trial-balance-excel', [TrialBalanceController::class, 'excel'])->middleware(['company.connection'])->name('trial-balance.excel');

// AJAX detail endpoint for trial balance rows
Route::get('trial-balance-detail', [TrialBalanceController::class, 'detail'])->middleware(['company.connection'])->name('trial-balance.detail');
Route::get('trial-balance-entries', [TrialBalanceController::class, 'entries'])->middleware(['company.connection'])->name('trial-balance.entries');

// Admin/settings endpoints
Route::post('settings/companies', [HomeController::class, 'saveCompanies'])->middleware(['company.connection'])->name('settings.companies.save');

// Helper endpoint for company list (optional)
Route::get('companies.json', function () {
    $companies = CompanyManager::listCompanies();
    $current = CompanyManager::getSelectedKey();
    return response()->json(['data' => $companies, 'current' => $current]);
})->name('companies.json');
