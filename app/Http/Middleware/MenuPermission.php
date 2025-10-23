<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuPermission
{
    /**
     * Check menu permission via role -> role_menu_permissions -> menus
     * Usage: ->middleware('menu:cheque,view') or 'menu:cheque,create'
     */
    public function handle(Request $request, Closure $next, string $menuKey, string $action = 'view')
    {
        // Admin override
        if (Auth::check() && (Auth::user()->email ?? '') === 'admin@local') {
            return $next($request);
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        try {
            $conn = DB::connection('pgsql');
            $roles = $conn->table('user_roles')->where('user_id', $userId)->pluck('role_id');
            if ($roles->isEmpty()) {
                abort(403, 'Forbidden');
            }

            $allowed = $conn->table('role_menu_permissions as p')
                ->join('menus as m', 'm.id', '=', 'p.menu_id')
                ->whereIn('p.role_id', $roles->all())
                ->where('m.key', $menuKey)
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

            if (!$allowed) {
                abort(403, 'Forbidden');
            }
        } catch (\Throwable $e) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}

