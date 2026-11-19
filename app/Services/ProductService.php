<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * Get paginated products with search, filter, and whitelisted sorting.
     */
    public function getPaginatedProducts(array $filters): array
    {
        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 10;
        $page = isset($filters['page']) ? (int) $filters['page'] : 1;

        $query = Product::with('category:id,slug,name');

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
     * Get single product detail by ID.
     */
    public function getProductById(int|string $id): ?Product
    {
        return Product::with('category:id,slug,name')->find($id);
    }

    /**
     * Get all categories.
     */
    public function getAllCategories(): Collection
    {
        return Category::orderBy('name', 'asc')->get();
    }
}
