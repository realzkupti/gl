<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserApprovalController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (!Auth::check() || (Auth::user()->email ?? '') !== 'admin@local') {
            abort(403, 'Forbidden');
        }
    }

    public function index()
    {
        $this->ensureAdmin();
        $users = User::query()->orderBy('id')->get(['id','name','email','is_active','created_at']);
        return view('tailadmin.pages.admin.user-approvals', compact('users'));
    }

    public function activate(Request $request, $id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);
        $user->is_active = true;
        $user->save();
        return back()->with('status', 'เปิดใช้งานผู้ใช้แล้ว');
    }

    public function deactivate(Request $request, $id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);
        $user->is_active = false;
        $user->save();
        return back()->with('status', 'ปิดการใช้งานผู้ใช้แล้ว');
    }
}

