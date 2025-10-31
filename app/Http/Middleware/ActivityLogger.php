<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            // Log only state-changing requests to reduce noise
            if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                return $response;
            }

            $user = $request->user();
            $action = $request->route()?->getName() ?: ($request->route()?->uri() ?? 'unknown');

            $payload = $request->except(['password', 'password_confirmation', '_token']);

            ActivityLog::create([
                'user_id' => $user?->id,
                'action' => (string) $action,
                'url' => (string) $request->fullUrl(),
                'method' => (string) $request->method(),
                'ip' => (string) $request->ip(),
                'user_agent' => (string) ($request->userAgent() ?? ''),
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            // Swallow logging errors to not affect user flow
        }

        return $response;
    }
}

