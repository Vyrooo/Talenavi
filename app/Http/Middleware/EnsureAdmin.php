<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::unauthorized('Unauthenticated. Token tidak valid atau tidak disertakan.');
        }

        if (! $user->isAdmin()) {
            return ApiResponse::forbidden('Akses ditolak. Endpoint ini hanya dapat diakses oleh Administrator.');
        }

        return $next($request);
    }
}
