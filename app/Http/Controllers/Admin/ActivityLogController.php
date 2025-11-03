<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Ensure user is authenticated
     */
    protected function ensureAuth()
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Display the activity logs page
     */
    public function index(Request $request)
    {
        $this->ensureAuth();

        // Get all users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('tailadmin.pages.admin.activity-logs', compact('users'));
    }

    /**
     * Get paginated activity logs data (AJAX endpoint)
     */
    public function data(Request $request)
    {
        $this->ensureAuth();

        $query = ActivityLog::with('user:id,name,email');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by HTTP method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search in URL, IP, or action
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('url', 'ilike', "%{$search}%")
                  ->orWhere('ip', 'ilike', "%{$search}%")
                  ->orWhere('action', 'ilike', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 50);
        $logs = $query->orderByDesc('id')->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Get single activity log detail (for modal)
     */
    public function show($id)
    {
        $this->ensureAuth();

        $log = ActivityLog::with('user:id,name,email')->findOrFail($id);

        return response()->json($log);
    }
}
