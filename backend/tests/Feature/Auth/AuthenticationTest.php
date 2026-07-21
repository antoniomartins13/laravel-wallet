<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertNoContent();
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

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertNoContent();
    }

    /**
     * Complements LoginRequest's own per-email throttle: this one is scoped
     * by IP alone at the route level, so it also catches an attacker
     * spraying attempts across many different email addresses.
     */
    public function test_login_route_is_rate_limited_by_ip(): void
    {
        RateLimiter::for('login', fn () => Limit::perMinute(2));

        $user = User::factory()->create();

        // Wrong credentials on purpose: a successful login would authenticate
        // the guard for the rest of the test (Laravel's test client reuses
        // it across calls), which would trip the unrelated "guest" middleware
        // on subsequent attempts instead of exercising the throttle.
        for ($i = 0; $i < 2; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }
}
