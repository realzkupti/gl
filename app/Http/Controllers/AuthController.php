<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->intended('/');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $remember = (bool) $request->boolean('remember');
        if (Auth::attempt($credentials, $remember)) {
            if (! (Auth::user()->is_active ?? true)) {
                Auth::logout();
                return back()->with('status', 'บัญชีของคุณรอการอนุมัติจากผู้ดูแลระบบ')
                    ->withInput();
            }
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
        return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect()->intended('/');
        return view('auth.register');
    }

    public function showForgot()
    {
        if (Auth::check()) return redirect()->intended('/');
        return view('auth.forgot');
    }

    public function forgot(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        // ที่โปรเจกต์นี้ยังไม่ตั้งค่าระบบส่งอีเมล รีเทิร์นสถานะแจ้งเตือนแทน
        // สามารถเปลี่ยนมาใช้ Password::sendResetLink() ของ Laravel ได้ภายหลัง
        return back()->with('status', 'เราได้รับคำขอรีเซ็ตรหัสผ่านแล้ว โปรดติดต่อผู้ดูแลเพื่อดำเนินการ');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            // Pin to pgsql connection explicitly to avoid company_default
            'email' => 'required|email|max:255|unique:pgsql.users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => false,
        ]);

        // Do not auto-login inactive users; ask to wait for approval.
        return redirect()->route('login')
            ->with('status', 'ลงทะเบียนสำเร็จ กรุณารอผู้ดูแลระบบอนุมัติการใช้งาน');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านเดิมไม่ถูกต้อง']);
        }

        $user->password = $request->input('password');
        $user->save();

        return back()->with('status', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }
}
