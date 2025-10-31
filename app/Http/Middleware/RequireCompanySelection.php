<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCompanySelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for admin routes (company management pages)
        if ($request->is('admin/*') || $request->is('api/*')) {
            return $next($request);
        }

        // Check if company is selected
        if (!session('current_company_id')) {
            \Log::info('RequireCompanySelection: No company selected, route: ' . $request->route()->getName());

            // If AJAX/fetch request -> return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'require_company_selection' => true,
                    'message' => 'กรุณาเลือกบริษัทก่อนใช้งาน'
                ], 403);
            }

            // If page request -> redirect to Bplus Dashboard with flag
            return redirect()->route('bplus.dashboard')
                ->with('require_company_selection', true);
        }

        return $next($request);
    }
}
