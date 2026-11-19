<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends Controller
{
    /**
     * Return public health check status for load balancers and deployment monitoring.
     */
    public function __invoke(): JsonResponse
    {
        $databaseStatus = 'ok';

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $databaseStatus = 'error';
        }

        $isHealthy = ($databaseStatus === 'ok');

        $statusData = [
            'status'    => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'services'  => [
                'database' => $databaseStatus,
            ],
        ];

        return ApiResponse::success(
            data: $statusData,
            message: $isHealthy ? 'Aplikasi berjalan normal.' : 'Aplikasi mengalami gangguan layanan.',
            meta: null,
            statusCode: $isHealthy ? 200 : 503
        );
    }
}
