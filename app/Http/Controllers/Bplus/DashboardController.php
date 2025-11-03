<?php

namespace App\Http\Controllers\Bplus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the Bplus Dashboard
     */
    public function index()
    {
        $company = auth()->user()?->getCurrentCompany();

        // Log for debugging
        \Log::info('Bplus Dashboard accessed', [
            'user_id' => auth()->id(),
            'company_id' => $company?->id,
            'company_name' => $company?->label,
        ]);

        return view('bplus.dashboard', compact('company'));
    }

    /**
     * Test database connection for current company
     */
    public function testConnection(Request $request)
    {
        $company = auth()->user()?->getCurrentCompany();

        \Log::info("Testing connection for company ID: " . ($company?->id ?? 'null'));

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาเลือกบริษัทก่อนทดสอบการเชื่อมต่อ'
            ], 400);
        }

        try {
            \Log::info("Company found: {$company->label}, Driver: {$company->driver}");

            // Try to decrypt password, if fails, use as plain text (for old data)
            $password = null;
            if (!empty($company->password)) {
                try {
                    $password = \Illuminate\Support\Facades\Crypt::decryptString($company->password);
                    \Log::info("Password decrypted successfully for company {$company->id}");

                    // Check if decrypted password is empty
                    if (empty($password)) {
                        \Log::warning("Decrypted password is empty for company {$company->id}");
                        $password = null;
                    }
                } catch (\Exception $e) {
                    // Password is not encrypted or corrupted, try using as is
                    \Log::warning("Could not decrypt password for company {$company->id}: " . $e->getMessage());
                    \Log::info("Attempting to use password as plain text");

                    // Only use plain text if it's not empty
                    if (!empty($company->password)) {
                        $password = $company->password;
                    } else {
                        $password = null;
                    }
                }
            } else {
                \Log::warning("Company {$company->id} has empty password field");
                $password = null;
            }

            // If password is still null or empty, we cannot proceed
            if ($password === null || $password === '') {
                throw new \Exception("Password is empty or could not be decrypted. Please update the company password.");
            }

            $startTime = microtime(true);

            $config = [
                'driver' => $company->driver,
                'host' => $company->host,
                'port' => $company->port,
                'database' => $company->database,
                'username' => $company->username,
                'password' => $password,
                'charset' => $company->charset ?? 'utf8',
                'collation' => $company->collation,
            ];

            // Add driver-specific configuration
            if ($company->driver === 'sqlsrv') {
                $config['options'] = [
                    'TrustServerCertificate' => true,
                ];
            }

            \Log::info("Attempting connection with config: " . json_encode([
                'driver' => $config['driver'],
                'host' => $config['host'],
                'port' => $config['port'],
                'database' => $config['database'],
                'username' => $config['username'],
            ]));

            config(['database.connections.test_connection_bplus' => $config]);
            $pdo = \DB::connection('test_connection_bplus')->getPdo();

            \Log::info("Connection successful! PDO Driver: " . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));

            // Try to get database version
            $version = \DB::connection('test_connection_bplus')->selectOne('SELECT @@VERSION as version');

            // Get database name
            $database = \DB::connection('test_connection_bplus')->getDatabaseName();

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            \DB::purge('test_connection_bplus'); // Clean up

            return response()->json([
                'success' => true,
                'message' => 'เชื่อมต่อฐานข้อมูลสำเร็จ',
                'database' => $database,
                'host' => $company->host,
                'version' => $version->version ?? 'Unknown',
                'response_time' => $responseTime
            ]);

        } catch (\Exception $e) {
            \Log::error("Connection test failed: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
