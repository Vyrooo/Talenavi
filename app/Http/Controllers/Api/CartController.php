<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Services\CartService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Get user's cart contents.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getUserCart($request->user());

        return ApiResponse::success($cart, 'Isi keranjang belanja berhasil diambil');
    }

    /**
     * Add product to cart.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $item = $this->cartService->addToCart(
            user: $request->user(),
            productId: (int) $request->input('product_id'),
            quantity: (int) $request->input('quantity')
        );

        return ApiResponse::created($item, 'Produk berhasil ditambahkan ke keranjang');
    }

    /**
     * Update quantity of an item in cart.
     */
    public function update(UpdateCartItemRequest $request, int|string $id): JsonResponse
    {
        $updatedItem = $this->cartService->updateCartItem(
            user: $request->user(),
            cartItemId: (int) $id,
            quantity: (int) $request->input('quantity')
        );

        if (! $updatedItem) {
            return ApiResponse::notFound('Item keranjang tidak ditemukan');
        }

        return ApiResponse::success($updatedItem, 'Jumlah item keranjang berhasil diperbarui');
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $deleted = $this->cartService->removeCartItem(
            user: $request->user(),
            cartItemId: (int) $id
        );

        if (! $deleted) {
            return ApiResponse::notFound('Item keranjang tidak ditemukan');
        }

        return ApiResponse::success(null, 'Item keranjang berhasil dihapus');
    }
}
