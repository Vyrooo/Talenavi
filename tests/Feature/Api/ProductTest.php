<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elecCat = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $this->clothCat = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);

        $this->prod1 = Product::create([
            'category_id' => $this->elecCat->id,
            'title'       => 'Wireless Gaming Headset',
            'description' => 'Noise cancellation Bluetooth gaming headset',
            'price'       => 500000.00,
            'stock'       => 15,
            'image_url'   => 'https://example.com/headset.jpg',
            'is_active'   => true,
        ]);

        $this->prod2 = Product::create([
            'category_id' => $this->elecCat->id,
            'title'       => 'Mechanical Keyboard RGB',
            'description' => 'Blue clicky switch keyboard',
            'price'       => 800000.00,
            'stock'       => 20,
            'image_url'   => 'https://example.com/keyboard.jpg',
            'is_active'   => true,
        ]);

        $this->prod3 = Product::create([
            'category_id' => $this->clothCat->id,
            'title'       => 'Denim Jacket Classic',
            'description' => 'Vintage wash denim jacket',
            'price'       => 350000.00,
            'stock'       => 10,
            'image_url'   => 'https://example.com/jacket.jpg',
            'is_active'   => true,
        ]);

        $this->inactiveProd = Product::create([
            'category_id' => $this->elecCat->id,
            'title'       => 'Discontinued Smartphone Model X',
            'description' => 'Outdated model',
            'price'       => 1000000.00,
            'stock'       => 5,
            'image_url'   => 'https://example.com/phone.jpg',
            'is_active'   => false, // Inactive
        ]);
    }

    public function test_can_list_active_products_with_pagination(): void
    {
        $response = $this->getJson('/api/products?page=1&limit=10');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'title', 'price', 'stock', 'is_active', 'category'],
                ],
                'meta' => ['page', 'limit', 'total', 'total_pages'],
            ]);

        // Inactive product should NOT be in public listing
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertContains('Wireless Gaming Headset', $titles);
        $this->assertNotContains('Discontinued Smartphone Model X', $titles);
    }

    public function test_can_search_products_by_title(): void
    {
        $response = $this->getJson('/api/products?q=Keyboard');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Mechanical Keyboard RGB', $data[0]['title']);
    }

    public function test_can_filter_products_by_category_slug(): void
    {
        $response = $this->getJson('/api/products?category=fashion');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Denim Jacket Classic', $data[0]['title']);
    }

    public function test_can_sort_products_by_price_asc_and_desc(): void
    {
        // Sort asc
        $resAsc = $this->getJson('/api/products?sort=price_asc');
        $resAsc->assertStatus(200);
        $dataAsc = $resAsc->json('data');
        $this->assertEquals('Denim Jacket Classic', $dataAsc[0]['title']); // 350,000

        // Sort desc
        $resDesc = $this->getJson('/api/products?sort=price_desc');
        $resDesc->assertStatus(200);
        $dataDesc = $resDesc->json('data');
        $this->assertEquals('Mechanical Keyboard RGB', $dataDesc[0]['title']); // 800,000
    }

    public function test_can_get_single_active_product_detail(): void
    {
        $response = $this->getJson("/api/products/{$this->prod1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'    => $this->prod1->id,
                    'title' => 'Wireless Gaming Headset',
                ],
            ]);
    }

    public function test_get_non_existent_product_returns_404(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_get_inactive_product_in_public_returns_404(): void
    {
        $response = $this->getJson("/api/products/{$this->inactiveProd->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_list_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(2, $response->json('data'));
    }
}
