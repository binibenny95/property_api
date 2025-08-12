<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    // Check while registering a new user its return token and user data.
    public function test_register_creates_user_and_returns_token(): void
    {
        $res = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test1@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => false,
        ]);

        $res->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id','name','email','is_admin'],
                    'token',
                    'token_type',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test1@example.com']);
    }
    // Login with the registering credentials.
    public function test_login_with_valid_credentials_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $res = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $res->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => ['id','name','email','is_admin'],
                    'token',
                    'token_type',
                ],
            ]);
    }

    // Login with invalid credentials.
    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'wrong@example.com',
            'password' => bcrypt('password123'),
        ]);

        $res = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'incorrect',
        ]);

        $res->assertStatus(401)
             ->assertJsonPath('status', 'error')
             ->assertJsonPath('message', 'Authentication failed')
             ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');

    }

}
