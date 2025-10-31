<?php

namespace App\Http\Middleware;

use App\Services\CompanyManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SetCompanyConnection
{
    public function handle(Request $request, Closure $next)
    {
        // Get company from session (new system using current_company_id)
        $companyId = session('current_company_id');

        Log::debug('SetCompanyConnection middleware', [
            'company_id' => $companyId,
            'url' => $request->url(),
            'session_all' => session()->all(),
        ]);

        if ($companyId) {
            // Get company details from database using pgsql connection
            try {
                $company = \App\Models\Company::on('pgsql')->find($companyId);

                if ($company) {
                    try {
                        // Try to decrypt password
                        // Try both decrypt() and Crypt::decryptString()
                        try {
                            $password = \Illuminate\Support\Facades\Crypt::decryptString($company->password);
                            Log::debug('Password decrypted successfully with Crypt::decryptString', ['company_id' => $company->id]);
                        } catch (\Exception $e1) {
                            try {
                                $password = decrypt($company->password);
                                Log::debug('Password decrypted successfully with decrypt()', ['company_id' => $company->id]);
                            } catch (\Exception $e2) {
                                Log::error('Could not decrypt company password', [
                                    'company_id' => $company->id,
                                    'error1' => $e1->getMessage(),
                                    'error2' => $e2->getMessage(),
                                ]);

                                // Cannot proceed without valid password
                                // Keep using pgsql connection
                                return $next($request);
                            }
                        }

                        // Create dynamic connection name
                        $connectionName = 'company_' . $company->key;

                        // Configure the database connection
                        $connection = [
                            'driver' => $company->driver ?? 'sqlsrv',
                            'host' => $company->host,
                            'port' => $company->port ?? 1433,
                            'database' => $company->database,
                            'username' => $company->username,
                            'password' => $password,
                            'charset' => 'utf8',
                            'prefix' => '',
                            'prefix_indexes' => true,
                        ];

                        // Add driver-specific settings
                        if ($connection['driver'] === 'sqlsrv') {
                            $connection['options'] = [
                                \PDO::SQLSRV_ATTR_ENCODING => \PDO::SQLSRV_ENCODING_UTF8
                            ];
                        }

                        // Register and set as default connection
                        Config::set('database.connections.' . $connectionName, $connection);
                        Config::set('database.default', $connectionName);

                        // Also store in session for legacy compatibility
                        session([
                            'company.key' => $company->key,
                            'company.connection' => $connectionName
                        ]);

                        Log::info('Company connection set', [
                            'company_id' => $company->id,
                            'company_key' => $company->key,
                            'connection_name' => $connectionName,
                            'database' => $company->database,
                        ]);

                    } catch (\Exception $e) {
                        Log::error('Failed to decrypt company password', [
                            'company_id' => $companyId,
                            'error' => $e->getMessage(),
                        ]);

                        // Don't change connection if we fail to set company connection
                        // Keep using pgsql (system database)
                    }
                } else {
                    Log::warning('Company not found', ['company_id' => $companyId]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to load company', [
                    'company_id' => $companyId,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            // No company selected, use legacy system or keep default (pgsql)
            $key = $request->query('company');

            if ($key) {
                CompanyManager::apply($key);
            }
            // If no company and no query param, keep using pgsql (don't change to sqlsrv)
        }

        return $next($request);
    }
}

