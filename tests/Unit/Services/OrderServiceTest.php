<?php

namespace Tests\Unit\Services;

use App\Exceptions\EmptyCartException;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_throws_empty_cart_exception_when_cart_has_no_items(): void
    {
        $user = User::create([
            'name'     => 'Empty Cart User',
            'email'    => 'emptycart@example.com',
            'password' => Hash::make('password123'),
        ]);

        $orderService = app(OrderService::class);

        $this->expectException(EmptyCartException::class);
        $this->expectExceptionMessage('Keranjang belanja kosong. Tidak dapat melakukan checkout.');

        $orderService->checkout($user);
    }
}
