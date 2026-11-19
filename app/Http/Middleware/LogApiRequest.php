<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?: Str::uuid()->toString();
        $request->attributes->set('request_id', $requestId);

        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // Jangan pernah biarkan kegagalan logging menggagalkan HTTP request
        try {
            $userId = $request->user()?->id;

            Log::channel('daily')->info('API Request Logged', [
                'request_id'  => $requestId,
                'method'      => $request->method(),
                'path'        => $request->path(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'user_id'     => $userId,
                'ip_address'  => $request->ip(),
                'timestamp'   => now()->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            // Abaikan kesalahan log agar flow utama aplikasi tetap berjalan
        }

        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
