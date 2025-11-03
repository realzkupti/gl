<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Base model for all company-specific models that connect to MSSQL Server databases
 *
 * Each company has its own MSSQL database with connection name: company_{company_key}
 */
abstract class CompanyModel extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The connection name is dynamically set based on current company
     * Override this in child classes if needed
     *
     * @var string|null
     */
    protected $connection = null;

    /**
     * Get the current company from authenticated user
     *
     * @return \App\Models\Company|null
     */
    protected static function getCurrentCompany()
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::user()->getCurrentCompany();
    }

    /**
     * Get connection name for current company
     *
     * @return string|null
     */
    protected static function getCompanyConnectionName()
    {
        $company = static::getCurrentCompany();

        if (!$company) {
            return null;
        }

        return 'company_' . $company->key;
    }

    /**
     * Set the connection for the model based on company
     *
     * @param string|null $companyKey
     * @return static
     */
    public static function onCompany($companyKey = null)
    {
        $instance = new static;

        if ($companyKey) {
            $connectionName = 'company_' . $companyKey;
        } else {
            $connectionName = static::getCompanyConnectionName();
        }

        $instance->setConnection($connectionName);

        return $instance->newQuery();
    }

    /**
     * Begin querying the model on the current company's connection.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        $builder = parent::newEloquentBuilder($query);

        // Auto-set connection if not already set
        if (!$this->connection) {
            $connectionName = static::getCompanyConnectionName();
            if ($connectionName) {
                $this->setConnection($connectionName);
            }
        }

        return $builder;
    }

    /**
     * Execute a raw SQL query on the company database
     *
     * @param string $sql
     * @param array $bindings
     * @param string|null $connectionName Override connection
     * @return array
     */
    protected static function executeRawQuery($sql, $bindings = [], $connectionName = null)
    {
        $connection = $connectionName ?? static::getCompanyConnectionName();

        if (!$connection) {
            throw new \RuntimeException('No company connection available');
        }

        return \DB::connection($connection)->select($sql, $bindings);
    }

    /**
     * Get the database connection for the current company
     *
     * @param string|null $connectionName
     * @return \Illuminate\Database\Connection
     */
    protected static function getCompanyConnection($connectionName = null)
    {
        $connection = $connectionName ?? static::getCompanyConnectionName();

        if (!$connection) {
            throw new \RuntimeException('No company connection available');
        }

        return \DB::connection($connection);
    }
}
