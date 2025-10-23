<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrialBalance;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TrialBalanceExport;
use Illuminate\Support\Facades\Log;

class TrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $periodKey = $request->input('period', null);

            if (!$periodKey) {
                // Default to current month if no period selected
                $currentMonth = now()->format('Y-m');
                $periods = TrialBalance::getGLPeriods();
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
                return view('trial_balance_plain', [
                    'rows' => [],
                    'periods' => TrialBalance::getGLPeriods(),
                    'selectedPeriod' => null,
                    'error' => 'ไม่พบงวดบัญชีที่เลือก',
                    'companies' => \App\Services\CompanyManager::listCompanies(),
                    'selectedCompany' => \App\Services\CompanyManager::getSelectedKey(),
                ]);
            }

            $movementRows = TrialBalance::getMovementBalancesForPeriod($periodKey);
            $openingRows = TrialBalance::getOpeningBalancesForPeriod($periodKey);

            $rows = TrialBalance::processTrialBalanceData($movementRows, $openingRows);

            return view('trial_balance_plain', [
                'rows' => $rows,
                'periods' => TrialBalance::getGLPeriods(),
                'selectedPeriod' => $period,
                'companies' => \App\Services\CompanyManager::listCompanies(),
                'selectedCompany' => \App\Services\CompanyManager::getSelectedKey(),
            ]);
        } catch (\Throwable $e) {
            return view('trial_balance_plain', [
                'rows' => [],
                'periods' => [],
                'selectedPeriod' => null,
                'error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้',
                'companies' => \App\Services\CompanyManager::listCompanies(),
                'selectedCompany' => \App\Services\CompanyManager::getSelectedKey(),
            ]);
        }
    }

    // Return JSON detail rows for a given account and period
    public function detail(Request $request)
    {
        $account = $request->query('account');
        $periodKey = $request->query('period');
        $format = strtolower((string) $request->query('format', 'csv'));

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
        $engine = $request->query('engine'); // optional: 'mpdf' (default) or 'dompdf'
        $rowLimit = (int) ($request->query('limit') ?? 0); // optional: debug limit
        $companyKey = \App\Services\CompanyManager::getSelectedKey();
        $companies = \App\Services\CompanyManager::listCompanies();
        $companyLabel = is_array($companies[$companyKey] ?? null)
            ? (($companies[$companyKey]['label'] ?? $companyKey))
            : ($companies[$companyKey] ?? $companyKey);

        $period = TrialBalance::getPeriodByKey($periodKey);
        if (!$period) {
            abort(404, 'Invalid period');
        }

        $movementRows = TrialBalance::getMovementBalancesForPeriod($periodKey);
        $openingRows = TrialBalance::getOpeningBalancesForPeriod($periodKey);
        $rows = TrialBalance::processTrialBalanceData($movementRows, $openingRows);
        if ($rowLimit > 0) { $rows = array_slice($rows, 0, $rowLimit); }

        // Compute category totals for balances (DR/CR)
        $totals = [
            'all' => ['dr' => 0.0, 'cr' => 0.0],
            'assets' => ['dr' => 0.0, 'cr' => 0.0],
            'liab' => ['dr' => 0.0, 'cr' => 0.0],
            'equity' => ['dr' => 0.0, 'cr' => 0.0],
            'revenue' => ['dr' => 0.0, 'cr' => 0.0],
            'expense' => ['dr' => 0.0, 'cr' => 0.0],
        ];
        foreach ($rows as $r) {
            $acc = (string)($r['account_number'] ?? '');
            $first = substr($acc, 0, 1);
            $group = null;
            if ($first === '1') $group = 'assets';
            elseif ($first === '2') $group = 'liab';
            elseif ($first === '3') $group = 'equity';
            elseif ($first === '4') $group = 'revenue';
            else $group = 'expense'; // 5-9 grouped as expenses by default

            $bd = (float)($r['balance_debit'] ?? 0);
            $bc = (float)($r['balance_credit'] ?? 0);
            $totals['all']['dr'] += $bd; $totals['all']['cr'] += $bc;
            $totals[$group]['dr'] += $bd; $totals[$group]['cr'] += $bc;
        }

        $data = [
            'company' => $companyLabel,
            'rows' => $rows,
            'period' => $period,
            'totals' => $totals,
        ];

        // Use mPDF for better Thai rendering
        $html = view('trial_balance_pdf', $data)->render();

        // If explicitly requested or mPDF class not available, use DomPDF
        if ($engine === 'dompdf' || !class_exists('Mpdf\\Mpdf')) {
            // Optional fallback engine for debugging viewer differences
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
            $file = 'trial-balance-'.($period->GLP_YEAR).'_'.$period->GLP_SEQUENCE.'.pdf';
            return $pdf->stream($file);
        }

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
        $mpdf->SetAutoPageBreak(true, 10);
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

        $movementRows = TrialBalance::getMovementBalancesForPeriod($periodKey);
        $openingRows = TrialBalance::getOpeningBalancesForPeriod($periodKey);
        $rows = TrialBalance::processTrialBalanceData($movementRows, $openingRows);
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
        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['งบทดลอง']);
            fputcsv($out, []);
            fputcsv($out, ['เลขบัญชี','ชื่อบัญชี','ยอดยกมา','','ยอดเคลื่อนไหว','','ยอดคงเหลือ','']);
            fputcsv($out, ['', '', 'เดบิต','เครดิต','เดบิต','เครดิต','เดบิต','เครดิต']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['account_number'] ?? '',
                    $r['account_name'] ?? '',
                    number_format((float)($r['opening_debit'] ?? 0), 2, '.', ''),
                    number_format((float)($r['opening_credit'] ?? 0), 2, '.', ''),
                    number_format((float)($r['movement_debit'] ?? 0), 2, '.', ''),
                    number_format((float)($r['movement_credit'] ?? 0), 2, '.', ''),
                    number_format((float)($r['balance_debit'] ?? 0), 2, '.', ''),
                    number_format((float)($r['balance_credit'] ?? 0), 2, '.', ''),
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    // Blade + JS branch trial balance page (no Livewire)
    public function branch(Request $request)
    {
        $periods = TrialBalance::getGLPeriods();
        $selected = $request->query('period');
        if (!$selected && $periods) {
            $currentMonth = now()->format('Y-m');
            foreach ($periods as $p) {
                if (substr($p->GLP_ST_DATE,0,7) === $currentMonth) { $selected = $p->GLP_KEY; break; }
            }
            if (!$selected) $selected = $periods[0]->GLP_KEY ?? null;
        }
        $branches = [];
        if (\DB::getSchemaBuilder()->hasTable('BRANCH')) {
            $branches = \DB::table('BRANCH')->select('BR_CODE as code','BR_THAIDESC as name')->orderBy('BR_CODE')->get();
        } elseif (\DB::getSchemaBuilder()->hasTable('DOCTYPE')) {
            $branches = \DB::table('DOCTYPE')->select(\DB::raw('DT_1ST_BR_CODE as code'))
                ->distinct()->orderBy('DT_1ST_BR_CODE')->get()->map(function($r){ $r->name=$r->code; return $r; });
        } elseif (\DB::getSchemaBuilder()->hasTable('branches')) {
            $branches = \DB::table('branches')->select('code','name')->orderBy('code')->get();
        }
        return view('trial_balance_branch', [
            'periods' => $periods,
            'selectedPeriodKey' => $selected,
            'branches' => $branches,
        ]);
    }

    // JSON data endpoint for branch trial balance table
    public function branchData(Request $request)
    {
        $periodKey = $request->query('period');
        $branch = $request->query('branch');
        if (!$periodKey) return response()->json(['data'=>[]]);
        try {
            $rows = TrialBalance::getBranchPeriodData($periodKey, $branch ?: null);
            return response()->json(['data'=>$rows]);
        } catch (\Throwable $e) {
            return response()->json(['data'=>[], 'error'=>'connection error']);
        }
    }
}
