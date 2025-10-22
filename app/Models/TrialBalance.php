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
            $net = ($v['opening_debit'] - $v['opening_credit']) + ($v['movement_debit'] - $v['movement_credit']);
            $rows[] = [
                'account_number' => $v['account_number'],
                'account_name' => $v['account_name'],
                'opening_debit' => $v['opening_debit'],
                'opening_credit' => $v['opening_credit'],
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
}
