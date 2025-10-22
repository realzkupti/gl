<?php

namespace App\Http\Middleware;

use App\Services\CompanyManager;
use Closure;
use Illuminate\Http\Request;

class SetCompanyConnection
{
    public function handle(Request $request, Closure $next)
    {
        // Allow override from query param for quick switching
        $key = $request->query('company');

        if ($key) {
            CompanyManager::apply($key);
        } else {
            // Apply existing selection (or default) on every request
            CompanyManager::apply(CompanyManager::getSelectedKey());
        }

        return $next($request);
    }
}

