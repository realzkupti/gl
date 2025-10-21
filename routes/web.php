<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
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
})->name('trial-balance.open');

// Plain server-rendered trial balance for debugging (no Livewire)
Route::get('trial-balance-plain', [App\Http\Controllers\TrialBalanceController::class, 'index'])->name('trial-balance.plain');

// AJAX detail endpoint for trial balance rows
Route::get('trial-balance-detail', [App\Http\Controllers\TrialBalanceController::class, 'detail'])->name('trial-balance.detail');
Route::get('trial-balance-entries', [App\Http\Controllers\TrialBalanceController::class, 'entries'])->name('trial-balance.entries');
