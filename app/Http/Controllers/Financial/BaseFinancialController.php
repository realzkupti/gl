<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Base controller for Financial module
 * Provides shared methods and error handling for all financial controllers
 */
abstract class BaseFinancialController extends Controller
{
    /**
     * Ensure user is authenticated
     *
     * @return void
     */
    protected function ensureAuth()
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Get current company from authenticated user
     *
     * @return \App\Models\Company|null
     */
    protected function getCurrentCompany()
    {
        return auth()->user()?->getCurrentCompany();
    }

    /**
     * Get connection name for current company
     *
     * @return string|null
     */
    protected function getCompanyConnectionName()
    {
        $company = $this->getCurrentCompany();

        if (!$company) {
            return null;
        }

        return 'company_' . $company->key;
    }

    /**
     * Ensure company is selected, redirect if not
     *
     * @param string $errorMessage
     * @param string $redirectRoute
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function ensureCompanySelected($errorMessage = 'กรุณาเลือกบริษัทก่อนใช้งาน', $redirectRoute = 'bplus.dashboard')
    {
        $company = $this->getCurrentCompany();

        if (!$company) {
            return redirect()->route($redirectRoute)->with('error', $errorMessage);
        }

        return null;
    }

    /**
     * Handle controller exceptions with logging
     *
     * @param \Exception $e
     * @param string $context
     * @param string $redirectRoute
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function handleException(\Exception $e, $context = 'Financial operation', $redirectRoute = 'bplus.dashboard')
    {
        Log::error("$context failed: " . $e->getMessage(), [
            'exception' => $e,
            'user_id' => auth()->id(),
            'company' => $this->getCurrentCompany()?->key,
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return redirect()->route($redirectRoute)
            ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}
