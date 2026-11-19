<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $payload = [
            'name'     => 'Budi Santoso',
            'email'    => 'budi@example.com',
            'password' => 'secret12345',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registrasi berhasil',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'role'  => 'customer',
        ]);
    }

    public function test_register_with_duplicate_email_is_rejected(): void
    {
        User::create([
            'name'     => 'Existing User',
            'email'    => 'existing@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name'     => 'Duplicate User',
            'email'    => 'existing@example.com',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_is_properly_hashed_in_database(): void
    {
        $this->postJson('/api/auth/register', [
            'name'     => 'Hash Test User',
            'email'    => 'hashtest@example.com',
            'password' => 'plainpassword123',
        ]);

        $user = User::where('email', 'hashtest@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals('plainpassword123', $user->password);
        $this->assertTrue(Hash::check('plainpassword123', $user->password));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::create([
            'name'     => 'Login User',
            'email'    => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil',
                'data'    => [
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_with_invalid_password_returns_401(): void
    {
        User::create([
            'name'     => 'User Wrong Pass',
            'email'    => 'wrongpass@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'wrongpass@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email atau password salah.',
            ]);
    }

    public function test_get_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_authenticated_user_can_access_me_profile(): void
    {
        $user = User::create([
            'name'     => 'Profile User',
            'email'    => 'profile@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'    => $user->id,
                    'name'  => 'Profile User',
                    'email' => 'profile@example.com',
                ],
            ]);
    }
}
