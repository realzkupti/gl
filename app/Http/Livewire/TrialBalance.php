<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TrialBalance extends Component
{
    public $dateStart;
    public $dateEnd;
    public $branchId = null; // nullable, when null show all branches grouped (this holds branch code)
    public $branches = [];

    // Data for the table
    public $rows = [];

    // Detail modal
    public $showDetail = false;
    public $detailRows = [];
    public $detailAccount = null;

    protected $listeners = [
        'refreshComponent' => '$refresh',
        // expose load as an event/listener alias as well
        'load' => 'load',
    ];

    public function mount()
    {
        $this->dateStart = now()->startOfMonth()->toDateString();
        $this->dateEnd = now()->endOfMonth()->toDateString();

        // Load branches from BRANCH table (Bplus): BR_KEY, BR_CODE, BR_THAIDESC
        if (DB::getSchemaBuilder()->hasTable('BRANCH')) {
            $this->branches = DB::table('BRANCH')
                ->select('BR_KEY as id', 'BR_CODE as code', 'BR_THAIDESC as name')
                ->orderBy('BR_CODE')
                ->get()
                ->toArray();
        } else {
            // fallback: try DOCTYPE or branches
            if (DB::getSchemaBuilder()->hasTable('DOCTYPE')) {
                $this->branches = DB::table('DOCTYPE')
                    ->select(DB::raw('DT_1ST_BR_CODE as code'))
                    ->distinct()
                    ->orderBy('DT_1ST_BR_CODE')
                    ->get()
                    ->map(function ($r) { return (object) ['code' => $r->code, 'name' => $r->code]; })
                    ->toArray();
            } elseif (DB::getSchemaBuilder()->hasTable('branches')) {
                $this->branches = DB::table('branches')
                    ->select('id as id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($r) { return (object) ['code' => $r->id, 'name' => $r->name]; })
                    ->toArray();
            } else {
                $this->branches = [];
            }
        }
    }

    public function render()
    {
        return view('livewire.trial-balance');
    }

    // Main loader - replace the SQL inside with your existing query
    public function loadData()
    {
        // Use the user's provided logic: union TRANSTKJ and TRANPAYJ for movements inside range
        $dateS = $this->dateStart;
        $dateE = $this->dateEnd;

        $branchWhere = '';
        $bindingsMovement = [$dateS, $dateE, $dateS, $dateE];
        $bindingsOpening = [$dateS, $dateS];

        if ($this->branchId) {
            // DT_1ST_BR_CODE is used in the user's query — append branch filter for each inner select
            $branchWhere = ' AND DT.DT_1ST_BR_CODE = ?';
            // append branch param for movement (two inner selects)
            $bindingsMovement[] = $this->branchId;
            // append branch param for opening
            $bindingsOpening[] = $this->branchId;
        }

        // Movement within date range
        $movementSql = "
            SELECT account_number, account_name, branch_code, SUM(DR) AS DR, SUM(CR) AS CR
            FROM (
                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TRJ_DEBIT) AS DR, SUM(GL.TRJ_CREDIT) AS CR
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE BETWEEN ? AND ?
                " . $branchWhere . "
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE

                UNION ALL

                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TPJ_DEBIT) AS DR, SUM(GL.TPJ_CREDIT) AS CR
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE BETWEEN ? AND ?
                " . $branchWhere . "
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE
            ) x
            GROUP BY account_number, account_name, branch_code
            ORDER BY account_number
        ";

        $movementRows = DB::select($movementSql, $bindingsMovement);

        // Opening (before dateS)
        $openingSql = "
            SELECT account_number, account_name, branch_code, SUM(DR) AS DR, SUM(CR) AS CR
            FROM (
                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TRJ_DEBIT) AS DR, SUM(GL.TRJ_CREDIT) AS CR
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE < ?
                " . $branchWhere . "
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE

                UNION ALL

                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TPJ_DEBIT) AS DR, SUM(GL.TPJ_CREDIT) AS CR
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE < ?
                " . $branchWhere . "
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE
            ) x
            GROUP BY account_number, account_name, branch_code
            ORDER BY account_number
        ";

        $openingRows = DB::select($openingSql, $bindingsOpening);

        // Merge results keyed by account+branch
        $map = [];

        foreach ($openingRows as $r) {
            $key = $r->account_number . '|' . ($r->branch_code ?? '');
            $map[$key] = [
                'account_number' => $r->account_number,
                'account_name' => $r->account_name,
                'branch_code' => $r->branch_code,
                'opening_debit' => (float) $r->DR,
                'opening_credit' => (float) $r->CR,
                'movement_debit' => 0.0,
                'movement_credit' => 0.0,
            ];
        }

        foreach ($movementRows as $r) {
            $key = $r->account_number . '|' . ($r->branch_code ?? '');
            if (!isset($map[$key])) {
                $map[$key] = [
                    'account_number' => $r->account_number,
                    'account_name' => $r->account_name,
                    'branch_code' => $r->branch_code,
                    'opening_debit' => 0.0,
                    'opening_credit' => 0.0,
                    'movement_debit' => (float) $r->DR,
                    'movement_credit' => (float) $r->CR,
                ];
            } else {
                $map[$key]['movement_debit'] = (float) $r->DR;
                $map[$key]['movement_credit'] = (float) $r->CR;
            }
        }

        // Build rows array with balances
        $rows = [];
        foreach ($map as $k => $v) {
            $opening_debit = $v['opening_debit'];
            $opening_credit = $v['opening_credit'];
            $movement_debit = $v['movement_debit'];
            $movement_credit = $v['movement_credit'];

            $net = ($opening_debit - $opening_credit) + ($movement_debit - $movement_credit);
            $balance_debit = $net >= 0 ? $net : 0;
            $balance_credit = $net < 0 ? abs($net) : 0;

            $rows[] = [
                'branch_id' => $v['branch_code'],
                'branch_name' => $v['branch_code'],
                'account_number' => $v['account_number'],
                'account_name' => $v['account_name'],
                'opening_debit' => $opening_debit,
                'opening_credit' => $opening_credit,
                'movement_debit' => $movement_debit,
                'movement_credit' => $movement_credit,
                'balance_debit' => $balance_debit,
                'balance_credit' => $balance_credit,
            ];
        }

        // Sort rows by branch then account
        usort($rows, function ($a, $b) {
            if ($a['branch_id'] === $b['branch_id']) {
                return strcmp($a['account_number'], $b['account_number']);
            }
            return strcmp((string) $a['branch_id'], (string) $b['branch_id']);
        });

        $this->rows = $rows;
    }

    public function updatedDateStart()
    {
        $this->load();
    }

    public function updatedDateEnd()
    {
        $this->load();
    }

    public function updatedBranchId()
    {
        $this->load();
    }

    // wrapper for Livewire calls (some runtime contexts may require a simple public action name)
    public function load()
    {
        $this->loadData();
    }

    // When user clicks on movement amount, show detail rows for that account and branch
    public function showMovementDetail($accountNumber, $branchId = null)
    {
        $this->detailAccount = $accountNumber;
        $this->showDetail = true;

        $dateS = $this->dateStart;
        $dateE = $this->dateEnd;

        $branchWhere = '';
        $bindings = [$accountNumber, $dateS, $dateE, $accountNumber, $dateS, $dateE];
        if ($branchId) {
            $branchWhere = ' AND DT.DT_1ST_BR_CODE = ?';
            $bindings[] = $branchId;
        }

        $sql = "
            SELECT DI.DI_DATE AS doc_date, DI.DI_KEY AS doc_key, DT.DT_1ST_BR_CODE AS branch_code,
                   SUM_PART.DR AS DR, SUM_PART.CR AS CR, SUM_PART.SOURCE
            FROM (
                SELECT DI.DI_KEY, DI.DI_DATE, DT.DT_1ST_BR_CODE, GL.TRJ_DEBIT AS DR, GL.TRJ_CREDIT AS CR, 'TRANSTKJ' AS SOURCE
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE AC.AC_CODE = ?
                AND DI.DI_DATE BETWEEN ? AND ?
                " . $branchWhere . "

                UNION ALL

                SELECT DI.DI_KEY, DI.DI_DATE, DT.DT_1ST_BR_CODE, GL.TPJ_DEBIT AS DR, GL.TPJ_CREDIT AS CR, 'TRANPAYJ' AS SOURCE
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE AC.AC_CODE = ?
                AND DI.DI_DATE BETWEEN ? AND ?
                " . $branchWhere . "
            ) SUM_PART
            INNER JOIN DOCINFO DI ON SUM_PART.DI_KEY = DI.DI_KEY
            LEFT JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
            ORDER BY doc_date, doc_key
        ";

        // Note: Above we selected DR/CR per row; DB::select will return rows from the union. Use it directly.
        $this->detailRows = DB::select($sql, $bindings);
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->detailRows = [];
        $this->detailAccount = null;
    }
}
