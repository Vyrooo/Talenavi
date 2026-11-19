<?php

namespace App\Providers;

use App\Support\ApiResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Rate Limiter khusus endpoint login: 5 percobaan per menit per email + IP
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));
            $key = $email ? ($email . '|' . $request->ip()) : $request->ip();

            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                return ApiResponse::error('Too many login attempts. Please try again later.', null, 429);
            });
        });
    }
}
