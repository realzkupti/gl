<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                return back()->withErrors(['email' => 'บัญชียังไม่ได้รับการอนุมัติ'])->withInput();
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

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended('/');
    }
}
