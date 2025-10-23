<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChequeApiController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Services\CompanyManager;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TailAdminController;

Route::get('/', [HomeController::class, 'index'])->middleware(['company.connection'])->name('home');

// Admin demo dashboard (redirects to new TailAdmin)
Route::get('admin/dashboard-demo', [HomeController::class, 'dashboardDemo'])->middleware(['company.connection'])->name('admin.dashboard.demo');

// TailAdmin Pages
Route::prefix('tailadmin')->name('tailadmin.')->middleware(['company.connection'])->group(function () {
    Route::get('dashboard', [HomeController::class, 'tailadminDashboard'])->name('dashboard');
    Route::get('analytics', [TailAdminController::class, 'analytics'])->name('analytics');
    Route::get('alerts', [TailAdminController::class, 'alerts'])->name('alerts');
    Route::get('buttons', [TailAdminController::class, 'buttons'])->name('buttons');
    Route::get('cards', [TailAdminController::class, 'cards'])->name('cards');
    Route::get('tables', [TailAdminController::class, 'tables'])->name('tables');
    Route::get('forms', [TailAdminController::class, 'forms'])->name('forms');

    // Original HTML demo (deprecated)
    Route::get('demo-html', [TailAdminController::class, 'index'])->name('demo.html');
});

// Cheque System Pages
Route::prefix('cheque')->name('cheque.')->middleware(['company.connection'])->group(function () {
    Route::get('print', [TailAdminController::class, 'chequePrint'])->name('print');
    Route::get('designer', [TailAdminController::class, 'chequeDesigner'])->name('designer');
    Route::get('reports', [TailAdminController::class, 'chequeReports'])->name('reports');
    Route::get('branches', [TailAdminController::class, 'chequeBranches'])->name('branches');
    Route::get('settings', [TailAdminController::class, 'chequeSettings'])->name('settings');
});

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

// New: Branch trial balance (Blade + JS)
Route::get('trial-balance-branch', [TrialBalanceController::class, 'branch'])->middleware(['company.connection'])->name('trial-balance.branch');
Route::get('trial-balance-branch-data', [TrialBalanceController::class, 'branchData'])->middleware(['company.connection'])->name('trial-balance.branch-data');

// Admin/settings endpoints
Route::post('settings/companies', [HomeController::class, 'saveCompanies'])->middleware(['company.connection'])->name('settings.companies.save');

// Helper endpoint for company list (optional)
Route::get('companies.json', function () {
    $companies = CompanyManager::listCompanies();
    $current = CompanyManager::getSelectedKey();
    return response()->json(['data' => $companies, 'current' => $current]);
})->name('companies.json');

// Cheque UI + minimal API migrated to Laravel
Route::get('cheque', [ChequeApiController::class, 'ui'])->name('cheque.ui');
Route::get('cheque/styles.css', [ChequeApiController::class, 'css'])->name('cheque.css');
Route::get('api/branches', [ChequeApiController::class, 'branches']);
Route::get('api/cheques', [ChequeApiController::class, 'chequesIndex']);
Route::post('api/cheques', [ChequeApiController::class, 'chequesStore']);
Route::delete('api/cheques/{id}', [ChequeApiController::class, 'chequesDestroy']);
Route::get('api/cheques/next', [ChequeApiController::class, 'chequesNext']);

// Admin: mock user/permission management UI
Route::get('admin/users', [UserPermissionController::class, 'index'])->name('admin.users');
// Admin: Cheque (embedded UI)
Route::get('admin/cheque', function () {
    return view('admin.cheque');
})->name('admin.cheque');

// Auth (basic)
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');






