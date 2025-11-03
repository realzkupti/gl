<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUserTracking;

class Company extends Model
{
    use HasFactory, HasUserTracking;

    protected $connection = 'pgsql';
    protected $table = 'sys_companies';

    protected $fillable = [
        'key', 'label', 'logo', 'driver', 'host', 'port',
        'database', 'username', 'password', 'charset', 'collation', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'port' => 'integer',
    ];

    protected $hidden = [
        'password', // Hide sensitive data
    ];

    /**
     * Get all active companies
     */
    public static function getActiveCompanies()
    {
        return static::where('is_active', true)
            ->orderBy('key')
            ->get();
    }

    /**
     * Get company by key
     */
    public static function findByKey(string $key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * Get company configuration as array (for connection setup)
     */
    public function getConfig(): array
    {
        $config = [
            'key' => $this->key,
            'label' => $this->label,
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => $this->charset ?? ($this->driver === 'mysql' ? 'utf8mb4' : 'utf8'),
            'collation' => $this->collation,
        ];

        // Add SQL Server Driver 18 compatibility settings (works with Driver 17 too)
        if ($this->driver === 'sqlsrv') {
            $config['encrypt'] = 'yes';
            $config['TrustServerCertificate'] = true;
            $config['MultipleActiveResultSets'] = true;
        }

        return $config;
    }

    /**
     * Get all companies as array (format compatible with CompanyManager)
     */
    public static function getAllAsArray(): array
    {
        $companies = static::getActiveCompanies();
        $result = [];

        foreach ($companies as $company) {
            $result[$company->key] = $company->getConfig();
        }

        return $result;
    }
}
