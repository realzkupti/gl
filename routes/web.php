<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\TrialBalanceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChequeApiController;
use App\Http\Controllers\Admin\UserPermissionController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Services\CompanyManager;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TailAdminController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UserMenuPermissionController;

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

// Cheque System Pages (protected by login + permission)
Route::prefix('cheque')->name('cheque.')->middleware(['auth','company.connection','menu:cheque,view'])->group(function () {
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
    // Trial balance quick view (keep)
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

// Cheque UI + minimal API migrated to Laravel (protected by login + permission)
Route::get('cheque', [ChequeApiController::class, 'ui'])
    ->middleware(['auth','company.connection','menu:cheque,view'])
    ->name('cheque.ui');
Route::get('cheque/styles.css', [ChequeApiController::class, 'css'])->name('cheque.css');
Route::middleware(['auth','company.connection','menu:cheque,view'])->group(function(){
    Route::get('api/branches', [ChequeApiController::class, 'branches']);
    Route::get('api/cheques', [ChequeApiController::class, 'chequesIndex']);
    Route::get('api/cheques/next', [ChequeApiController::class, 'chequesNext']);
});
Route::post('api/cheques', [ChequeApiController::class, 'chequesStore'])
    ->middleware(['auth','company.connection','menu:cheque,create']);
Route::delete('api/cheques/{id}', [ChequeApiController::class, 'chequesDestroy'])
    ->middleware(['auth','company.connection','menu:cheque,delete']);

// Admin: mock user/permission management UI
Route::middleware(['auth'])->group(function(){
    Route::get('admin/users', [UserPermissionController::class, 'index'])->name('admin.users');

    // Admin: Menus CRUD (basic)
    Route::get('admin/menus', [MenuController::class, 'index'])->name('admin.menus');
    Route::post('admin/menus', [MenuController::class, 'store'])->name('admin.menus.store');
    Route::put('admin/menus/{id}', [MenuController::class, 'update'])->name('admin.menus.update');
    Route::patch('admin/menus/{id}/toggle', [MenuController::class, 'toggle'])->name('admin.menus.toggle');
    Route::delete('admin/menus/{id}', [MenuController::class, 'destroy'])->name('admin.menus.destroy');

    // Admin: User Menu Permissions (รายคน)
    Route::get('admin/user-permissions', [UserMenuPermissionController::class, 'index'])->name('admin.user-permissions');
    Route::get('admin/user-permissions/{userId}', [UserMenuPermissionController::class, 'show'])->name('admin.user-permissions.show');
    Route::put('admin/user-permissions/{userId}', [UserMenuPermissionController::class, 'update'])->name('admin.user-permissions.update');
    Route::delete('admin/user-permissions/{userId}', [UserMenuPermissionController::class, 'reset'])->name('admin.user-permissions.reset');
});
// Admin: Approve users
Route::middleware(['auth'])->group(function () {
    Route::get('admin/user-approvals', [UserApprovalController::class, 'index'])->name('admin.user-approvals');
    Route::post('admin/user-approvals/{id}/activate', [UserApprovalController::class, 'activate'])->name('admin.user-approvals.activate');
    Route::post('admin/user-approvals/{id}/deactivate', [UserApprovalController::class, 'deactivate'])->name('admin.user-approvals.deactivate');
});
// Admin: Cheque (embedded UI)
Route::get('admin/cheque', function () {
    return view('admin.cheque');
})->name('admin.cheque');

// Auth (basic)
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Registration
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::post('register', [AuthController::class, 'register']);

// Basic settings/profile routes to satisfy links in UI (keep on pgsql)
Route::middleware(['auth'])->group(function () {
    Route::get('profile/edit', function () {
        return view('profile.edit');
    })->name('profile.edit');

    Route::get('user/password', function () {
        return view('profile.password');
    })->name('user-password.edit');

    Route::get('two-factor', function () {
        return view('profile.two-factor');
    })->name('two-factor.show');

    Route::get('appearance', function () {
        return view('profile.appearance');
    })->name('appearance.edit');
});
