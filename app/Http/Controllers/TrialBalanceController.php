<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        $dateS = $request->input('dateStart', now()->startOfMonth()->toDateString());
        $dateE = $request->input('dateEnd', now()->endOfMonth()->toDateString());
            // $branch = $request->input('branch'); // Removed branch input

        $bindingsMovement = [$dateS, $dateE, $dateS, $dateE];
        $bindingsOpening = [$dateS, $dateS];
        $branchWhere = '';

        // Aggregate across branches: do NOT group by DT_1ST_BR_CODE so the same account is combined
        $movementSql = "
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

        // Opening balances aggregated across branches (no branch grouping)
        $openingSql = "
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

        $movementRows = DB::select($movementSql, $bindingsMovement);
        $openingRows = DB::select($openingSql, $bindingsOpening);

        // merge and calculate balances (same logic as component)
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

        return view('trial_balance_plain', [
            'rows' => $rows,
            'dateStart' => $dateS,
            'dateEnd' => $dateE,
        ]);
    }

    // Return JSON detail rows for a given account and date range
    public function detail(Request $request)
    {
        $account = $request->query('account');
        $dateS = $request->query('dateStart', now()->startOfMonth()->toDateString());
        $dateE = $request->query('dateEnd', now()->endOfMonth()->toDateString());

        if (! $account) {
            return response()->json(['data' => []]);
        }

        $bindings = [$account, $dateS, $dateE, $account, $dateS, $dateE];

        // Return per-document aggregated amounts and include DI_KEY (doc_key) and DI_DT (doc_type)
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

        $rows = DB::select($sql, $bindings);

        return response()->json(['data' => $rows]);
    }

    // Return accounting entries (postings) for a given document DI_KEY
    public function entries(Request $request)
    {
        $docKey = $request->query('doc_key');
        if (! $docKey) {
            return response()->json(['data' => []]);
        }

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

        $rows = DB::select($sql, $bindings);

        return response()->json(['data' => $rows]);
    }
}
