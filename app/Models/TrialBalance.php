<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrialBalance extends Model
{
    // This model doesn't use Eloquent ORM directly since it's complex queries
    // We'll use raw queries for performance

    /**
     * Get movement balances for the given date range
     */
    public static function getMovementBalances($dateStart, $dateEnd)
    {
        $bindings = [$dateStart, $dateEnd, $dateStart, $dateEnd];

        $sql = "
            SELECT account_number, account_name, SUM(DR) AS DR, SUM(CR) AS CR
            FROM (
                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name,
                       SUM(GL.TRJ_DEBIT) AS DR, SUM(GL.TRJ_CREDIT) AS CR
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE BETWEEN ? AND ?
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC

                UNION ALL

                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name,
                       SUM(GL.TPJ_DEBIT) AS DR, SUM(GL.TPJ_CREDIT) AS CR
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE BETWEEN ? AND ?
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC
            ) x
            GROUP BY account_number, account_name
            ORDER BY account_number
        ";

        return DB::select($sql, $bindings);
    }

    /**
     * Get opening balances before the given date
     */
    public static function getOpeningBalances($dateStart)
    {
        $bindings = [$dateStart, $dateStart];

        $sql = "
            SELECT account_number, account_name, SUM(DR) AS DR, SUM(CR) AS CR
            FROM (
                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name,
                       SUM(GL.TRJ_DEBIT) AS DR, SUM(GL.TRJ_CREDIT) AS CR
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE < ?
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC

                UNION ALL

                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name,
                       SUM(GL.TPJ_DEBIT) AS DR, SUM(GL.TPJ_CREDIT) AS CR
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE < ?
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC
            ) x
            GROUP BY account_number, account_name
            ORDER BY account_number
        ";

        return DB::select($sql, $bindings);
    }

    /**
     * Get detail rows for a specific account and date range
     */
    public static function getAccountDetails($account, $dateStart, $dateEnd)
    {
        $bindings = [$account, $dateStart, $dateEnd, $account, $dateStart, $dateEnd];

        $sql = "
            SELECT DI.DI_KEY AS doc_key, DI.DI_REF as doc_ref, DI.DI_DATE as doc_date,
                   DT.DT_THAIDESC as doc_type, DT.DT_1ST_BR_CODE as branch_code,
                   SUM(SUM_PART.DR) as DR, SUM(SUM_PART.CR) as CR, SUM_PART.SOURCE as source
            FROM (
                SELECT DI.DI_KEY, DI.DI_REF, DI.DI_DATE, GL.TRJ_DEBIT AS DR, GL.TRJ_CREDIT AS CR, 'TRANSTKJ' AS SOURCE
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE AC.AC_CODE = ? AND DI.DI_DATE BETWEEN ? AND ?

                UNION ALL

                SELECT DI.DI_KEY, DI.DI_REF, DI.DI_DATE, GL.TPJ_DEBIT AS DR, GL.TPJ_CREDIT AS CR, 'TRANPAYJ' AS SOURCE
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE AC.AC_CODE = ? AND DI.DI_DATE BETWEEN ? AND ?
            ) SUM_PART
            INNER JOIN DOCINFO DI ON SUM_PART.DI_KEY = DI.DI_KEY
            LEFT JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
            GROUP BY DI.DI_KEY, DI.DI_REF, DI.DI_DATE, DT.DT_THAIDESC, DT.DT_1ST_BR_CODE, SUM_PART.SOURCE
            ORDER BY DI.DI_DATE, DI.DI_REF
        ";

        return DB::select($sql, $bindings);
    }

    /**
     * Get accounting entries for a specific document
     */
    public static function getDocumentEntries($docKey)
    {
        $bindings = [$docKey, $docKey];

        $sql = "
            SELECT AC.AC_CODE as account_code, AC.AC_THAIDESC as account_name, lines.DR as DR, lines.CR as CR,
                   DT.DT_THAIDESC as doc_type, DI.DI_REMARK as doc_remark
            FROM (
                SELECT TRJ_AC as AC_KEY, TRJ_DEBIT as DR, TRJ_CREDIT as CR, 'TRANSTKJ' as SOURCE, TRJ_DI as DI_KEY
                FROM TRANSTKJ WHERE TRJ_DI = ?
                UNION ALL
                SELECT TPJ_AC as AC_KEY, TPJ_DEBIT as DR, TPJ_CREDIT as CR, 'TRANPAYJ' as SOURCE, TPJ_DI as DI_KEY
                FROM TRANPAYJ WHERE TPJ_DI = ?
            ) lines
            LEFT JOIN ACCOUNTCHART AC ON lines.AC_KEY = AC.AC_KEY
            LEFT JOIN DOCINFO DI ON lines.DI_KEY = DI.DI_KEY
            LEFT JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
            ORDER BY COALESCE(lines.DR,0) DESC, COALESCE(lines.CR,0) DESC
        ";

        return DB::select($sql, $bindings);
    }

    /**
     * Get opening net balance (DR-CR) for a specific account before a given date
     */
    public static function getAccountOpeningNet($account, $dateStart)
    {
        $bindings = [$account, $dateStart, $account, $dateStart];

        $sql = "
            SELECT SUM(DR) AS DR, SUM(CR) AS CR
            FROM (
                SELECT SUM(GL.TRJ_DEBIT) AS DR, SUM(GL.TRJ_CREDIT) AS CR
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                WHERE AC.AC_CODE = ? AND DI.DI_DATE < ?

                UNION ALL

                SELECT SUM(GL.TPJ_DEBIT) AS DR, SUM(GL.TPJ_CREDIT) AS CR
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                WHERE AC.AC_CODE = ? AND DI.DI_DATE < ?
            ) x
        ";

        $rows = DB::select($sql, $bindings);
        $dr = isset($rows[0]->DR) ? (float)$rows[0]->DR : 0.0;
        $cr = isset($rows[0]->CR) ? (float)$rows[0]->CR : 0.0;
        return $dr - $cr; // positive=debit balance, negative=credit balance
    }

    /**
     * Process and merge movement and opening balances into trial balance rows
     */
    public static function processTrialBalanceData($movementRows, $openingRows)
    {
        $map = [];

        // Key by account only (aggregated across branches)
        foreach ($openingRows as $r) {
            $key = $r->account_number;
            $map[$key] = [
                'account_number' => $r->account_number,
                'account_name' => $r->account_name,
                'opening_debit' => (float) $r->DR,
                'opening_credit' => (float) $r->CR,
                'movement_debit' => 0.0,
                'movement_credit' => 0.0,
            ];
        }

        foreach ($movementRows as $r) {
            $key = $r->account_number;
            if (!isset($map[$key])) {
                $map[$key] = [
                    'account_number' => $r->account_number,
                    'account_name' => $r->account_name,
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

        $rows = [];
        foreach ($map as $k => $v) {
            // Net opening: show difference on one side only
            $openNet = ($v['opening_debit'] - $v['opening_credit']);
            $openingDebit = $openNet >= 0 ? $openNet : 0;
            $openingCredit = $openNet < 0 ? abs($openNet) : 0;

            // Net balance: opening + movement
            $net = ($openingDebit - $openingCredit) + ($v['movement_debit'] - $v['movement_credit']);
            $rows[] = [
                'account_number' => $v['account_number'],
                'account_name' => $v['account_name'],
                'opening_debit' => $openingDebit,
                'opening_credit' => $openingCredit,
                'movement_debit' => $v['movement_debit'],
                'movement_credit' => $v['movement_credit'],
                'balance_debit' => $net >= 0 ? $net : 0,
                'balance_credit' => $net < 0 ? abs($net) : 0,
            ];
        }

        return $rows;
    }

    /**
     * Get all available GL periods
     */
    public static function getGLPeriods()
    {
        return DB::select("SELECT GLP_KEY, GLP_YEAR, GLP_SEQUENCE, GLP_ST_DATE, GLP_EN_DATE FROM GLPERIOD ORDER BY GLP_ST_DATE");
    }

    /**
     * Get period details by key
     */
    public static function getPeriodByKey($periodKey)
    {
        $periods = DB::select("SELECT GLP_KEY, GLP_YEAR, GLP_SEQUENCE, GLP_ST_DATE, GLP_EN_DATE FROM GLPERIOD WHERE GLP_KEY = ?", [$periodKey]);
        return $periods[0] ?? null;
    }

    /**
     * Get opening balances as of start of selected period
     */
    public static function getOpeningBalancesForPeriod($periodKey)
    {
        $period = self::getPeriodByKey($periodKey);
        if (!$period) return [];

        // Opening balances as of start of selected period
        $periodStart = $period->GLP_ST_DATE;

        return self::getOpeningBalances($periodStart);
    }

    /**
     * Get movement balances for the selected period only
     */
    public static function getMovementBalancesForPeriod($periodKey)
    {
        $period = self::getPeriodByKey($periodKey);
        if (!$period) return [];

        // Movement within the selected period
        $periodStart = $period->GLP_ST_DATE;
        $periodEnd = $period->GLP_EN_DATE;

        return self::getMovementBalances($periodStart, $periodEnd);
    }

    /**
     * Movement within date range grouped by account and branch
     */
    public static function getMovementByBranch($dateStart, $dateEnd, $branchCode = null)
    {
        $branchWhere = '';
        $bindings = [$dateStart, $dateEnd, $dateStart, $dateEnd];
        if ($branchCode) {
            $branchWhere = ' AND DT.DT_1ST_BR_CODE = ?';
            // One param for each inner select (2 selects)
            $bindings[] = $branchCode;
            $bindings[] = $branchCode;
        }

        $sql = "
            SELECT account_number, account_name, branch_code, SUM(DR) AS DR, SUM(CR) AS CR
            FROM (
                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TRJ_DEBIT) AS DR, SUM(GL.TRJ_CREDIT) AS CR
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE BETWEEN ? AND ?
                {$branchWhere}
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE

                UNION ALL

                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TPJ_DEBIT) AS DR, SUM(GL.TPJ_CREDIT) AS CR
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE BETWEEN ? AND ?
                {$branchWhere}
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE
            ) x
            GROUP BY account_number, account_name, branch_code
            ORDER BY account_number
        ";
        return DB::select($sql, $bindings);
    }

    /**
     * Opening before date grouped by account and branch
     */
    public static function getOpeningByBranch($dateStart, $branchCode = null)
    {
        $branchWhere = '';
        $bindings = [$dateStart, $dateStart];
        if ($branchCode) {
            $branchWhere = ' AND DT.DT_1ST_BR_CODE = ?';
            // One param for each inner select (2 selects)
            $bindings[] = $branchCode;
            $bindings[] = $branchCode;
        }

        $sql = "
            SELECT account_number, account_name, branch_code, SUM(DR) AS DR, SUM(CR) AS CR
            FROM (
                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TRJ_DEBIT) AS DR, SUM(GL.TRJ_CREDIT) AS CR
                FROM TRANSTKJ GL
                INNER JOIN DOCINFO DI ON GL.TRJ_DI = DI.DI_KEY
                INNER JOIN ACCOUNTCHART AC ON GL.TRJ_AC = AC.AC_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE < ?
                {$branchWhere}
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE

                UNION ALL

                SELECT AC.AC_CODE AS account_number, AC.AC_THAIDESC AS account_name, DT.DT_1ST_BR_CODE AS branch_code,
                       SUM(GL.TPJ_DEBIT) AS DR, SUM(GL.TPJ_CREDIT) AS CR
                FROM TRANPAYJ GL
                INNER JOIN ACCOUNTCHART AC ON GL.TPJ_AC = AC.AC_KEY
                INNER JOIN DOCINFO DI ON GL.TPJ_DI = DI.DI_KEY
                INNER JOIN DOCTYPE DT ON DI.DI_DT = DT.DT_KEY
                WHERE DI.DI_DATE < ?
                {$branchWhere}
                GROUP BY AC.AC_CODE, AC.AC_THAIDESC, DT.DT_1ST_BR_CODE
            ) x
            GROUP BY account_number, account_name, branch_code
            ORDER BY account_number
        ";
        return DB::select($sql, $bindings);
    }

    public static function getBranchPeriodData($periodKey, $branchCode = null)
    {
        $period = self::getPeriodByKey($periodKey);
        if (!$period) return [];
        $movement = self::getMovementByBranch($period->GLP_ST_DATE, $period->GLP_EN_DATE, $branchCode);
        $opening  = self::getOpeningByBranch($period->GLP_ST_DATE, $branchCode);

        // Merge like Livewire map logic (account + branch)
        $map = [];
        foreach ($opening as $r) {
            $key = $r->account_number.'|'.($r->branch_code ?? '');
            $map[$key] = [
                'branch_id' => $r->branch_code,
                'branch_name' => $r->branch_code,
                'account_number' => $r->account_number,
                'account_name' => $r->account_name,
                'opening_debit' => (float)$r->DR,
                'opening_credit' => (float)$r->CR,
                'movement_debit' => 0.0,
                'movement_credit' => 0.0,
            ];
        }
        foreach ($movement as $r) {
            $key = $r->account_number.'|'.($r->branch_code ?? '');
            if (!isset($map[$key])) {
                $map[$key] = [
                    'branch_id' => $r->branch_code,
                    'branch_name' => $r->branch_code,
                    'account_number' => $r->account_number,
                    'account_name' => $r->account_name,
                    'opening_debit' => 0.0,
                    'opening_credit' => 0.0,
                    'movement_debit' => (float)$r->DR,
                    'movement_credit' => (float)$r->CR,
                ];
            } else {
                $map[$key]['movement_debit'] = (float)$r->DR;
                $map[$key]['movement_credit'] = (float)$r->CR;
            }
        }
        $rows = [];
        foreach ($map as $v) {
            $net = ($v['opening_debit'] - $v['opening_credit']) + ($v['movement_debit'] - $v['movement_credit']);
            $rows[] = $v + [
                'balance_debit' => $net >= 0 ? $net : 0,
                'balance_credit' => $net < 0 ? abs($net) : 0,
            ];
        }
        usort($rows, function($a,$b){
            if (($a['branch_id'] ?? '') === ($b['branch_id'] ?? '')) return strcmp($a['account_number'],$b['account_number']);
            return strcmp((string)($a['branch_id'] ?? ''),(string)($b['branch_id'] ?? ''));
        });
        return $rows;
    }

    /**
     * Get trial balance by branch
     */
    public static function getTrialBalanceByBranch($periodKey, $branchCode)
    {
        return self::getBranchPeriodData($periodKey, $branchCode);
    }

    /**
     * Get all branches from DOCTYPE
     */
    public static function getBranches()
    {
        return DB::select("SELECT DISTINCT DT.DT_1ST_BR_CODE AS branch_code FROM DOCTYPE DT WHERE DT.DT_1ST_BR_CODE IS NOT NULL ORDER BY DT.DT_1ST_BR_CODE");
    }

    /**
     * Sort rows by account number and add subtotals by category
     */
    public static function addSubtotals($rows)
    {
        // Sort by account number
        usort($rows, function($a, $b) {
            return strcmp($a['account_number'] ?? '', $b['account_number'] ?? '');
        });

        // Add subtotals by category
        $result = [];
        $currentCategory = null;
        $subtotals = ['dr' => 0.0, 'cr' => 0.0];

        foreach ($rows as $row) {
            $acc = (string)($row['account_number'] ?? '');
            $first = substr($acc, 0, 1);

            // Determine category
            $category = null;
            if ($first === '1') $category = '1';
            elseif ($first === '2') $category = '2';
            elseif ($first === '3') $category = '3';
            elseif ($first === '4') $category = '4';
            else $category = '5'; // 5-9 grouped as expenses

            // If category changed, add subtotal
            if ($currentCategory !== null && $currentCategory !== $category) {
                $result[] = [
                    'is_subtotal' => true,
                    'category' => $currentCategory,
                    'account_number' => 'รวม ' . self::getCategoryName($currentCategory),
                    'account_name' => '',
                    'opening_debit' => '',
                    'opening_credit' => '',
                    'movement_debit' => '',
                    'movement_credit' => '',
                    'balance_debit' => $subtotals['dr'],
                    'balance_credit' => $subtotals['cr'],
                ];
                $subtotals = ['dr' => 0.0, 'cr' => 0.0];
            }

            $currentCategory = $category;
            $result[] = $row;

            // Accumulate subtotals
            $subtotals['dr'] += (float)($row['balance_debit'] ?? 0);
            $subtotals['cr'] += (float)($row['balance_credit'] ?? 0);
        }

        // Add final subtotal
        if ($currentCategory !== null) {
            $result[] = [
                'is_subtotal' => true,
                'category' => $currentCategory,
                'account_number' => 'รวม ' . self::getCategoryName($currentCategory),
                'account_name' => '',
                'opening_debit' => '',
                'opening_credit' => '',
                'movement_debit' => '',
                'movement_credit' => '',
                'balance_debit' => $subtotals['dr'],
                'balance_credit' => $subtotals['cr'],
            ];
        }

        return $result;
    }

    /**
     * Get category name in Thai
     */
    protected static function getCategoryName($category)
    {
        $names = [
            '1' => 'สินทรัพย์',
            '2' => 'หนี้สิน',
            '3' => 'ทุน',
            '4' => 'รายได้',
            '5' => 'ค่าใช้จ่าย',
        ];
        return $names[$category] ?? 'อื่นๆ';
    }
}
