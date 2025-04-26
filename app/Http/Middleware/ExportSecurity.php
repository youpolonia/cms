<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ExportSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        // IP restrictions
        $allowedIps = config('analytics.export_allowed_ips', []);
        if (!empty($allowedIps)) {
            $clientIp = $request->ip();
            if (!in_array($clientIp, $allowedIps)) {
                Log::warning("Export access denied from IP: $clientIp");
                abort(403, 'Access denied');
            }
        }

        // Rate limiting (5 downloads per minute per IP)
        $key = 'exports:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Too many download attempts',
                'retry_after' => $retryAfter
            ], 429);
        }
        RateLimiter::hit($key);

        // Log access
        $response = $next($request);

        if ($response->isSuccessful()) {
            Log::info("Export downloaded", [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'export_id' => $request->route('export')->id,
                'user_id' => $request->user()?->id
            ]);
        }

        return $response;
    }
}