<?php

namespace App\Services;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Checkout user's entire cart into a single order with strict row locking and transaction safety.
     *
     * @throws EmptyCartException
     * @throws InsufficientStockException
     */
    public function checkout(User $user): Order
    {
        return DB::transaction(function () use ($user) {
            // 1. Get all cart items for current user
            $cartItems = CartItem::where('user_id', $user->id)->get();

            if ($cartItems->isEmpty()) {
                throw new EmptyCartException('Keranjang belanja kosong. Tidak dapat melakukan checkout.');
            }

            // 2. Lock products in consistent ascending ID order to prevent race conditions and deadlocks
            $productIds = $cartItems->pluck('product_id')->unique()->sort()->values()->all();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 3. Verify stock availability and active status for each cart item
            $insufficientItems = [];
            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);
                if (! $product || ! $product->is_active) {
                    $insufficientItems[] = [
                        'product_id' => $item->product_id,
                        'title' => $product?->title ?? 'Produk tidak ditemukan',
                        'available_stock' => $product?->stock ?? 0,
                        'requested_quantity' => $item->quantity,
                        'reason' => 'Produk dinonaktifkan atau tidak tersedia.',
                    ];
                } elseif ($product->stock < $item->quantity) {
                    $insufficientItems[] = [
                        'product_id' => $item->product_id,
                        'title' => $product->title,
                        'available_stock' => $product->stock,
                        'requested_quantity' => $item->quantity,
                        'reason' => 'Stok produk tidak mencukupi.',
                    ];
                }
            }

            if (! empty($insufficientItems)) {
                throw new InsufficientStockException(
                    message: 'Checkout gagal karena terdapat produk yang tidak tersedia atau stok tidak mencukupi.',
                    errors: $insufficientItems
                );
            }

            // 4. Calculate total price using integer cents to prevent floating point inaccuracies
            $totalCents = 0;
            $itemsToCreate = [];

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);
                $priceFloat = (float) $product->price;
                $priceCents = (int) round($priceFloat * 100);
                $subtotalCents = $priceCents * $item->quantity;
                $totalCents += $subtotalCents;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => number_format($priceFloat, 2, '.', ''),
                ];
            }

            $totalPrice = number_format($totalCents / 100, 2, '.', '');

            // 5. Create Order record
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // 6. Create order items and decrement product stocks
            foreach ($itemsToCreate as $itemData) {
                $order->orderItems()->create($itemData);

                $product = $products->get($itemData['product_id']);
                $product->decrement('stock', $itemData['quantity']);
            }

            // 7. Empty user cart
            CartItem::where('user_id', $user->id)->delete();

            // Return order with eager loaded relations
            return $order->load(['orderItems.product:id,title,image_url,is_active']);
        });
    }

    /**
     * Get order history for the authenticated user.
     */
    public function getUserOrders(User $user): Collection
    {
        return Order::with(['orderItems.product:id,title,image_url,is_active'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get order detail by ID for the authenticated user.
     */
    public function getUserOrderDetail(User $user, int|string $orderId): ?Order
    {
        return Order::with(['orderItems.product:id,title,image_url,is_active'])
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();
    }
}
