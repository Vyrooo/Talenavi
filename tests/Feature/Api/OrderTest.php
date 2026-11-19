<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name'     => 'Shopper User',
            'email'    => 'shopper@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->token = $this->user->createToken('shopper_token')->plainTextToken;

        $this->otherUser = User::create([
            'name'     => 'Other User',
            'email'    => 'other@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->otherToken = $this->otherUser->createToken('other_token')->plainTextToken;

        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);

        $this->prodA = Product::create([
            'category_id' => $category->id,
            'title'       => 'Product A - Gaming Mouse',
            'description' => 'Fast sensor',
            'price'       => 150000.00,
            'stock'       => 10,
            'is_active'   => true,
        ]);

        $this->prodB = Product::create([
            'category_id' => $category->id,
            'title'       => 'Product B - Mousepad RGB',
            'description' => 'Large size',
            'price'       => 50000.00,
            'stock'       => 8,
            'is_active'   => true,
        ]);
    }

    public function test_successful_checkout_creates_order_and_items_and_clears_cart(): void
    {
        // Add 2 of ProdA and 3 of ProdB into cart
        CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->prodA->id,
            'quantity'   => 2,
        ]);

        CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->prodB->id,
            'quantity'   => 3,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/orders');

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Checkout berhasil, order berhasil dibuat',
            ]);

        // Expected total: (150,000 * 2) + (50,000 * 3) = 300,000 + 150,000 = 450,000
        $this->assertEquals('450000.00', $response->json('data.total_price'));

        // Verify order created in DB
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('450000.00', $order->total_price);
        $this->assertEquals('pending', $order->status);

        // Verify order items saved
        $this->assertEquals(2, $order->orderItems()->count());

        // Verify stock deducted
        $this->assertEquals(8, $this->prodA->fresh()->stock); // 10 - 2
        $this->assertEquals(5, $this->prodB->fresh()->stock); // 8 - 3

        // Verify cart emptied
        $this->assertEquals(0, CartItem::where('user_id', $this->user->id)->count());
    }

    public function test_checkout_saves_price_as_historical_snapshot(): void
    {
        CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->prodA->id,
            'quantity'   => 1,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/orders');

        $response->assertStatus(201);
        $orderId = $response->json('data.id');

        // Now merchant increases the catalog product price to 250,000
        $this->prodA->update(['price' => 250000.00]);

        // View order details
        $detailRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/orders/{$orderId}");

        $detailRes->assertStatus(200);

        // Snapshot price should strictly remain 150,000.00!
        $orderItemPrice = $detailRes->json('data.order_items.0.price');
        $this->assertEquals('150000.00', $orderItemPrice);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/orders');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals(0, Order::count());
    }

    public function test_checkout_fails_and_rolls_back_when_stock_is_insufficient(): void
    {
        // Set ProdA stock to 1, but cart has 3
        $this->prodA->update(['stock' => 1]);

        CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->prodA->id,
            'quantity'   => 3,
        ]);

        CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $this->prodB->id,
            'quantity'   => 2,
        ]);

        $initialStockB = $this->prodB->stock;

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/orders');

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);

        // 1. No order created
        $this->assertEquals(0, Order::count());

        // 2. No order items created
        $this->assertEquals(0, OrderItem::count());

        // 3. Stock of other product did not change
        $this->assertEquals($initialStockB, $this->prodB->fresh()->stock);

        // 4. Cart was NOT cleared
        $this->assertEquals(2, CartItem::where('user_id', $this->user->id)->count());
    }

    public function test_user_cannot_access_orders_belonging_to_other_users(): void
    {
        // Create an order for user 1
        $order = Order::create([
            'user_id'     => $this->user->id,
            'total_price' => 150000.00,
            'status'      => 'pending',
        ]);

        // Other user attempts to view this order -> 404
        $response = $this->withHeader('Authorization', "Bearer {$this->otherToken}")
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404);
    }
}
