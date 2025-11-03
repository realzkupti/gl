<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected function ensureAdmin()
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Display list of users with tabs
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $tab = $request->get('tab', 'active'); // active or pending

        // Get ALL users (both active and pending) for client-side filtering
        $users = User::with(['department', 'companies'])
            ->orderBy('is_active', 'asc') // pending first (is_active=0), then active (is_active=1)
            ->orderBy('created_at', 'desc')
            ->get();

        $departments = Department::orderBy('sort_order')->get();
        $companies = Company::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.users', compact('users', 'departments', 'companies', 'tab'));
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pgsql.sys_users,email',
            'password' => 'required|string|min:6',
            'department_id' => 'required|exists:pgsql.sys_departments,id',
            'is_active' => 'boolean',
            'company_ids' => 'array',
            'company_ids.*' => 'exists:pgsql.sys_companies,id',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'department_id' => $data['department_id'],
                'is_active' => $data['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            // Attach companies
            if (!empty($data['company_ids'])) {
                $user->companies()->attach($data['company_ids']);
            }

            DB::commit();

            return redirect()->route('admin.users')->with('status', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:pgsql.sys_users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'department_id' => 'required|exists:pgsql.sys_departments,id',
            'is_active' => 'boolean',
            'company_ids' => 'array',
            'company_ids.*' => 'exists:pgsql.sys_companies,id',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'department_id' => $data['department_id'],
                'is_active' => $data['is_active'] ?? true,
            ];

            // Only update password if provided
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            // Sync companies
            $user->companies()->sync($data['company_ids'] ?? []);

            DB::commit();

            return redirect()->route('admin.users')->with('status', 'อัปเดตผู้ใช้เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);

        if ($user->email === 'admin@local') {
            // Support both AJAX and traditional requests
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบ admin@local ได้'
                ], 403);
            }
            return back()->with('error', 'ไม่สามารถลบ admin@local ได้');
        }

        $userName = $user->name;
        $user->delete();

        $message = 'ลบผู้ใช้ ' . $userName . ' เรียบร้อยแล้ว';

        // Support both AJAX and traditional requests
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('admin.users')->with('status', $message);
    }

    /**
     * Approve pending user
     */
    public function approve(Request $request, $id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        $user->is_active = true;
        $user->save();

        $message = 'อนุมัติผู้ใช้ ' . $user->name . ' เรียบร้อยแล้ว';

        // Support both AJAX and traditional requests
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('admin.users', ['tab' => 'pending'])
            ->with('status', $message);
    }

    /**
     * Reject pending user
     */
    public function reject(Request $request, $id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);

        if ($user->email === 'admin@local') {
            // Support both AJAX and traditional requests
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถปฏิเสธ admin@local ได้'
                ], 403);
            }
            return back()->with('error', 'ไม่สามารถปฏิเสธ admin@local ได้');
        }

        $userName = $user->name;
        $user->delete();

        $message = 'ปฏิเสธและลบผู้ใช้ ' . $userName . ' เรียบร้อยแล้ว';

        // Support both AJAX and traditional requests
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->route('admin.users', ['tab' => 'pending'])
            ->with('status', $message);
    }

    /**
     * Toggle user active status
     */
    public function toggle($id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);

        if ($user->email === 'admin@local') {
            return back()->with('error', 'ไม่สามารถปิดการใช้งาน admin@local ได้');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
        return redirect()->route('admin.users')->with('status', $status . 'ผู้ใช้เรียบร้อยแล้ว');
    }

    /**
     * Get user counts for badge display
     */
    public function getCounts(Request $request)
    {
        $this->ensureAdmin();

        $activeCount = User::where('is_active', true)->count();
        $pendingCount = User::where('is_active', false)->count();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'active_count' => $activeCount,
                'pending_count' => $pendingCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid request'
        ], 400);
    }
}
