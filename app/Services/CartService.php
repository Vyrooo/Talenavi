<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartService
{
    /**
     * Get user cart items with calculated subtotals and overall total price.
     */
    public function getUserCart(User $user): array
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        $formattedItems = [];
        $totalCents = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;
            $priceFloat = (float) $product->price;
            $priceCents = (int) round($priceFloat * 100);
            $subtotalCents = $priceCents * $item->quantity;
            $totalCents += $subtotalCents;

            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'title' => $product->title,
                'image_url' => $product->image_url,
                'price' => number_format($priceFloat, 2, '.', ''),
                'quantity' => $item->quantity,
                'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                'is_active' => (bool) $product->is_active,
            ];
        }

        return [
            'items' => $formattedItems,
            'total_price' => number_format($totalCents / 100, 2, '.', ''),
        ];
    }

    /**
     * Add product to user cart or increment quantity if already exists.
     *
     * @throws InsufficientStockException
     */
    public function addToCart(User $user, int $productId, int $quantity): CartItem
    {
        $product = Product::findOrFail($productId);

        if (! $product->is_active) {
            throw new InsufficientStockException(
                message: "Produk '{$product->title}' saat ini tidak aktif atau tidak tersedia.",
                errors: [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'is_active' => false,
                ]
            );
        }

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        $newQuantity = $cartItem ? ($cartItem->quantity + $quantity) : $quantity;

        if ($newQuantity > $product->stock) {
            throw new InsufficientStockException(
                message: "Stok produk '{$product->title}' tidak mencukupi.",
                errors: [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'available_stock' => $product->stock,
                    'requested_quantity' => $newQuantity,
                ]
            );
        }

        if ($cartItem) {
            $cartItem->update(['quantity' => $newQuantity]);
            return $cartItem;
        }

        return CartItem::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Update quantity of specific cart item.
     *
     * @throws InsufficientStockException
     */
    public function updateCartItem(User $user, int $cartItemId, int $quantity): ?CartItem
    {
        $cartItem = CartItem::with('product')
            ->where('id', $cartItemId)
            ->where('user_id', $user->id)
            ->first();

        if (! $cartItem) {
            return null;
        }

        if (! $cartItem->product || ! $cartItem->product->is_active) {
            throw new InsufficientStockException(
                message: "Produk '{$cartItem->product?->title}' saat ini tidak aktif atau tidak tersedia.",
                errors: [
                    'product_id' => $cartItem->product_id,
                    'title' => $cartItem->product?->title,
                    'is_active' => false,
                ]
            );
        }

        if ($quantity > $cartItem->product->stock) {
            throw new InsufficientStockException(
                message: "Stok produk '{$cartItem->product->title}' tidak mencukupi.",
                errors: [
                    'product_id' => $cartItem->product_id,
                    'title' => $cartItem->product->title,
                    'available_stock' => $cartItem->product->stock,
                    'requested_quantity' => $quantity,
                ]
            );
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem;
    }

    /**
     * Remove item from cart.
     */
    public function removeCartItem(User $user, int $cartItemId): bool
    {
        $cartItem = CartItem::where('id', $cartItemId)
            ->where('user_id', $user->id)
            ->first();

        if (! $cartItem) {
            return false;
        }

        return (bool) $cartItem->delete();
    }
}
