<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
            $view->with('userMenus', $userMenus);
        });
    }
}
