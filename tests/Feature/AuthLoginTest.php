<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_succeeds_with_valid_credentials(): void
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
                     'success',
                     'token_type',
                     'access_token',
                     'expires_at',
                     'user' => ['id', 'client_id', 'name', 'email'],
                 ])
                 ->assertJsonFragment([
                     'success'    => true,
                     'token_type' => 'bearer',
                 ]);

        $this->assertDatabaseHas('client_device_tokens', [
            'user_id'   => $user->id,
            'client_id' => 42,
            'status'    => 'Active',
        ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('correct-password'),
            'client_id' => 1,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'any-password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_returns_422_when_user_has_no_client_id(): void
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
                 ->assertJson([
                     'success' => false,
                     'message' => 'User account is not associated with a client. Please contact support.',
                 ]);

        $this->assertDatabaseCount('client_device_tokens', 0);
    }
}
