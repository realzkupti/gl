<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class TailAdminController extends Controller
{
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
}

