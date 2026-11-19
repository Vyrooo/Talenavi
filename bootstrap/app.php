<?php

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\LogApiRequest;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan middleware pencatatan log request API secara global
        $middleware->api(prepend: [
            LogApiRequest::class,
        ]);

        // Daftarkan alias middleware admin
        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::validationError($e->errors(), 'Validation failed');
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::unauthorized('Unauthenticated. Token tidak valid atau tidak disertakan.');
            }
        });

        $exceptions->render(function (InsufficientStockException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $e->render();
            }
        });

        $exceptions->render(function (EmptyCartException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $e->render();
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return $e->getResponse();
            }

            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();

                    if ($statusCode === 429) {
                        return ApiResponse::error('Too many login attempts. Please try again later.', null, 429);
                    }

                    if ($statusCode === 404) {
                        return ApiResponse::notFound('Data atau endpoint tidak ditemukan.');
                    }

                    if ($statusCode === 403) {
                        return ApiResponse::forbidden('Akses ditolak.');
                    }

                    if ($statusCode === 401) {
                        return ApiResponse::unauthorized('Unauthenticated. Token tidak valid atau tidak disertakan.');
                    }

                    return ApiResponse::error($e->getMessage() ?: 'Error', null, $statusCode);
                }

                $requestId = $request->header('X-Request-ID') ?: $request->attributes->get('request_id');

                try {
                    Log::channel('daily')->error('API Internal Error', [
                        'request_id' => $requestId,
                        'message'    => $e->getMessage(),
                        'file'       => $e->getFile(),
                        'line'       => $e->getLine(),
                    ]);
                } catch (Throwable $logError) {
                    // Abaikan kesalahan penulisan log
                }

                if (config('app.debug')) {
                    return ApiResponse::error($e->getMessage(), null, 500);
                }
                return ApiResponse::error('Terjadi kesalahan internal server.', null, 500);
            }
        });
    })->create();
