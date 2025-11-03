<?php

namespace App\Models\Company;

/**
 * BankAccount Model
 *
 * Represents BANKACCOUNT table in company MSSQL databases
 * Handles bank account information and balance calculations
 */
class BankAccount extends CompanyModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'BANKACCOUNT';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'BNKAC_KEY';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'BNKAC_KEY' => 'integer',
        'BNKAC_ENABLED' => 'string',
    ];

    /**
     * Scope: Active accounts only
     */
    public function scopeActive($query)
    {
        return $query->where('BNKAC_ENABLED', 'Y');
    }

    /**
     * Scope: Current accounts (type 1)
     */
    public function scopeCurrentAccounts($query)
    {
        return $query->where('BNKAC_AC_TYPE', '1');
    }

    /**
     * Scope: Savings accounts (type 2)
     */
    public function scopeSavingsAccounts($query)
    {
        return $query->where('BNKAC_AC_TYPE', '2');
    }

    /**
     * Relationships
     */

    /**
     * Get bank statements for this account
     */
    public function statements()
    {
        return $this->hasMany(BankStatement::class, 'BSTM_BNKAC', 'BNKAC_KEY');
    }

    /**
     * Get periods for this account
     */
    public function periods()
    {
        return $this->hasMany(BankStatementPeriod::class, 'BSTMP_BNKAC', 'BNKAC_KEY');
    }

    /**
     * Get cheques for this account
     */
    public function cheques()
    {
        return $this->hasMany(ChequeBook::class, 'CQBK_BNKAC', 'BNKAC_KEY');
    }

    /**
     * Get all active accounts
     *
     * @param string|null $connectionName
     * @return array
     */
    public static function getAllActive($connectionName = null)
    {
        $sql = "
            SELECT BNKAC_KEY, BNKAC_CODE, BNKAC_NAME, BNKAC_AC_TYPE
            FROM BANKACCOUNT
            WHERE BNKAC_ENABLED = 'Y'
            ORDER BY BNKAC_CODE
        ";

        return static::executeRawQuery($sql, [], $connectionName);
    }

    /**
     * Get active current accounts (type 1)
     *
     * @param string|null $connectionName
     * @return array
     */
    public static function getActiveCurrentAccounts($connectionName = null)
    {
        $sql = "
            SELECT BNKAC_KEY, BNKAC_CODE, BNKAC_NAME
            FROM BANKACCOUNT
            WHERE BNKAC_ENABLED = 'Y' AND BNKAC_AC_TYPE = '1'
            ORDER BY BNKAC_CODE
        ";

        return static::executeRawQuery($sql, [], $connectionName);
    }

    /**
     * Get account information by key
     *
     * @param int $accountKey
     * @param string|null $connectionName
     * @return object|null
     */
    public static function getAccountInfo($accountKey, $connectionName = null)
    {
        $sql = "
            SELECT BNKAC_KEY, BNKAC_CODE, BNKAC_NAME, BNKAC_AC_TYPE
            FROM BANKACCOUNT
            WHERE BNKAC_KEY = ?
        ";

        $results = static::executeRawQuery($sql, [$accountKey], $connectionName);

        return $results[0] ?? null;
    }

    /**
     * Get account summary with balances as of a specific date
     * Groups accounts by type and calculates ending balances
     *
     * @param string $asOfDate Format: Y-m-d
     * @param string|null $connectionName
     * @return array ['summary' => [...], 'grand_total' => float, 'all_accounts' => [...]]
     */
    public static function getAccountSummary($asOfDate, $connectionName = null)
    {
        $sql = "
            DECLARE @AsOfDate DATE = ?;
            ;WITH Period AS (
                SELECT BSTMP_BNKAC, BSTMP_ST_DATE, BSTMP_EN_DATE, BSTMP_TOWARD AS CarryOver
                FROM BSTMPERIOD
                WHERE @AsOfDate BETWEEN BSTMP_ST_DATE AND BSTMP_EN_DATE
            ),
            Movements AS (
                SELECT
                    S.BSTM_BNKAC,
                    SUM(ISNULL(S.BSTM_A_DEBIT,0) - ISNULL(S.BSTM_A_CREDIT,0)) AS NetChange,
                    MAX(S.BSTM_RECNL_DD) AS LastTxnDate
                FROM BANKSTATEMENT S
                INNER JOIN Period P ON P.BSTMP_BNKAC = S.BSTM_BNKAC
                    AND S.BSTM_RECNL_DD BETWEEN P.BSTMP_ST_DATE AND @AsOfDate
                GROUP BY S.BSTM_BNKAC
            )
            SELECT
                BA.BNKAC_CODE,
                BA.BNKAC_NAME,
                BA.BNKAC_AC_TYPE,
                CASE
                    WHEN BA.BNKAC_AC_TYPE = '1' THEN N'กระแสรายวัน'
                    WHEN BA.BNKAC_AC_TYPE = '2' THEN N'ออมทรัพย์'
                    ELSE N'ไม่ระบุ'
                END AS AccountTypeName,
                P.BSTMP_ST_DATE AS StartDate,
                @AsOfDate AS EndDate,
                CAST(ISNULL(P.CarryOver,0) + ISNULL(M.NetChange,0) AS DECIMAL(18,2)) AS EndingBalance,
                M.LastTxnDate
            FROM BANKACCOUNT BA
            LEFT JOIN Period P ON P.BSTMP_BNKAC = CONVERT(VARCHAR(10), BA.BNKAC_KEY)
            LEFT JOIN Movements M ON M.BSTM_BNKAC = CONVERT(VARCHAR(10), BA.BNKAC_KEY)
            WHERE BA.BNKAC_ENABLED = 'Y'
            ORDER BY BA.BNKAC_AC_TYPE, BA.BNKAC_CODE
        ";

        $accounts = static::executeRawQuery($sql, [$asOfDate], $connectionName);

        // Group by account type and calculate totals
        return static::groupAccountsByType($accounts);
    }

    /**
     * Group accounts by type and calculate totals
     *
     * @param array $accounts
     * @return array
     */
    protected static function groupAccountsByType($accounts)
    {
        $summary = [
            'current' => ['label' => 'กระแสรายวัน', 'accounts' => [], 'total' => 0],
            'savings' => ['label' => 'ออมทรัพย์', 'accounts' => [], 'total' => 0],
            'other' => ['label' => 'ไม่ระบุ', 'accounts' => [], 'total' => 0]
        ];

        $grandTotal = 0;

        foreach ($accounts as $account) {
            $balance = $account->EndingBalance ?? 0;
            $grandTotal += $balance;

            if ($account->BNKAC_AC_TYPE == '1') {
                $summary['current']['accounts'][] = $account;
                $summary['current']['total'] += $balance;
            } elseif ($account->BNKAC_AC_TYPE == '2') {
                $summary['savings']['accounts'][] = $account;
                $summary['savings']['total'] += $balance;
            } else {
                $summary['other']['accounts'][] = $account;
                $summary['other']['total'] += $balance;
            }
        }

        return [
            'summary' => $summary,
            'all_accounts' => $accounts,
            'grand_total' => $grandTotal
        ];
    }
}
