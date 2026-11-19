<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * Get paginated products for public catalog (only active products).
     */
    public function getPaginatedProducts(array $filters): array
    {
        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 10;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;

        // Katalog publik hanya menampilkan produk aktif
        $query = Product::active()->with('category:id,slug,name');

        // Search by title
        if (! empty($filters['q'])) {
            $searchTerm = trim($filters['q']);
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        // Filter by category slug
        if (! empty($filters['category'])) {
            $categorySlug = trim($filters['category']);
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Whitelisted sorting
        $sort = $filters['sort'] ?? null;
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('id', 'asc'),
        };

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * Get single active product detail by ID for public view.
     */
    public function getProductById(int|string $id): ?Product
    {
        return Product::active()->with('category:id,slug,name')->find($id);
    }

    /**
     * Get all categories.
     */
    public function getAllCategories(): Collection
    {
        return Category::orderBy('name', 'asc')->get();
    }

    /**
     * Get all products for Admin management (includes active and inactive products).
     */
    public function getAdminPaginatedProducts(array $filters): array
    {
        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 10;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;

        $query = Product::with('category:id,slug,name');

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['q'])) {
            $searchTerm = trim($filters['q']);
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        if (! empty($filters['category'])) {
            $categorySlug = trim($filters['category']);
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $query->orderBy('id', 'desc');

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * Get product detail for Admin by ID (regardless of is_active status).
     */
    public function getAdminProductById(int|string $id): ?Product
    {
        return Product::with('category:id,slug,name')->find($id);
    }

    /**
     * Create a new product by Admin.
     */
    public function createProduct(array $data): Product
    {
        if (! isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        $product = Product::create($data);

        return $product->load('category:id,slug,name');
    }

    /**
     * Update an existing product by Admin.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh()->load('category:id,slug,name');
    }

    /**
     * Soft deactivation of product by Admin to protect historical orders.
     */
    public function deactivateProduct(Product $product): bool
    {
        return $product->update(['is_active' => false]);
    }
}
