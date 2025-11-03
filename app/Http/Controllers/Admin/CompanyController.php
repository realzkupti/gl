<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    protected function ensureAdmin()
    {
        if (!Auth::check() ) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        $companies = Company::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.companies', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'key' => 'required|string|max:50|unique:pgsql.sys_companies,key',
            'label' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'driver' => 'required|in:sqlsrv,mysql,pgsql',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'database' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
            'charset' => 'nullable|string|max:20',
            'collation' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company-logos', 'public');
        }

        $company = Company::create([
            'key' => $data['key'],
            'label' => $data['label'],
            'logo' => $logoPath,
            'driver' => $data['driver'],
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            'password' => Crypt::encryptString($data['password']),
            'charset' => $data['charset'] ?? 'utf8',
            'collation' => $data['collation'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'เพิ่มบริษัทเรียบร้อยแล้ว',
                'company' => $company
            ]);
        }

        return redirect()->route('admin.companies')->with('status', 'เพิ่มบริษัทเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        $company = Company::findOrFail($id);

        $data = $request->validate([
            'key' => 'required|string|max:50|unique:pgsql.sys_companies,key,' . $id,
            'label' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_logo' => 'nullable|in:0,1',
            'driver' => 'required|in:sqlsrv,mysql,pgsql',
            'host' => 'required|string|max:255',
            'port' => 'required|integer',
            'database' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'password' => 'nullable|string|max:255',
            'charset' => 'nullable|string|max:20',
            'collation' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $updateData = [
            'key' => $data['key'],
            'label' => $data['label'],
            'driver' => $data['driver'],
            'host' => $data['host'],
            'port' => $data['port'],
            'database' => $data['database'],
            'username' => $data['username'],
            'charset' => $data['charset'] ?? 'utf8',
            'collation' => $data['collation'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        // Handle logo upload/removal
        if ($request->input('remove_logo') == '1') {
            // Remove old logo file if exists
            if ($company->logo && \Storage::disk('public')->exists($company->logo)) {
                \Storage::disk('public')->delete($company->logo);
            }
            $updateData['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo && \Storage::disk('public')->exists($company->logo)) {
                \Storage::disk('public')->delete($company->logo);
            }
            // Upload new logo
            $updateData['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Crypt::encryptString($data['password']);
        }

        $company->update($updateData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'อัปเดตบริษัทเรียบร้อยแล้ว',
                'company' => $company->fresh()
            ]);
        }

        return redirect()->route('admin.companies')->with('status', 'อัปเดตบริษัทเรียบร้อยแล้ว');
    }

    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin();

        $company = Company::findOrFail($id);
        $company->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ลบบริษัทเรียบร้อยแล้ว'
            ]);
        }

        return redirect()->route('admin.companies')->with('status', 'ลบบริษัทเรียบร้อยแล้ว');
    }

    public function toggle(Request $request, $id)
    {
        $this->ensureAdmin();

        $company = Company::findOrFail($id);
        $company->is_active = !$company->is_active;
        $company->save();

        $status = $company->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $status . 'บริษัทเรียบร้อยแล้ว',
                'company' => $company
            ]);
        }

        return redirect()->route('admin.companies')->with('status', $status . 'บริษัทเรียบร้อยแล้ว');
    }

    public function testConnection(Request $request, $id)
    {
        $this->ensureAdmin();

        Log::info("Testing connection for company ID: {$id}");

        try {
            $company = Company::findOrFail($id);

            Log::info("Company found: {$company->label}, Driver: {$company->driver}");

            // Try to decrypt password, if fails, use as plain text (for old data)
            $password = null;
            if (!empty($company->password)) {
                try {
                    $password = Crypt::decryptString($company->password);
                    Log::info("Password decrypted successfully for company {$company->id}");

                    // Check if decrypted password is empty
                    if (empty($password)) {
                        Log::warning("Decrypted password is empty for company {$company->id}");
                        $password = null;
                    }
                } catch (\Exception $e) {
                    // Password is not encrypted or corrupted, try using as is
                    Log::warning("Could not decrypt password for company {$company->id}: " . $e->getMessage());
                    Log::info("Attempting to use password as plain text");

                    // Only use plain text if it's not empty
                    if (!empty($company->password)) {
                        $password = $company->password;
                    } else {
                        $password = null;
                    }
                }
            } else {
                Log::warning("Company {$company->id} has empty password field");
                $password = null;
            }

            // If password is still null or empty, we cannot proceed
            if ($password === null || $password === '') {
                throw new \Exception("Password is empty or could not be decrypted. Please update the company password.");
            }

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

            Log::info("Attempting connection with config: " . json_encode([
                'driver' => $config['driver'],
                'host' => $config['host'],
                'port' => $config['port'],
                'database' => $config['database'],
                'username' => $config['username'],
            ]));

            config(['database.connections.test_connection' => $config]);
            $pdo = DB::connection('test_connection')->getPdo();

            Log::info("Connection successful! PDO Driver: " . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));

            DB::purge('test_connection'); // Clean up

            $message = '✓ เชื่อมต่อสำเร็จ: ' . $company->label . ' (' . $company->driver . ')';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('admin.companies')->with('status', $message);
        } catch (\Exception $e) {
            Log::error("Connection test failed: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            $message = '✗ เชื่อมต่อไม่สำเร็จ: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ], 500);
            }

            return redirect()->route('admin.companies')->with('error', $message);
        }
    }

    /**
     * Switch current company in session
     */
    public function switchCompany(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'company_id' => 'required|integer|exists:pgsql.sys_companies,id',
        ]);

        $company = Company::findOrFail($request->company_id);

        // Check if user has access to this company
        if (!Auth::user()->hasAccessToCompany($company->id)) {
            return response()->json(['success' => false, 'message' => 'No access to this company'], 403);
        }

        // Test database connection before switching
        $connectionName = 'company_' . $company->key;
        $connectionTest = $this->testCompanyConnection($company, $connectionName);

        if (!$connectionTest['success']) {
            Log::error('Failed to switch company - connection test failed', [
                'user_id' => Auth::id(),
                'company_id' => $company->id,
                'company_label' => $company->label,
                'error' => $connectionTest['error']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลของบริษัท: ' . $company->label,
                'error' => $connectionTest['error'],
                'technical_details' => $connectionTest['technical_details'] ?? null
            ], 500);
        }

        // Store company ID in session
        session(['current_company_id' => $company->id]);

        // Force save session to ensure it persists before page reload
        session()->save();

        Log::info('Company switched successfully', [
            'user_id' => Auth::id(),
            'company_id' => $company->id,
            'company_label' => $company->label,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'สลับบริษัทเป็น: ' . $company->label,
            'redirect_url' => route('bplus.dashboard'),
            'company' => [
                'id' => $company->id,
                'key' => $company->key,
                'label' => $company->label,
                'logo' => $company->logo,
            ]
        ]);
    }

    /**
     * Test database connection for a company
     *
     * @param Company $company
     * @param string $connectionName
     * @return array ['success' => bool, 'error' => string|null, 'technical_details' => string|null]
     */
    protected function testCompanyConnection(Company $company, $connectionName)
    {
        try {
            // Set up dynamic connection config
            config(['database.connections.' . $connectionName => [
                'driver' => $company->driver,
                'host' => $company->host,
                'port' => $company->port,
                'database' => $company->database,
                'username' => $company->username,
                'password' => Crypt::decryptString($company->password),
                'charset' => $company->charset ?? 'utf8',
                'collation' => $company->collation ?? null,
            ]]);

            // Test the connection with a simple query
            DB::connection($connectionName)->getPdo();

            // Try a simple SELECT to make sure we can actually query
            $result = DB::connection($connectionName)->select('SELECT 1 AS test');

            if (empty($result)) {
                throw new \Exception('Connection test query returned empty result');
            }

            // Purge the connection after test (optional, to free resources)
            DB::purge($connectionName);

            return [
                'success' => true,
                'error' => null
            ];

        } catch (\PDOException $e) {
            $errorMessage = 'เชื่อมต่อฐานข้อมูลไม่สำเร็จ';

            // Provide more specific error messages
            if (str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]')) {
                $errorMessage = 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ฐานข้อมูลได้ (Host unreachable)';
            } elseif (str_contains($e->getMessage(), 'SQLSTATE[28000]')) {
                $errorMessage = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง (Authentication failed)';
            } elseif (str_contains($e->getMessage(), 'SQLSTATE[42000]')) {
                $errorMessage = 'ไม่สามารถเข้าถึงฐานข้อมูลได้ (Database not found or access denied)';
            } elseif (str_contains($e->getMessage(), 'could not find driver')) {
                $errorMessage = 'ไม่พบไดรเวอร์ฐานข้อมูล (' . $company->driver . ')';
            }

            return [
                'success' => false,
                'error' => $errorMessage,
                'technical_details' => config('app.debug') ? $e->getMessage() : null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'เกิดข้อผิดพลาดในการทดสอบการเชื่อมต่อ',
                'technical_details' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Get list of companies user has access to
     */
    public function getAccessibleCompanies()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $companies = Auth::user()->getAccessibleCompanies();
        $currentCompanyId = session('current_company_id');

        return response()->json([
            'success' => true,
            'companies' => $companies,
            'current_company_id' => $currentCompanyId,
        ]);
    }
}
