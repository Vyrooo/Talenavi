<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductQueryRequest;
use App\Services\ProductService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a listing of products with pagination, search, filter, and sorting.
     */
    public function index(ProductQueryRequest $request): JsonResponse
    {
        $result = $this->productService->getPaginatedProducts($request->validated());

        return ApiResponse::success(
            data: $result['data'],
            message: 'Daftar produk berhasil diambil',
            meta: $result['meta']
        );
    }

    /**
     * Display the specified product detail.
     */
    public function show(int|string $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (! $product) {
            return ApiResponse::notFound('Produk tidak ditemukan');
        }

        return ApiResponse::success($product, 'Detail produk berhasil diambil');
    }
}
