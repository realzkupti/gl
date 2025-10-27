<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

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

        $company = Company::findOrFail($id);

        try {
            // Try to decrypt password, if fails, use as plain text (for old data)
            $password = '';
            if (!empty($company->password)) {
                try {
                    $password = Crypt::decryptString($company->password);
                } catch (\Exception $e) {
                    // Password is not encrypted or corrupted, use as is
                    \Log::warning("Could not decrypt password for company {$company->id}, using as plain text");
                    $password = $company->password;
                }
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

            config(['database.connections.test_connection' => $config]);
            DB::connection('test_connection')->getPdo();
            DB::purge('test_connection'); // Clean up

            $message = '✓ เชื่อมต่อสำเร็จ: ' . $company->label;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('admin.companies')->with('status', $message);
        } catch (\Exception $e) {
            $message = '✗ เชื่อมต่อไม่สำเร็จ: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
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

        // Store company ID in session
        session(['current_company_id' => $company->id]);

        return response()->json([
            'success' => true,
            'message' => 'สลับบริษัทเป็น: ' . $company->label,
            'company' => [
                'id' => $company->id,
                'key' => $company->key,
                'label' => $company->label,
            ]
        ]);
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
