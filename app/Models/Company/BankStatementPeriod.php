<?php

namespace App\Models\Company;

/**
 * BankStatementPeriod Model
 *
 * Represents BSTMPERIOD table in company MSSQL databases
 * Handles bank statement period information and carry-over balances
 */
class BankStatementPeriod extends CompanyModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'BSTMPERIOD';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'BSTMP_KEY';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'BSTMP_KEY' => 'integer',
        'BSTMP_ST_DATE' => 'date',
        'BSTMP_EN_DATE' => 'date',
        'BSTMP_TOWARD' => 'decimal:2',
        'BSTMP_YEAR' => 'integer',
        'BSTMP_MONTH' => 'integer',
    ];

    /**
     * Relationships
     */

    /**
     * Get the bank account this period belongs to
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'BSTMP_BNKAC', 'BNKAC_KEY');
    }

    /**
     * Get statements for this period
     */
    public function statements()
    {
        return $this->hasMany(BankStatement::class, 'BSTM_BNKAC', 'BSTMP_BNKAC')
                    ->whereBetween('BSTM_RECNL_DD', [$this->BSTMP_ST_DATE, $this->BSTMP_EN_DATE]);
    }

    /**
     * Get period for a specific date
     *
     * @param int|string $accountKey
     * @param string $date Format: Y-m-d
     * @param string|null $connectionName
     * @return object|null
     */
    public static function getPeriodForDate($accountKey, $date, $connectionName = null)
    {
        $sql = "
            SELECT
                BSTMP_BNKAC,
                BSTMP_ST_DATE,
                BSTMP_EN_DATE,
                BSTMP_TOWARD AS CarryOver,
                BSTMP_YEAR,
                BSTMP_MONTH
            FROM BSTMPERIOD
            WHERE BSTMP_BNKAC = ?
                AND ? BETWEEN BSTMP_ST_DATE AND BSTMP_EN_DATE
        ";

        $results = static::executeRawQuery($sql, [$accountKey, $date], $connectionName);

        return $results[0] ?? null;
    }

    /**
     * Get period by year and month
     *
     * @param int|string $accountKey
     * @param int $year
     * @param int $month
     * @param string|null $connectionName
     * @return object|null
     */
    public static function getPeriodByYearMonth($accountKey, $year, $month, $connectionName = null)
    {
        $sql = "
            SELECT TOP 1
                BSTMP_BNKAC,
                BSTMP_ST_DATE,
                BSTMP_EN_DATE,
                BSTMP_TOWARD AS CarryOver,
                BSTMP_YEAR,
                BSTMP_MONTH
            FROM BSTMPERIOD
            WHERE BSTMP_BNKAC = ?
                AND BSTMP_YEAR = ?
                AND BSTMP_MONTH = ?
        ";

        $results = static::executeRawQuery($sql, [$accountKey, $year, $month], $connectionName);

        return $results[0] ?? null;
    }

    /**
     * Get carry-over balance for a period
     *
     * @param int|string $accountKey
     * @param int $year
     * @param int $month
     * @param string|null $connectionName
     * @return float
     */
    public static function getCarryOverBalance($accountKey, $year, $month, $connectionName = null)
    {
        $period = static::getPeriodByYearMonth($accountKey, $year, $month, $connectionName);

        return $period->CarryOver ?? 0;
    }
}
