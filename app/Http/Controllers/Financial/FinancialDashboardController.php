<?php

namespace App\Http\Controllers\Financial;

use App\Models\Company\BankAccount;
use App\Models\Company\ChequeBook;
use Illuminate\Http\Request;

class FinancialDashboardController extends BaseFinancialController
{
    /**
     * Display financial dashboard
     */
    public function index()
    {
        $this->ensureAuth();

        // Ensure company is selected
        $redirectResponse = $this->ensureCompanySelected('กรุณาเลือกบริษัทก่อนดูแดชบอร์ดการเงิน');
        if ($redirectResponse) {
            return $redirectResponse;
        }

        $company = $this->getCurrentCompany();

        return view('tailadmin.pages.financial.dashboard', compact('company'));
    }

    /**
     * Get bank account balances (ยอดเงินคงเหลือทุกบัญชี)
     */
    public function accountSummary(Request $request)
    {
        $this->ensureAuth();

        $company = $this->getCurrentCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาเลือกบริษัท'
            ], 400);
        }

        try {
            $connectionName = $this->getCompanyConnectionName();
            $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));

            // Get account summary from Model
            $result = BankAccount::getAccountSummary($asOfDate, $connectionName);

            return response()->json([
                'success' => true,
                'summary' => $result['summary'],
                'all_accounts' => $result['all_accounts'],
                'grand_total' => $result['grand_total'],
                'as_of_date' => $asOfDate,
                'company' => $company->label
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to fetch account summary');
        }
    }

    /**
     * Get cheque summary grouped by period buckets
     */
    public function chequeSummary(Request $request)
    {
        $this->ensureAuth();

        $company = $this->getCurrentCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาเลือกบริษัท'
            ], 400);
        }

        try {
            $connectionName = $this->getCompanyConnectionName();
            $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));

            // Get cheque summary from Model
            $result = ChequeBook::getSummaryByPeriod($asOfDate, $connectionName);

            return response()->json([
                'success' => true,
                'summary' => $result['summary'],
                'total' => $result['total'],
                'as_of_date' => $asOfDate
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to fetch cheque summary');
        }
    }

    /**
     * Get cheques by due date period (รายละเอียดเช็คแบ่งตามช่วงเวลา)
     */
    public function chequesByDueDate(Request $request)
    {
        $this->ensureAuth();

        $company = $this->getCurrentCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาเลือกบริษัท'
            ], 400);
        }

        try {
            $connectionName = $this->getCompanyConnectionName();
            $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));

            // Get cheques by due date from Model
            $cheques = ChequeBook::getChequesByDueDate($asOfDate, $connectionName);

            return response()->json([
                'success' => true,
                'cheques' => $cheques,
                'as_of_date' => $asOfDate,
                'count' => count($cheques)
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to fetch cheques by due date');
        }
    }

    /**
     * Get cheques grouped by payee (รายละเอียดเช็คแยกตามผู้รับเงิน)
     */
    public function chequesByPayee(Request $request)
    {
        $this->ensureAuth();

        $company = $this->getCurrentCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาเลือกบริษัท'
            ], 400);
        }

        try {
            $connectionName = $this->getCompanyConnectionName();
            $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));

            // Get cheques by payee from Model
            $payees = ChequeBook::getChequesByPayee($asOfDate, $connectionName);

            return response()->json([
                'success' => true,
                'payees' => $payees,
                'as_of_date' => $asOfDate
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to fetch cheques by payee');
        }
    }

    /**
     * Get bank statement for specific account (รายการเดินบัญชี)
     *
     * Note: This method is a TODO placeholder.
     * For full implementation, use BankStatementController@getData instead.
     */
    public function accountStatement(Request $request, $accountCode)
    {
        $this->ensureAuth();

        $company = $this->getCurrentCompany();

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาเลือกบริษัท'
            ], 400);
        }

        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            // TODO: Implement bank statement query using BankStatement Model
            // For now, redirect to BankStatementController or implement using:
            // BankStatement::getStatementForPeriod($accountKey, $year, $month, $connectionName)

            return response()->json([
                'success' => true,
                'account_code' => $accountCode,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'statements' => [],
                'message' => 'Bank statement query not yet implemented. Use BankStatementController instead.'
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to fetch account statement');
        }
    }
}
