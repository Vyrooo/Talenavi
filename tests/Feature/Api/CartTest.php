<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::create([
            'name'     => 'Customer One',
            'email'    => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->token1 = $this->user1->createToken('token1')->plainTextToken;

        $this->user2 = User::create([
            'name'     => 'Customer Two',
            'email'    => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->token2 = $this->user2->createToken('token2')->plainTextToken;

        $category = Category::create(['name' => 'Gadgets', 'slug' => 'gadgets']);

        $this->product = Product::create([
            'category_id' => $category->id,
            'title'       => 'Smart Fitness Watch',
            'description' => 'Heart rate monitor with GPS',
            'price'       => 250000.00,
            'stock'       => 5,
            'is_active'   => true,
        ]);

        $this->inactiveProduct = Product::create([
            'category_id' => $category->id,
            'title'       => 'Old Watch',
            'description' => 'Discontinued',
            'price'       => 150000.00,
            'stock'       => 10,
            'is_active'   => false,
        ]);
    }

    public function test_can_add_product_to_cart_successfully(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token1}")
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity'   => 2,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan ke keranjang',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->user1->id,
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);
    }

    public function test_adding_same_product_increments_existing_cart_quantity(): void
    {
        // Add 2 items
        $this->withHeader('Authorization', "Bearer {$this->token1}")
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity'   => 2,
            ]);

        // Add 2 more items
        $response = $this->withHeader('Authorization', "Bearer {$this->token1}")
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity'   => 2,
            ]);

        $response->assertStatus(201);

        // Cart items table should only have 1 row with quantity = 4
        $this->assertEquals(1, CartItem::where('user_id', $this->user1->id)->count());
        $this->assertEquals(4, CartItem::where('user_id', $this->user1->id)->first()->quantity);
    }

    public function test_adding_quantity_exceeding_stock_is_rejected_with_409(): void
    {
        // Product has stock = 5, request 6
        $response = $this->withHeader('Authorization', "Bearer {$this->token1}")
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity'   => 6,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_adding_inactive_product_to_cart_is_rejected(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token1}")
            ->postJson('/api/cart/items', [
                'product_id' => $this->inactiveProduct->id,
                'quantity'   => 1,
            ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_quantity_cannot_be_zero_or_negative(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token1}")
            ->postJson('/api/cart/items', [
                'product_id' => $this->product->id,
                'quantity'   => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_user_cannot_update_or_delete_other_users_cart_item(): void
    {
        // User 1 has a cart item
        $cartItem = CartItem::create([
            'user_id'    => $this->user1->id,
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        // User 2 tries to update User 1's cart item -> 404
        $updateResponse = $this->withHeader('Authorization', "Bearer {$this->token2}")
            ->patchJson("/api/cart/items/{$cartItem->id}", [
                'quantity' => 2,
            ]);

        $updateResponse->assertStatus(404);

        // User 2 tries to delete User 1's cart item -> 404
        $deleteResponse = $this->withHeader('Authorization', "Bearer {$this->token2}")
            ->deleteJson("/api/cart/items/{$cartItem->id}");

        $deleteResponse->assertStatus(404);

        // Verify the cart item was NOT deleted
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
        ]);
    }
}
