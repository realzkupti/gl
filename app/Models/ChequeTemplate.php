<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserTracking;

/**
 * ChequeTemplate Model
 *
 * Stores cheque layout templates for different banks.
 * Always uses PostgreSQL connection.
 */
class ChequeTemplate extends Model
{
    use HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_cheque_templates';

    protected $fillable = [
        'bank',
        'template_json'
    ];

    protected $casts = [
        'template_json' => 'array',
    ];

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return 'pgsql';
    }
}
