<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rate_limiting_triggers_after_5_attempts(): void
    {
        RateLimiter::clear('spammer@example.com|127.0.0.1');

        User::create([
            'name'     => 'Target User',
            'email'    => 'spammer@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        // Attempt 5 bad logins
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email'    => 'spammer@example.com',
                'password' => 'wrongpassword',
            ]);
            $response->assertStatus(401);
        }

        // 6th attempt must be blocked by rate limiter with 429 Too Many Requests
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'spammer@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
            ]);
    }

    public function test_api_responses_include_x_request_id_header(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('X-Request-ID'));
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }

    public function test_health_check_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'status'   => 'healthy',
                    'services' => [
                        'database' => 'ok',
                    ],
                ],
            ]);
    }
}
