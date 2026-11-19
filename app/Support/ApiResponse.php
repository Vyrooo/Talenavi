<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a standardized success JSON response.
     */
    public static function success(mixed $data = null, string $message = 'OK', mixed $meta = null, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $statusCode);
    }

    /**
     * Return a standardized 201 Created JSON response.
     */
    public static function created(mixed $data = null, string $message = 'Data berhasil dibuat'): JsonResponse
    {
        return self::success($data, $message, null, 201);
    }

    /**
     * Return a standardized error JSON response.
     */
    public static function error(string $message, mixed $errors = null, int $statusCode = 400): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * 401 Unauthorized
     */
    public static function unauthorized(string $message = 'Unauthenticated'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * 403 Forbidden
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * 404 Not Found
     */
    public static function notFound(string $message = 'Resource tidak ditemukan'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * 409 Conflict
     */
    public static function conflict(string $message, mixed $errors = null): JsonResponse
    {
        return self::error($message, $errors, 409);
    }

    /**
     * 422 Unprocessable Entity
     */
    public static function validationError(mixed $errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }
}
