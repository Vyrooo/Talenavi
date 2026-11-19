<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::created($result, 'Registrasi berhasil');
    }

    /**
     * Login user with credentials and return bearer token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (! $result) {
            return ApiResponse::unauthorized('Email atau password salah.');
        }

        return ApiResponse::success($result, 'Login berhasil');
    }

    /**
     * Get profile of currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($request->user(), 'Data user berhasil diambil');
    }
}
