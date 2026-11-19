<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Checkout entire cart into a new order.
     */
    public function store(Request $request): JsonResponse
    {
        $order = $this->orderService->checkout($request->user());

        return ApiResponse::created($order, 'Checkout berhasil, order berhasil dibuat');
    }

    /**
     * Get order history of the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrders($request->user());

        return ApiResponse::success($orders, 'Riwayat pesanan berhasil diambil');
    }

    /**
     * Get details of a specific order belonging to the authenticated user.
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $order = $this->orderService->getUserOrderDetail($request->user(), $id);

        if (! $order) {
            return ApiResponse::notFound('Pesanan tidak ditemukan');
        }

        return ApiResponse::success($order, 'Detail pesanan berhasil diambil');
    }
}
