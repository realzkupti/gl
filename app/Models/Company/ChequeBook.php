<?php

namespace App\Models\Company;

/**
 * ChequeBook Model
 *
 * Represents CHEQUEBOOK table in company MSSQL databases
 * Handles cheque information, due dates, and payment tracking
 */
class ChequeBook extends CompanyModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'CHEQUEBOOK';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'CQBK_KEY';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'CQBK_KEY' => 'integer',
        'CQBK_CHEQUE_DD' => 'date',
        'CQBK_REFER_DATE' => 'date',
        'CQBK_AMT' => 'decimal:2',
        'CQBK_A_AMT' => 'decimal:2',
    ];

    /**
     * Scope: Pending cheques (outstanding amount > 0)
     */
    public function scopePending($query)
    {
        return $query->where('CQBK_A_AMT', '>', 0);
    }

    /**
     * Scope: Cleared cheques (outstanding amount = 0 but has cheque amount)
     */
    public function scopeCleared($query)
    {
        return $query->where('CQBK_A_AMT', 0)
                    ->where('CQBK_AMT', '>', 0);
    }

    /**
     * Scope: Overdue cheques
     */
    public function scopeOverdue($query)
    {
        return $query->where('CQBK_CHEQUE_DD', '<', now()->format('Y-m-d'))
                    ->where('CQBK_A_AMT', '>', 0);
    }

    /**
     * Relationships
     */

    /**
     * Get the bank account this cheque belongs to
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'CQBK_BNKAC', 'BNKAC_KEY');
    }

    /**
     * Get cheque summary grouped by period buckets
     *
     * @param string $asOfDate Format: Y-m-d
     * @param string|null $connectionName
     * @return array ['summary' => [...], 'total' => object]
     */
    public static function getSummaryByPeriod($asOfDate, $connectionName = null)
    {
        // Summary by period buckets
        $sql = "
            DECLARE @AsOfDate DATE = ?;

            ;WITH Base AS (
                SELECT
                    CQBK_REFER_REF,
                    CQBK_CHEQUE_DD,
                    CQBK_REFER_DATE,
                    TRY_CONVERT(DECIMAL(18,2), CQBK_AMT)   AS ChequeAmt,
                    TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT) AS OutstandingAmt,
                    DATEDIFF(DAY, @AsOfDate, CQBK_CHEQUE_DD) AS DueInDays
                FROM dbo.CHEQUEBOOK
                WHERE ISNULL(CQBK_REFER_REF,'') <> ''
                  AND TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT) <> 0
            ),
            Bucketed AS (
                SELECT
                    CASE
                        WHEN CQBK_CHEQUE_DD <  @AsOfDate                   THEN N'เกินกำหนด'
                        WHEN DueInDays BETWEEN 0  AND 7                    THEN N'ภายใน 7 วัน'
                        WHEN DueInDays BETWEEN 8  AND 14                   THEN N'8–14 วัน'
                        WHEN DueInDays BETWEEN 15 AND 30                   THEN N'15–30 วัน'
                        ELSE N'มากกว่า 30 วัน'
                    END AS PeriodBucket,
                    ChequeAmt,
                    OutstandingAmt
                FROM Base
            )
            SELECT
                PeriodBucket,
                COUNT(*) AS ChequeCount,
                SUM(ChequeAmt) AS TotalChequeAmt,
                SUM(OutstandingAmt) AS TotalOutstandingAmt
            FROM Bucketed
            GROUP BY PeriodBucket
            ORDER BY CASE PeriodBucket
                        WHEN N'เกินกำหนด'       THEN 1
                        WHEN N'ภายใน 7 วัน'     THEN 2
                        WHEN N'8–14 วัน'         THEN 3
                        WHEN N'15–30 วัน'        THEN 4
                        ELSE 5
                     END
        ";

        $summary = static::executeRawQuery($sql, [$asOfDate], $connectionName);

        // Total outstanding
        $totalSql = "
            SELECT
                COUNT(*) AS TotalCount,
                SUM(TRY_CONVERT(DECIMAL(18,2), CQBK_AMT)) AS TotalAmt,
                SUM(TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT)) AS TotalOutstanding
            FROM [dbo].[CHEQUEBOOK]
            WHERE ISNULL(CQBK_REFER_REF,'') != ''
              AND CQBK_A_AMT!='0'
        ";

        $total = static::executeRawQuery($totalSql, [], $connectionName);

        return [
            'summary' => $summary,
            'total' => $total[0] ?? null
        ];
    }

    /**
     * Get cheques with filters and period bucketing
     *
     * @param array $filters ['period' => 'all|overdue|7days|14days|30days', 'account' => 'key', 'status' => 'pending|cleared|all']
     * @param string|null $connectionName
     * @return array
     */
    public static function getCheques($filters = [], $connectionName = null)
    {
        $period = $filters['period'] ?? 'all';
        $accountKey = $filters['account'] ?? null;
        $status = $filters['status'] ?? 'pending';

        $sql = "
            DECLARE @Today DATE = CAST(GETDATE() AS DATE);

            SELECT
                CQBK_REFER_REF AS ReferenceNo,
                CQBK_CHEQUE_NO AS ChequeNo,
                CQBK_CHEQUE_DD AS ChequeDate,
                CQBK_AMT AS Amount,
                CQBK_A_AMT AS OutstandingAmount,
                CQBK_PAY AS Payee,
                CQBK_BNKAC AS BankAccountKey,
                DATEDIFF(DAY, @Today, CQBK_CHEQUE_DD) AS DaysUntilDue,
                CASE
                    WHEN CQBK_CHEQUE_DD < @Today THEN N'เกินกำหนด'
                    WHEN DATEDIFF(DAY, @Today, CQBK_CHEQUE_DD) <= 7 THEN N'ภายใน 7 วัน'
                    WHEN DATEDIFF(DAY, @Today, CQBK_CHEQUE_DD) BETWEEN 8 AND 14 THEN N'8–14 วัน'
                    WHEN DATEDIFF(DAY, @Today, CQBK_CHEQUE_DD) BETWEEN 15 AND 30 THEN N'15–30 วัน'
                    ELSE N'มากกว่า 30 วัน'
                END AS PeriodBucket
            FROM CHEQUEBOOK
             WHERE ISNULL(CQBK_REFER_REF,'') <> ''
            AND TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT) <> 0
        ";

        $params = [];

        // Add status filter
        if ($status === 'pending') {
            $sql .= " AND CQBK_A_AMT > 0";
        } elseif ($status === 'cleared') {
            $sql .= " AND CQBK_A_AMT = 0 AND CQBK_AMT > 0";
        }

        // Add account filter if provided
        if ($accountKey) {
            $sql .= " AND CQBK_BNKAC = ?";
            $params[] = $accountKey;
        }

        // Add period filter
        switch ($period) {
            case 'overdue':
                $sql .= " AND CQBK_CHEQUE_DD < @Today";
                break;
            case '7days':
                $sql .= " AND DATEDIFF(DAY, @Today, CQBK_CHEQUE_DD) BETWEEN 0 AND 7";
                break;
            case '14days':
                $sql .= " AND DATEDIFF(DAY, @Today, CQBK_CHEQUE_DD) BETWEEN 8 AND 14";
                break;
            case '30days':
                $sql .= " AND DATEDIFF(DAY, @Today, CQBK_CHEQUE_DD) BETWEEN 15 AND 30";
                break;
            case 'all':
            default:
                // No additional filter
                break;
        }

        $sql .= " ORDER BY CQBK_CHEQUE_DD ASC, CQBK_CHEQUE_NO ASC";

        return static::executeRawQuery($sql, $params, $connectionName);
    }

    /**
     * Calculate period summary from cheques array
     *
     * @param array $cheques
     * @return array
     */
    public static function calculatePeriodSummary($cheques)
    {
        $periodSummary = [
            'all' => ['count' => 0, 'amount' => 0],
            'overdue' => ['count' => 0, 'amount' => 0],
            '7days' => ['count' => 0, 'amount' => 0],
            '14days' => ['count' => 0, 'amount' => 0],
            '30days' => ['count' => 0, 'amount' => 0]
        ];

        foreach ($cheques as $cheque) {
            $amount = $cheque->OutstandingAmount ?? 0;

            // All
            $periodSummary['all']['count']++;
            $periodSummary['all']['amount'] += $amount;

            // Group by period
            if ($cheque->PeriodBucket === 'เกินกำหนด') {
                $periodSummary['overdue']['count']++;
                $periodSummary['overdue']['amount'] += $amount;
            } elseif ($cheque->PeriodBucket === 'ภายใน 7 วัน') {
                $periodSummary['7days']['count']++;
                $periodSummary['7days']['amount'] += $amount;
            } elseif ($cheque->PeriodBucket === '8–14 วัน') {
                $periodSummary['14days']['count']++;
                $periodSummary['14days']['amount'] += $amount;
            } elseif ($cheque->PeriodBucket === '15–30 วัน') {
                $periodSummary['30days']['count']++;
                $periodSummary['30days']['amount'] += $amount;
            }
        }

        return $periodSummary;
    }

    /**
     * Get cheques grouped by due date periods (detailed)
     *
     * @param string $asOfDate Format: Y-m-d
     * @param string|null $connectionName
     * @return array
     */
    public static function getChequesByDueDate($asOfDate, $connectionName = null)
    {
        $sql = "
            DECLARE @AsOfDate DATE = ?;

            ;WITH Base AS (
                SELECT
                    CQBK_REFER_REF,
                    CQBK_CHEQUE_NO,
                    CQBK_CHEQUE_DD,
                    CQBK_PAY,
                    TRY_CONVERT(DECIMAL(18,2), CQBK_AMT)   AS ChequeAmt,
                    TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT) AS OutstandingAmt,
                    DATEDIFF(DAY, @AsOfDate, CQBK_CHEQUE_DD) AS DueInDays
                FROM dbo.CHEQUEBOOK
                WHERE ISNULL(CQBK_REFER_REF,'') <> ''
                  AND TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT) <> 0
            )
            SELECT
                CQBK_REFER_REF AS ReferenceNo,
                CQBK_CHEQUE_NO AS ChequeNo,
                CQBK_CHEQUE_DD AS ChequeDate,
                CQBK_PAY AS Payee,
                ChequeAmt,
                OutstandingAmt,
                DueInDays,
                CASE
                    WHEN CQBK_CHEQUE_DD <  @AsOfDate                   THEN N'เกินกำหนด'
                    WHEN DueInDays BETWEEN 0  AND 7                    THEN N'ภายใน 7 วัน'
                    WHEN DueInDays BETWEEN 8  AND 14                   THEN N'8–14 วัน'
                    WHEN DueInDays BETWEEN 15 AND 30                   THEN N'15–30 วัน'
                    ELSE N'มากกว่า 30 วัน'
                END AS PeriodBucket
            FROM Base
            ORDER BY CASE
                        WHEN CQBK_CHEQUE_DD <  @AsOfDate                   THEN 1
                        WHEN DueInDays BETWEEN 0  AND 7                    THEN 2
                        WHEN DueInDays BETWEEN 8  AND 14                   THEN 3
                        WHEN DueInDays BETWEEN 15 AND 30                   THEN 4
                        ELSE 5
                     END,
                     CQBK_CHEQUE_DD ASC
        ";

        return static::executeRawQuery($sql, [$asOfDate], $connectionName);
    }

    /**
     * Get cheques grouped by payee
     *
     * @param string $asOfDate Format: Y-m-d
     * @param string|null $connectionName
     * @return array
     */
    public static function getChequesByPayee($asOfDate, $connectionName = null)
    {
        $sql = "
            DECLARE @AsOfDate DATE = ?;

            ;WITH Base AS (
                SELECT
                    CQBK_PAY,
                    TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT) AS OutstandingAmt,
                    DATEDIFF(DAY, @AsOfDate, CQBK_CHEQUE_DD) AS DueInDays,
                    CQBK_CHEQUE_DD
                FROM dbo.CHEQUEBOOK
                WHERE ISNULL(CQBK_REFER_REF,'') <> ''
                  AND TRY_CONVERT(DECIMAL(18,2), CQBK_A_AMT) <> 0
            )
            SELECT
                CQBK_PAY AS Payee,
                COUNT(*) AS TotalCheques,
                SUM(OutstandingAmt) AS TotalAmount,
                SUM(CASE WHEN CQBK_CHEQUE_DD < @AsOfDate THEN OutstandingAmt ELSE 0 END) AS OverdueAmount,
                SUM(CASE WHEN DueInDays BETWEEN 0 AND 7 THEN OutstandingAmt ELSE 0 END) AS Within7DaysAmount,
                SUM(CASE WHEN DueInDays BETWEEN 8 AND 14 THEN OutstandingAmt ELSE 0 END) AS Within14DaysAmount,
                SUM(CASE WHEN DueInDays BETWEEN 15 AND 30 THEN OutstandingAmt ELSE 0 END) AS Within30DaysAmount,
                SUM(CASE WHEN DueInDays > 30 THEN OutstandingAmt ELSE 0 END) AS Beyond30DaysAmount
            FROM Base
            GROUP BY CQBK_PAY
            ORDER BY TotalAmount DESC
        ";

        return static::executeRawQuery($sql, [$asOfDate], $connectionName);
    }
}
