<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Display a paginated listing of all products for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->productService->getAdminPaginatedProducts($request->all());

        return ApiResponse::success(
            data: $result['data'],
            message: 'Daftar produk admin berhasil diambil',
            meta: $result['meta']
        );
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());

        return ApiResponse::created($product, 'Produk berhasil dibuat.');
    }

    /**
     * Display the specified product detail for admin.
     */
    public function show(int|string $id): JsonResponse
    {
        $product = $this->productService->getAdminProductById($id);

        if (! $product) {
            return ApiResponse::notFound('Produk tidak ditemukan.');
        }

        return ApiResponse::success($product, 'Detail produk berhasil diambil.');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, int|string $id): JsonResponse
    {
        $product = $this->productService->getAdminProductById($id);

        if (! $product) {
            return ApiResponse::notFound('Produk tidak ditemukan.');
        }

        $updatedProduct = $this->productService->updateProduct($product, $request->validated());

        return ApiResponse::success($updatedProduct, 'Produk berhasil diperbarui.');
    }

    /**
     * Soft deactivation of product to preserve order history.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $product = $this->productService->getAdminProductById($id);

        if (! $product) {
            return ApiResponse::notFound('Produk tidak ditemukan.');
        }

        $this->productService->deactivateProduct($product);

        return ApiResponse::success(
            data: $product->fresh(),
            message: 'Produk berhasil dinonaktifkan.'
        );
    }
}
