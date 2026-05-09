<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertStatus(200)
            ->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_expired_login_token_redirects_back_to_login_with_a_fresh_session(): void
    {
        $response = $this->withMiddleware(ValidateCsrfToken::class)
            ->from('/login')
            ->post('/login', [
                '_token' => 'expired-token',
                'login_mode' => 'admin',
                'email' => 'admin@example.test',
                'password' => 'password',
            ]);

        $this->assertGuest();
        $response
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHas('status', 'Your session expired. Please try again.')
            ->assertSessionHasInput('login_mode', 'admin')
            ->assertSessionHasInput('email', 'admin@example.test');
    }

    public function test_expired_token_on_other_web_forms_redirects_back_with_a_fresh_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withMiddleware(ValidateCsrfToken::class)
            ->from('/profile')
            ->post('/logout', [
                '_token' => 'expired-token',
            ]);

        $this->assertAuthenticated();
        $response
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'Your session expired. Please try again.');
    }

    public function test_expired_token_on_json_requests_returns_a_clear_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withMiddleware(ValidateCsrfToken::class)
            ->postJson('/logout', [
                '_token' => 'expired-token',
            ]);

        $this->assertAuthenticated();
        $response
            ->assertStatus(419)
            ->assertJson([
                'message' => 'Your session expired. Please try again.',
            ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
