<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\ApiToken;
use Illuminate\Support\Facades\Hash;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Login endpoint
    // -----------------------------------------------------------------------

    public function test_login_returns_bearer_token_and_user_data()
    {
        $user = User::factory()->create([
            'email'    => 'alice@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'alice@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     'token_type',
                     'access_token',
                     'expires_in',
                     'user' => ['id', 'name', 'email'],
                 ])
                 ->assertJsonFragment([
                     'token_type' => 'Bearer',
                     'user'       => [
                         'id'    => $user->id,
                         'name'  => $user->name,
                         'email' => $user->email,
                     ],
                 ]);

        $this->assertDatabaseHas('api_tokens', ['user_id' => $user->id]);
    }

    public function test_login_fails_with_wrong_password()
    {
        User::factory()->create([
            'email'    => 'bob@example.com',
            'password' => Hash::make('correctPass1'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'bob@example.com',
            'password' => 'wrongPass',
        ]);

        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Invalid credentials.']);
    }

    public function test_login_fails_with_unknown_email()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'nobody@example.com',
            'password' => 'any_password',
        ]);

        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Invalid credentials.']);
    }

    public function test_login_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_validates_email_format()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'not-an-email',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rotates_token_on_subsequent_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('pass1234'),
        ]);

        $first  = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'pass1234',
        ])->json('access_token');

        $second = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'pass1234',
        ])->json('access_token');

        $this->assertNotEquals($first, $second);
        $this->assertCount(1, ApiToken::where('user_id', $user->id)->get());
    }

    // -----------------------------------------------------------------------
    // Bearer token middleware
    // -----------------------------------------------------------------------

    public function test_protected_route_requires_bearer_token()
    {
        // Register a minimal protected route for testing only.
        \Illuminate\Support\Facades\Route::middleware(['api', 'bearer.token'])
            ->prefix('api/v1')
            ->get('/test-protected', fn () => response()->json(['ok' => true]));

        $response = $this->getJson('/api/v1/test-protected');

        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Unauthenticated. Bearer token required.']);
    }

    public function test_protected_route_accepts_valid_bearer_token()
    {
        \Illuminate\Support\Facades\Route::middleware(['api', 'bearer.token'])
            ->prefix('api/v1')
            ->get('/test-protected', fn () => response()->json(['ok' => true]));

        $user = User::factory()->create([
            'password' => Hash::make('pass1234'),
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'pass1234',
        ])->json('access_token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/v1/test-protected');

        $response->assertOk()
                 ->assertJson(['ok' => true]);
    }

    public function test_protected_route_rejects_expired_token()
    {
        \Illuminate\Support\Facades\Route::middleware(['api', 'bearer.token'])
            ->prefix('api/v1')
            ->get('/test-protected', fn () => response()->json(['ok' => true]));

        $user = User::factory()->create([
            'password' => Hash::make('pass1234'),
        ]);

        $plainToken = bin2hex(random_bytes(32));

        ApiToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plainToken),
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$plainToken}")
                         ->getJson('/api/v1/test-protected');

        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Token has expired.']);
    }

    public function test_protected_route_rejects_invalid_token()
    {
        \Illuminate\Support\Facades\Route::middleware(['api', 'bearer.token'])
            ->prefix('api/v1')
            ->get('/test-protected', fn () => response()->json(['ok' => true]));

        $response = $this->withHeader('Authorization', 'Bearer completelyfaketoken')
                         ->getJson('/api/v1/test-protected');

        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Invalid token.']);
    }
}
