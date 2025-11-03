<?php

namespace App\Http\Controllers\Financial;

use App\Models\Company\BankAccount;
use App\Models\Company\BankStatement;
use Illuminate\Http\Request;

class BankStatementController extends BaseFinancialController
{
    /**
     * Display bank statement page
     */
    public function index(Request $request)
    {
        $this->ensureAuth();

        // Ensure company is selected
        $redirectResponse = $this->ensureCompanySelected('กรุณาเลือกบริษัทก่อนดู Statement');
        if ($redirectResponse) {
            return $redirectResponse;
        }

        $company = $this->getCurrentCompany();

        // Get account key from query string (if coming from dashboard)
        $accountKey = $request->input('account');
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        // Get all active accounts for dropdown using Model
        $connectionName = $this->getCompanyConnectionName();
        $accounts = BankAccount::getAllActive($connectionName);

        return view('tailadmin.pages.financial.statement', compact('company', 'accounts', 'accountKey', 'year', 'month'));
    }

    /**
     * Get statement data for specific account and period
     */
    public function getData(Request $request)
    {
        $this->ensureAuth();

        $company = $this->getCurrentCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาเลือกบริษัท'
            ], 400);
        }

        $accountKey = $request->input('account');
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        if (!$accountKey) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุเลขบัญชี'
            ], 400);
        }

        try {
            $connectionName = $this->getCompanyConnectionName();

            // Get statement data from Model
            $result = BankStatement::getStatementForPeriod($accountKey, $year, $month, $connectionName);
            $statements = $result['statements'];
            $summary = $result['summary'];

            // Get account info from Model
            $accountInfo = BankAccount::getAccountInfo($accountKey, $connectionName);

            return response()->json([
                'success' => true,
                'statements' => $statements,
                'account' => $accountInfo,
                'year' => $year,
                'month' => $month,
                'count' => count($statements),
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to fetch bank statement');
        }
    }
}
