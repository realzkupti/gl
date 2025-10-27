<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cheque Model
 *
 * This model ALWAYS uses PostgreSQL connection regardless of
 * the current company database settings.
 *
 * Cheque system is centralized in the main PostgreSQL database
 * for personal use by the administrator.
 */
class Cheque extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'pgsql';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_cheques';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_code',
        'bank',
        'cheque_number',
        'date',
        'payee',
        'amount',
        'printed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'printed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the current connection name for the model.
     *
     * This method ensures that the model ALWAYS uses PostgreSQL
     * even if the default connection is changed by CompanyManager.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return 'pgsql';
    }

    /**
     * Relationship: Cheque belongs to a Branch
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_code', 'code');
    }
}
