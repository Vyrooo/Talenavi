<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Get all product categories.
     */
    public function index(): JsonResponse
    {
        $categories = $this->productService->getAllCategories();

        return ApiResponse::success($categories, 'Daftar kategori berhasil diambil');
    }
}
