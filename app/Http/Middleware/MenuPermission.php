<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuPermission
{
    /**
     * ส่งผลลัพธ์เมื่อถูกปฏิเสธสิทธิ์
     * - ถ้าคาดหวัง JSON: คืน JSON 403 พร้อมข้อความ
     * - ถ้าเป็นหน้าเว็บ: redirect ไปหน้าหลัก พร้อม flash สำหรับ SweetAlert
     */
    protected function deny(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->wantsJson() || str_contains(strtolower($request->header('accept', '')), 'application/json')) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        // เลือกปลายทางที่ปลอดภัย
        $fallback = route('tailadmin.dashboard');
        $prev = url()->previous();
        $to = ($prev && $prev !== $request->fullUrl()) ? $prev : $fallback;
        return redirect($to)->with('forbidden', $message);
    }

    /**
     * ตรวจสิทธิ์เมนูตาม key และ action
     *
     * ลำดับความสำคัญ:
     * 1. Admin override (admin@local)
     * 2. User-specific permissions (override department)
     * 3. Department permissions
     * 4. Deny
     *
     * การจำกัดสิทธิ์บริษัท: ถ้ามีการกำหนดเมนูนั้นไว้ในตาราง sys_user_menu_company_access
     * จะตรวจว่าผู้ใช้เข้าถึงบริษัทที่เลือกอยู่ได้หรือไม่
     *
     * ใช้กับ route: ->middleware('menu:cheque,view')
     */
    public function handle(Request $request, Closure $next, string $menuKey, string $action = 'view')
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 1. Admin override
        $user = Auth::user();
        if (($user->email ?? '') === 'admin@local') {
            return $next($request);
        }

        $userId = Auth::id();

        try {
            $conn = DB::connection('pgsql');

            // หาเมนูเป้าหมาย
            $menu = $conn->table('sys_menus')->where('key', $menuKey)->first(['id']);
            if (!$menu) {
                return $this->deny($request, 'ไม่พบเมนู หรือเมนูนี้ไม่ได้เปิดใช้งาน');
            }

            // 2. ตรวจ user override ก่อน (highest priority)
            $userPerm = $conn->table('sys_user_menu_permissions')
                ->where('user_id', $userId)
                ->where('menu_id', $menu->id)
                ->first();

            $allowedUser = false;
            if ($userPerm) {
                $allowedUser = match ($action) {
                    'view' => (bool)($userPerm->can_view ?? false),
                    'create' => (bool)($userPerm->can_create ?? false),
                    'update' => (bool)($userPerm->can_update ?? false),
                    'delete' => (bool)($userPerm->can_delete ?? false),
                    'export' => (bool)($userPerm->can_export ?? false),
                    'approve' => (bool)($userPerm->can_approve ?? false),
                    default => false,
                };
            }

            // 3. ตรวจตามแผนก (department permissions)
            $allowedDept = false;
            if ($user->department_id) {
                $deptPerm = $conn->table('sys_department_menu_permissions')
                    ->where('department_id', $user->department_id)
                    ->where('menu_id', $menu->id)
                    ->first();

                if ($deptPerm) {
                    $allowedDept = match ($action) {
                        'view' => (bool)($deptPerm->can_view ?? false),
                        'create' => (bool)($deptPerm->can_create ?? false),
                        'update' => (bool)($deptPerm->can_update ?? false),
                        'delete' => (bool)($deptPerm->can_delete ?? false),
                        'export' => (bool)($deptPerm->can_export ?? false),
                        'approve' => (bool)($deptPerm->can_approve ?? false),
                        default => false,
                    };
                }
            }

            // รวมสิทธิ์: user override || department
            $allowed = $allowedUser || $allowedDept;
            if (!$allowed) {
                return $this->deny($request, 'คุณไม่มีสิทธิ์สำหรับเมนูนี้');
            }

            // 4. ตรวจสิทธิ์บริษัท (ถ้ามีการบันทึกจำกัดไว้สำหรับเมนูนี้)
            $hasCompanyConstraint = $conn->table('sys_user_menu_company_access')
                ->where('user_id', $userId)
                ->where('menu_id', $menu->id)
                ->exists();

            if ($hasCompanyConstraint) {
                $currentCompanyKey = session('company.key');
                if ($currentCompanyKey) {
                    $companyId = $conn->table('sys_companies')->where('key', $currentCompanyKey)->value('id');
                    if ($companyId) {
                        $hasAccessToCompany = $conn->table('sys_user_menu_company_access')
                            ->where('user_id', $userId)
                            ->where('menu_id', $menu->id)
                            ->where('company_id', $companyId)
                            ->exists();

                        if (!$hasAccessToCompany) {
                            return $this->deny($request, 'คุณไม่มีสิทธิ์เข้าถึงบริษัทที่เลือกอยู่สำหรับเมนูนี้');
                        }
                    }
                }
            }

            // 5. ตรวจสิทธิ์การเข้าถึง Company (sys_user_company_access)
            // ถ้าผู้ใช้ไม่มีสิทธิ์เข้าถึง company ที่เลือกอยู่เลย ให้ปฏิเสธ
            $currentCompanyKey = session('company.key');
            if ($currentCompanyKey) {
                $companyId = $conn->table('sys_companies')->where('key', $currentCompanyKey)->value('id');
                if ($companyId) {
                    $hasCompanyAccess = $conn->table('sys_user_company_access')
                        ->where('user_id', $userId)
                        ->where('company_id', $companyId)
                        ->exists();

                    // ถ้าไม่มี record ใน sys_user_company_access แปลว่าไม่จำกัด (ให้ผ่านได้)
                    // แต่ถ้ามี record อยู่แล้ว ต้องมีสิทธิ์เข้าถึง company นั้น
                    $anyCompanyAccess = $conn->table('sys_user_company_access')
                        ->where('user_id', $userId)
                        ->exists();

                    if ($anyCompanyAccess && !$hasCompanyAccess) {
                        return $this->deny($request, 'คุณไม่มีสิทธิ์เข้าถึงบริษัทนี้');
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error('MenuPermission middleware error', [
                'error' => $e->getMessage(),
                'menu_key' => $menuKey,
                'action' => $action,
            ]);
            return $this->deny($request, 'ไม่สามารถตรวจสอบสิทธิ์ได้');
        }

        return $next($request);
    }
}
