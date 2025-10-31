<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
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
        $roles = Role::orderBy('name')->get();
        return view('admin.roles', compact('roles'));
    }
}

