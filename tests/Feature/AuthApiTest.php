<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_succeeds_with_valid_credentials_and_client_id()
    {
        $user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => Hash::make('secret123'),
            'client_id' => 42,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'user' => ['id', 'name', 'email', 'client_id'],
                 ])
                 ->assertJsonFragment(['client_id' => 42]);
    }

    public function test_login_fails_with_invalid_password()
    {
        User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => Hash::make('secret123'),
            'client_id' => 1,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid credentials.']);
    }

    public function test_login_fails_for_user_without_client_id()
    {
        User::factory()->create([
            'email'     => 'noclient@example.com',
            'password'  => Hash::make('secret123'),
            'client_id' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'noclient@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'User is not associated with any client.']);
    }

    public function test_login_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_for_nonexistent_user()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'any',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid credentials.']);
    }
}
