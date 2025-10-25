<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Support\Perm;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share user menus with sidebar partial
        View::composer('tailadmin.partials.sidebar', function ($view) {
            $userMenus = Perm::getUserMenus();

            // Debug log
            Log::info('Sidebar getUserMenus called', [
                'count' => count($userMenus),
                'groups' => array_keys($userMenus),
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email ?? 'N/A'
            ]);

            $view->with('userMenus', $userMenus);
        });
    }
}
