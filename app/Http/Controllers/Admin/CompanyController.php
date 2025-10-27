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
            'key' => 'required|string|max:50|unique:pgsql.companies,key',
            'label' => 'required|string|max:255',
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

        Company::create([
            'key' => $data['key'],
            'label' => $data['label'],
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

        return redirect()->route('admin.companies')->with('status', 'เพิ่มบริษัทเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        $company = Company::findOrFail($id);

        $data = $request->validate([
            'key' => 'required|string|max:50|unique:pgsql.companies,key,' . $id,
            'label' => 'required|string|max:255',
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

        // Only update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Crypt::encryptString($data['password']);
        }

        $company->update($updateData);

        return redirect()->route('admin.companies')->with('status', 'อัปเดตบริษัทเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->route('admin.companies')->with('status', 'ลบบริษัทเรียบร้อยแล้ว');
    }

    public function toggle($id)
    {
        $this->ensureAdmin();

        $company = Company::findOrFail($id);
        $company->is_active = !$company->is_active;
        $company->save();

        $status = $company->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
        return redirect()->route('admin.companies')->with('status', $status . 'บริษัทเรียบร้อยแล้ว');
    }

    public function testConnection($id)
    {
        $this->ensureAdmin();

        $company = Company::findOrFail($id);

        try {
            $config = [
                'driver' => $company->driver,
                'host' => $company->host,
                'port' => $company->port,
                'database' => $company->database,
                'username' => $company->username,
                'password' => Crypt::decryptString($company->password),
                'charset' => $company->charset ?? 'utf8',
                'collation' => $company->collation,
            ];

            config(['database.connections.test_connection' => $config]);
            DB::connection('test_connection')->getPdo();

            return redirect()->route('admin.companies')->with('status', '✓ เชื่อมต่อสำเร็จ: ' . $company->label);
        } catch (\Exception $e) {
            return redirect()->route('admin.companies')->with('error', '✗ เชื่อมต่อไม่สำเร็จ: ' . $e->getMessage());
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
