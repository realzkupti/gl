<?php

namespace App\Http\Controllers\Financial;

use App\Models\Company\BankAccount;
use App\Models\Company\ChequeBook;
use Illuminate\Http\Request;

class ChequeController extends BaseFinancialController
{
    public function index(Request $request)
    {
        $this->ensureAuth();

        // Ensure company is selected
        $redirectResponse = $this->ensureCompanySelected('กรุณาเลือกบริษัทก่อนดูรายการเช็ค');
        if ($redirectResponse) {
            return $redirectResponse;
        }

        $company = $this->getCurrentCompany();
        $period = $request->input('period', 'all');
        $accountKey = $request->input('account');

        // Get list of bank accounts for dropdown (current accounts only) using Model
        $connectionName = $this->getCompanyConnectionName();
        $accounts = BankAccount::getActiveCurrentAccounts($connectionName);

        return view('tailadmin.pages.financial.cheques', compact('company', 'period', 'accountKey', 'accounts'));
    }

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

        $period = $request->input('period', 'all');
        $accountKey = $request->input('account');
        $status = $request->input('status', 'pending');

        try {
            $connectionName = $this->getCompanyConnectionName();

            // Get cheques from Model
            $filters = [
                'period' => $period,
                'account' => $accountKey,
                'status' => $status
            ];

            $cheques = ChequeBook::getCheques($filters, $connectionName);

            // Calculate period summary using Model
            $periodSummary = ChequeBook::calculatePeriodSummary($cheques);

            // Calculate summary for current selection
            $totalCheques = count($cheques);
            $totalAmount = array_sum(array_map(fn($c) => $c->OutstandingAmount ?? 0, $cheques));

            return response()->json([
                'success' => true,
                'cheques' => $cheques,
                'summary' => [
                    'total_cheques' => $totalCheques,
                    'total_amount' => $totalAmount
                ],
                'period_summary' => $periodSummary,
                'period' => $period,
                'company' => $company->label
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to fetch cheques');
        }
    }
}
