<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'     => 'Regular Customer',
            'email'    => 'customer@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'customer',
        ]);
        $this->customerToken = $this->customer->createToken('cust_token')->plainTextToken;

        $this->admin = User::create([
            'name'     => 'Store Administrator',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);
        $this->adminToken = $this->admin->createToken('admin_token')->plainTextToken;

        $this->category = Category::create(['name' => 'Hardware', 'slug' => 'hardware']);
    }

    public function test_unauthenticated_request_to_admin_endpoint_returns_401(): void
    {
        $response = $this->getJson('/api/admin/products');

        $response->assertStatus(401);
    }

    public function test_customer_request_to_admin_endpoint_returns_403_forbidden(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->customerToken}")
            ->getJson('/api/admin/products');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_can_list_all_products(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'title'       => 'Test Prod Active',
            'description' => 'Active desc',
            'price'       => 10000.00,
            'stock'       => 10,
            'is_active'   => true,
        ]);

        Product::create([
            'category_id' => $this->category->id,
            'title'       => 'Test Prod Inactive',
            'description' => 'Inactive desc',
            'price'       => 20000.00,
            'stock'       => 0,
            'is_active'   => false,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/admin/products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_create_new_product(): void
    {
        $payload = [
            'category_id' => $this->category->id,
            'title'       => 'Brand New Laptop Stand',
            'description' => 'Aluminum adjustable laptop stand',
            'price'       => 250000.00,
            'stock'       => 30,
            'image_url'   => 'https://example.com/stand.jpg',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/admin/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Produk berhasil dibuat.',
                'data'    => [
                    'title'     => 'Brand New Laptop Stand',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'title'     => 'Brand New Laptop Stand',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'title'       => 'Original Title',
            'description' => 'Original Desc',
            'price'       => 100000.00,
            'stock'       => 10,
            'is_active'   => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/admin/products/{$product->id}", [
                'category_id' => $this->category->id,
                'title'       => 'Updated Title',
                'description' => 'Updated Desc',
                'price'       => 120000.00,
                'stock'       => 15,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'title' => 'Updated Title',
                    'price' => '120000.00',
                ],
            ]);

        $this->assertEquals('Updated Title', $product->fresh()->title);
    }

    public function test_admin_can_deactivate_product_via_delete(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'title'       => 'Product To Deactivate',
            'description' => 'Desc',
            'price'       => 50000.00,
            'stock'       => 5,
            'is_active'   => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Produk berhasil dinonaktifkan.',
            ]);

        // Product still exists in DB, but is_active = false (soft deactivation)
        $this->assertDatabaseHas('products', [
            'id'        => $product->id,
            'is_active' => false,
        ]);
    }
}
