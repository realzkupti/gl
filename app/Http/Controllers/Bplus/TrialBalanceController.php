<?php

namespace App\Http\Controllers\Bplus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrialBalance;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TrialBalanceExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        // Check if company is selected
        if (!session('current_company_id')) {
            return redirect()->route('bplus.dashboard')
                ->with('require_company_selection', true)
                ->with('error', 'กรุณาเลือกบริษัทก่อนเข้าใช้งานงบทดลอง');
        }

        try {
            // Log connection info
            $currentConnection = DB::connection()->getDatabaseName();
            $currentDriver = DB::connection()->getDriverName();

            Log::info('Trial Balance - Connection Info', [
                'database' => $currentConnection,
                'driver' => $currentDriver,
                'company_id' => session('current_company_id'),
                'default_connection' => config('database.default'),
            ]);

            $periodKey = $request->input('period', null);

            if (!$periodKey) {
                // Default to current month if no period selected
                $currentMonth = now()->format('Y-m');
                $periods = TrialBalance::getGLPeriods();

                Log::info('Trial Balance - Periods loaded', [
                    'count' => count($periods),
                    'periods' => array_map(function($p) {
                        return [
                            'key' => $p->GLP_KEY ?? null,
                            'year' => $p->GLP_YEAR ?? null,
                            'sequence' => $p->GLP_SEQUENCE ?? null,
                        ];
                    }, $periods)
                ]);

                foreach ($periods as $period) {
                    if (substr($period->GLP_ST_DATE, 0, 7) === $currentMonth) {
                        $periodKey = $period->GLP_KEY;
                        break;
                    }
                }
                if (!$periodKey && $periods) {
                    $periodKey = $periods[0]->GLP_KEY; // fallback to first period
                }
            }

            $period = TrialBalance::getPeriodByKey($periodKey);

            if (!$period) {
                return view('bplus.trial-balance', [
                    'rows' => [],
                    'periods' => TrialBalance::getGLPeriods(),
                    'selectedPeriod' => null,
                    'error' => 'ไม่พบงวดบัญชีที่เลือก',
                ]);
            }

            $movementRows = TrialBalance::getMovementBalancesForPeriod($periodKey);
            $openingRows = TrialBalance::getOpeningBalancesForPeriod($periodKey);

            $rows = TrialBalance::processTrialBalanceData($movementRows, $openingRows);

            $currentMenu = \App\Models\Menu::where('route', 'bplus.trial-balance')->first();

            return view('bplus.trial-balance', [
                'rows' => $rows,
                'periods' => TrialBalance::getGLPeriods(),
                'selectedPeriod' => $period,
                'currentMenu' => $currentMenu,
            ]);
        } catch (\Throwable $e) {
            $currentMenu = \App\Models\Menu::where('route', 'bplus.trial-balance')->first();

            Log::error('Trial balance error', [
                'error' => $e->getMessage(),
                'company_id' => session('current_company_id'),
                'user_id' => auth()->id(),
            ]);

            return view('bplus.trial-balance', [
                'rows' => [],
                'periods' => [],
                'selectedPeriod' => null,
                'error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage(),
                'currentMenu' => $currentMenu,
            ]);
        }
    }

    // Return JSON detail rows for a given account and period
    public function detail(Request $request)
    {
        $account = $request->query('account');
        $periodKey = $request->query('period');

        // Accept account code "0"; only reject when truly missing
        if ($account === null || $account === '' || $periodKey === null || $periodKey === '') {
            return response()->json(['data' => []]);
        }

        try {
            $period = TrialBalance::getPeriodByKey($periodKey);
            if (!$period) {
                return response()->json(['data' => [], 'error' => 'invalid period']);
            }

            // For detail, show transactions within the selected period
            $dateS = $period->GLP_ST_DATE;
            $dateE = $period->GLP_EN_DATE;

            // Opening net balance before start date (DR-CR)
            $opening = TrialBalance::getAccountOpeningNet($account, $dateS);

            $rows = TrialBalance::getAccountDetails($account, $dateS, $dateE);

            return response()->json(['data' => $rows, 'opening' => $opening]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => 'connection error']);
        }
    }

    // Return accounting entries (postings) for a given document DI_KEY
    public function entries(Request $request)
    {
        $docKey = $request->query('doc_key');
        if (! $docKey) {
            return response()->json(['data' => []]);
        }

        try {
            $rows = TrialBalance::getDocumentEntries($docKey);
            return response()->json(['data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => 'connection error']);
        }
    }

    public function pdf(Request $request)
    {
        $periodKey = $request->query('period');
        $rowLimit = (int) ($request->query('limit') ?? 0); // optional: debug limit

        // Get company name from session
        $companyId = session('current_company_id');
        $company = $companyId ? \App\Models\Company::find($companyId) : null;
        $companyLabel = $company ? $company->label : 'Unknown Company';

        $period = TrialBalance::getPeriodByKey($periodKey);
        if (!$period) {
            abort(404, 'Invalid period');
        }

        $movementRows = TrialBalance::getMovementBalancesForPeriod($periodKey);
        $openingRows = TrialBalance::getOpeningBalancesForPeriod($periodKey);
        $rows = TrialBalance::processTrialBalanceData($movementRows, $openingRows);

        // Add subtotals by category
        $rows = TrialBalance::addSubtotals($rows);

        if ($rowLimit > 0) { $rows = array_slice($rows, 0, $rowLimit); }

        // Split rows into pages (28 rows per page)
        $rowsPerPage = 28;
        $pages = array_chunk($rows, $rowsPerPage);

        // Compute category totals for balances (DR/CR)
        $totals = [
            'all' => ['dr' => 0.0, 'cr' => 0.0],
        ];
        foreach ($rows as $r) {
            // Skip subtotals in grand total calculation
            if (isset($r['is_subtotal']) && $r['is_subtotal']) {
                continue;
            }
            $bd = (float)($r['balance_debit'] ?? 0);
            $bc = (float)($r['balance_credit'] ?? 0);
            $totals['all']['dr'] += $bd;
            $totals['all']['cr'] += $bc;
        }

        $data = [
            'company' => $companyLabel,
            'pages' => $pages,
            'period' => $period,
            'totals' => $totals,
        ];

        // Use mPDF for better Thai rendering
        $html = view('trial_balance_pdf', $data)->render();

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'tempDir' => $tempDir,
            'fontDir' => [resource_path('fonts')],
            'fontdata' => [
                'sarabun' => [
                    'R' => 'Sarabun-Regular.ttf',
                    'B' => 'Sarabun-Bold.ttf',
                ],
            ],
            'default_font' => 'sarabun',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;
        $mpdf->shrink_tables_to_fit = 1;
        $mpdf->SetAutoPageBreak(false); // Manual page breaks
        $mpdf->WriteHTML($html);
        $file = 'trial-balance-'.($period->GLP_YEAR).'_'.$period->GLP_SEQUENCE.'.pdf';
        return response($mpdf->Output($file, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$file.'"'
        ]);
    }

    public function excel(Request $request)
    {
        $periodKey = $request->query('period');
        $format = strtolower((string) $request->query('format', 'csv'));
        $period = TrialBalance::getPeriodByKey($periodKey);
        if (!$period) {
            abort(404, 'Invalid period');
        }

        // Get company name from session
        $companyId = session('current_company_id');
        $company = $companyId ? \App\Models\Company::find($companyId) : null;
        $companyLabel = $company ? $company->label : 'Unknown Company';

        $movementRows = TrialBalance::getMovementBalancesForPeriod($periodKey);
        $openingRows = TrialBalance::getOpeningBalancesForPeriod($periodKey);
        $rows = TrialBalance::processTrialBalanceData($movementRows, $openingRows);

        // Add subtotals by category
        $rows = TrialBalance::addSubtotals($rows);

        // Default to CSV for reliability; allow xlsx only when explicitly asked
        if ($format === 'xlsx') {
            try {
                $title = 'งบทดลอง ' . ($period->GLP_SEQUENCE ?? '') . '/' . ($period->GLP_YEAR ?? '');
                $export = new TrialBalanceExport($rows, [/* totals not needed for sheet */], $title);
                $xlsx = 'trial-balance-' . ($period->GLP_YEAR) . '_' . $period->GLP_SEQUENCE . '.xlsx';
                return Excel::download($export, $xlsx, \Maatwebsite\Excel\Excel::XLSX);
            } catch (\Throwable $e) {
                // Log the underlying error for 500s that may not hit Laravel handler
                Log::error('XLSX export failed', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                // fall through to CSV below
            }
        }

        // Fallback: stream CSV with BOM (works on Excel Windows)
        $filename = 'trial-balance-' . ($period->GLP_YEAR) . '_' . $period->GLP_SEQUENCE . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($rows, $companyLabel, $period) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [$companyLabel]);
            fputcsv($out, ['งบทดลอง']);
            fputcsv($out, ['งวด ' . ($period->GLP_SEQUENCE ?? '') . '/' . ($period->GLP_YEAR ?? '')]);
            fputcsv($out, []);
            fputcsv($out, ['เลขบัญชี','ชื่อบัญชี','ยอดยกมา','','ยอดเคลื่อนไหว','','ยอดคงเหลือ','']);
            fputcsv($out, ['', '', 'เดบิต','เครดิต','เดบิต','เครดิต','เดบิต','เครดิต']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['account_number'] ?? '',
                    $r['account_name'] ?? '',
                    $r['opening_debit'] !== '' ? number_format((float)($r['opening_debit'] ?? 0), 2, '.', '') : '',
                    $r['opening_credit'] !== '' ? number_format((float)($r['opening_credit'] ?? 0), 2, '.', '') : '',
                    $r['movement_debit'] !== '' ? number_format((float)($r['movement_debit'] ?? 0), 2, '.', '') : '',
                    $r['movement_credit'] !== '' ? number_format((float)($r['movement_credit'] ?? 0), 2, '.', '') : '',
                    number_format((float)($r['balance_debit'] ?? 0), 2, '.', ''),
                    number_format((float)($r['balance_credit'] ?? 0), 2, '.', ''),
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    // Branch trial balance
    public function branch(Request $request)
    {
        // Check if company is selected
        if (!session('current_company_id')) {
            return redirect()->route('bplus.dashboard')
                ->with('require_company_selection', true)
                ->with('error', 'กรุณาเลือกบริษัทก่อนเข้าใช้งานงบทดลอง');
        }

        try {
            $periods = TrialBalance::getGLPeriods();
            $selectedPeriodKey = $request->input('period', $periods[0]->GLP_KEY ?? null);
            $branches = TrialBalance::getBranches();

            return view('bplus.trial-balance-branch', [
                'periods' => $periods,
                'selectedPeriodKey' => $selectedPeriodKey,
                'branches' => $branches,
            ]);
        } catch (\Throwable $e) {
            Log::error('Trial balance branch error', [
                'error' => $e->getMessage(),
                'company_id' => session('current_company_id'),
                'user_id' => auth()->id(),
            ]);

            return view('bplus.trial-balance-branch', [
                'periods' => [],
                'selectedPeriodKey' => null,
                'branches' => [],
                'error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . $e->getMessage(),
            ]);
        }
    }

    public function branchData(Request $request)
    {
        $periodKey = $request->query('period');
        $branchCode = $request->query('branch', '');

        try {
            $rows = TrialBalance::getTrialBalanceByBranch($periodKey, $branchCode);
            return response()->json(['data' => $rows]);
        } catch (\Throwable $e) {
            Log::error('Trial balance branch data error', [
                'error' => $e->getMessage(),
                'period' => $periodKey,
                'branch' => $branchCode,
            ]);
            return response()->json(['data' => [], 'error' => 'connection error']);
        }
    }
}
