<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class TailAdminController extends Controller
{
    /**
     * Original HTML demo (deprecated - use Blade templates instead)
     */
    public function index(): Response
    {
        $path = public_path('tailadmin/index.html');
        if (!file_exists($path)) {
            abort(404, 'TailAdmin build not found at /public/tailadmin');
        }
        $html = file_get_contents($path);
        // Inject <base> so all relative links/assets resolve under /tailadmin/
        if (strpos($html, '<base ') === false) {
            $html = preg_replace('/<head(\s*)>/', '<head$1><base href="/tailadmin/">', $html, 1);
        }
        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Analytics Dashboard
     */
    public function analytics(): View
    {
        $page = 'analytics';
        $stats = [
            'visitors' => 24589,
            'pageViews' => 84523,
            'bounceRate' => 42.5,
            'avgDuration' => '3:45',
        ];

        return view('tailadmin.pages.analytics', compact('page', 'stats'));
    }

    /**
     * Alerts Demo
     */
    public function alerts(): View
    {
        $page = 'alerts';
        return view('tailadmin.pages.alerts', compact('page'));
    }

    /**
     * Buttons Demo
     */
    public function buttons(): View
    {
        $page = 'buttons';
        return view('tailadmin.pages.buttons', compact('page'));
    }

    /**
     * Cards Demo
     */
    public function cards(): View
    {
        $page = 'cards';
        return view('tailadmin.pages.cards', compact('page'));
    }

    /**
     * Tables Demo
     */
    public function tables(): View
    {
        $page = 'tables';
        $users = [
            ['id' => 1, 'name' => 'สมชาย ใจดี', 'email' => 'somchai@example.com', 'role' => 'Admin', 'status' => 'active'],
            ['id' => 2, 'name' => 'สมหญิง รักงาน', 'email' => 'somying@example.com', 'role' => 'User', 'status' => 'active'],
            ['id' => 3, 'name' => 'ประชา ขยัน', 'email' => 'pracha@example.com', 'role' => 'Manager', 'status' => 'active'],
            ['id' => 4, 'name' => 'วิไล สุขใจ', 'email' => 'wilai@example.com', 'role' => 'User', 'status' => 'inactive'],
        ];

        return view('tailadmin.pages.tables', compact('page', 'users'));
    }

    /**
     * Forms Demo
     */
    public function forms(): View
    {
        $page = 'forms';
        return view('tailadmin.pages.forms', compact('page'));
    }

    /**
     * Cheque Print Page
     */
    public function chequePrint(): View
    {
        $page = 'cheque-print';
        return view('tailadmin.pages.cheque.print', compact('page'));
    }

    /**
     * Cheque Designer Page
     */
    public function chequeDesigner(): View
    {
        $page = 'cheque-designer';
        return view('tailadmin.pages.cheque.designer', compact('page'));
    }

    /**
     * Cheque Reports Page
     */
    public function chequeReports(): View
    {
        $page = 'cheque-reports';
        return view('tailadmin.pages.cheque.reports', compact('page'));
    }

    /**
     * Cheque Branches Page
     */
    public function chequeBranches(): View
    {
        $page = 'cheque-branches';
        return view('tailadmin.pages.cheque.branches', compact('page'));
    }

    /**
     * Cheque Settings Page
     */
    public function chequeSettings(): View
    {
        $page = 'cheque-settings';
        return view('tailadmin.pages.cheque.settings', compact('page'));
    }
}

