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
     * ลำดับความสำคัญ: admin@local > user override > role permissions > ปฏิเสธ
     * การจำกัดสิทธิ์บริษัท: ถ้ามีการกำหนดเมนูนั้นไว้ในตาราง user_menu_company_access
     * จะตรวจว่าผู้ใช้เข้าถึงบริษัทที่เลือกอยู่ได้หรือไม่ (ถ้าไม่พบรายการ แปลว่าไม่จำกัด)
     *
     * ใช้กับ route: ->middleware('menu:cheque,view')
     */
    public function handle(Request $request, Closure $next, string $menuKey, string $action = 'view')
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Admin override
        if ((Auth::user()->email ?? '') === 'admin@local') {
            return $next($request);
        }

        $userId = Auth::id();

        try {
            $conn = DB::connection('pgsql');

            // หาเมนูเป้าหมาย
            $menu = $conn->table('menus')->where('key', $menuKey)->first(['id']);
            if (!$menu) {
                return $this->deny($request, 'ไม่พบเมนู หรือเมนูนี้ไม่ได้เปิดใช้งาน');
            }

            // 1) ตรวจ user override ก่อน (ถ้ามี)
            $userPerm = $conn->table('user_menu_permissions')
                ->where('user_id', $userId)
                ->where('menu_id', $menu->id)
                ->first();

            // รวมสิทธิ์แบบ OR: user override || role permissions
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

            // 2) ตรวจตามบทบาท
            $allowedRole = false;
            {
                $roles = $conn->table('user_roles')->where('user_id', $userId)->pluck('role_id');
                // ไม่มีบทบาท = ไม่มีสิทธิ์จากบทบาท แต่ยังคงให้ใช้ผล user override ได้

                $allowedRole = $conn->table('role_menu_permissions as p')
                    ->whereIn('p.role_id', $roles->all())
                    ->where('p.menu_id', $menu->id)
                    ->select('p.can_view','p.can_create','p.can_update','p.can_delete','p.can_export','p.can_approve')
                    ->get()
                    ->some(function ($row) use ($action) {
                        return match ($action) {
                            'view' => (bool)($row->can_view ?? false),
                            'create' => (bool)($row->can_create ?? false),
                            'update' => (bool)($row->can_update ?? false),
                            'delete' => (bool)($row->can_delete ?? false),
                            'export' => (bool)($row->can_export ?? false),
                            'approve' => (bool)($row->can_approve ?? false),
                            default => false,
                        };
                    });
            }

            $allowed = $allowedUser || $allowedRole;
            if (!$allowed) {
                return $this->deny($request, 'คุณไม่มีสิทธิ์สำหรับเมนูนี้');
            }

            // 3) ตรวจสิทธิ์บริษัท (ถ้ามีการบันทึกจำกัดไว้สำหรับเมนูนี้)
            // หลักการ: ถ้าตาราง user_menu_company_access มีแถวของผู้ใช้+เมนูนี้อยู่บ้าง
            // จะถือว่าเมนูนี้ถูกจำกัดบริษัท และต้องมีแถวที่ตรงกับบริษัทที่เลือกอยู่เท่านั้นถึงจะผ่าน
            $hasCompanyConstraint = $conn->table('user_menu_company_access')
                ->where('user_id', $userId)
                ->where('menu_id', $menu->id)
                ->exists();

            if ($hasCompanyConstraint) {
                $currentCompanyKey = session('company.key');
                if ($currentCompanyKey) {
                    $companyId = $conn->table('companies')->where('key', $currentCompanyKey)->value('id');
                    if ($companyId) {
                        $hasAccessToCompany = $conn->table('user_menu_company_access')
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
        } catch (\Throwable $e) {
            return $this->deny($request, 'ไม่สามารถตรวจสอบสิทธิ์ได้');
        }

        return $next($request);
    }
}
