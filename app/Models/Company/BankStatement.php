<?php

namespace App\Models\Company;

/**
 * BankStatement Model
 *
 * Represents BANKSTATEMENT table in company MSSQL databases
 * Handles bank statement transactions and balances
 */
class BankStatement extends CompanyModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'BANKSTATEMENT';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'BSTM_KEY';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'BSTM_KEY' => 'integer',
        'BSTM_RECNL_DD' => 'date',
        'BSTM_DEBIT' => 'decimal:2',
        'BSTM_CREDIT' => 'decimal:2',
        'BSTM_A_DEBIT' => 'decimal:2',
        'BSTM_A_CREDIT' => 'decimal:2',
    ];

    /**
     * Relationships
     */

    /**
     * Get the bank account that owns this statement
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'BSTM_BNKAC', 'BNKAC_KEY');
    }

    /**
     * Get the period this statement belongs to
     */
    public function period()
    {
        return $this->belongsTo(BankStatementPeriod::class, 'BSTM_BNKAC', 'BSTMP_BNKAC');
    }

    /**
     * Get bank statement for a specific period (year/month) with running balance
     *
     * @param int|string $accountKey
     * @param int $year
     * @param int $month
     * @param string|null $connectionName
     * @return array ['statements' => [...], 'summary' => [...]]
     */
    public static function getStatementForPeriod($accountKey, $year, $month, $connectionName = null)
    {
        $sql = "
            DECLARE @BNKAC VARCHAR(10) = ?;
            DECLARE @YEAR INT = ?;
            DECLARE @MONTH INT = ?;

            -- หาช่วงวันและยอดยกมาของเดือนที่เลือก
            WITH Period AS (
                SELECT TOP 1
                    BSTMP_ST_DATE,
                    BSTMP_EN_DATE,
                    BSTMP_TOWARD AS CarryOver
                FROM BSTMPERIOD
                WHERE BSTMP_BNKAC = @BNKAC
                    AND BSTMP_YEAR = @YEAR
                    AND BSTMP_MONTH = @MONTH
            )
            , MonthTrans AS (
                SELECT
                    *,
                    ROW_NUMBER() OVER (ORDER BY BSTM_RECNL_DD, BSTM_SHOW_ORDER, BSTM_KEY, BSTM_CHEQUE_NO, BSTM_TYPE) AS rn
                FROM BANKSTATEMENT, Period
                WHERE BSTM_BNKAC = @BNKAC
                    AND BSTM_RECNL_DD BETWEEN Period.BSTMP_ST_DATE AND Period.BSTMP_EN_DATE
            )

            -- ผลลัพธ์
            SELECT
                0 AS BSTM_KEY,
                @BNKAC AS BSTM_BNKAC,
                Period.BSTMP_ST_DATE AS BSTM_RECNL_DD,
                N'ยอดยกมา' AS Statusx,
                '' AS BSTM_CHEQUE_NO,
                NULL AS BSTM_DEBIT,
                NULL AS BSTM_CREDIT,
                Period.CarryOver AS Balance,
                N'ยอดยกมา' AS BSTM_REMARK
            FROM Period

            UNION ALL

            SELECT
                T.BSTM_KEY,
                T.BSTM_BNKAC,
                T.BSTM_RECNL_DD,
                CASE
                    WHEN T.BSTM_TYPE='1' THEN N'ฝาก'
                    WHEN T.BSTM_TYPE='104' THEN N'เช็คผ่าน'
                    WHEN T.BSTM_TYPE='103' THEN N'ค่าธรรมเนียม'
                    ELSE ''
                END AS Statusx,
                ISNULL(T.BSTM_CHEQUE_NO,'') AS BSTM_CHEQUE_NO,
                T.BSTM_DEBIT,
                T.BSTM_CREDIT,
                Period.CarryOver +
                    SUM(T.BSTM_DEBIT - T.BSTM_CREDIT) OVER (
                        PARTITION BY T.BSTM_BNKAC
                        ORDER BY T.BSTM_RECNL_DD, T.BSTM_SHOW_ORDER, T.BSTM_KEY, T.BSTM_CHEQUE_NO, T.BSTM_TYPE
                        ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                    ) AS Balance,
                T.BSTM_REMARK
            FROM MonthTrans T
            CROSS JOIN Period

            ORDER BY BSTM_RECNL_DD, BSTM_KEY, BSTM_CHEQUE_NO, Statusx
        ";

        $statements = static::executeRawQuery($sql, [$accountKey, $year, $month], $connectionName);

        // Calculate summary
        $summary = static::calculateStatementSummary($statements);

        return [
            'statements' => $statements,
            'summary' => $summary
        ];
    }

    /**
     * Calculate summary from statement rows
     *
     * @param array $statements
     * @return array
     */
    protected static function calculateStatementSummary($statements)
    {
        $totalDebit = 0;
        $totalCredit = 0;
        $carryOver = 0;
        $endingBalance = 0;

        foreach ($statements as $row) {
            if ($row->BSTM_KEY == 0) {
                // ยอดยกมา
                $carryOver = $row->Balance ?? 0;
            } else {
                // รายการปกติ
                $totalDebit += $row->BSTM_DEBIT ?? 0;
                $totalCredit += $row->BSTM_CREDIT ?? 0;
                $endingBalance = $row->Balance ?? 0;
            }
        }

        return [
            'carry_over' => $carryOver,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'ending_balance' => $endingBalance
        ];
    }

    /**
     * Get month summary (for specific account, year, month)
     *
     * @param int|string $accountKey
     * @param int $year
     * @param int $month
     * @param string|null $connectionName
     * @return array
     */
    public static function getMonthSummary($accountKey, $year, $month, $connectionName = null)
    {
        $result = static::getStatementForPeriod($accountKey, $year, $month, $connectionName);
        return $result['summary'];
    }
}
