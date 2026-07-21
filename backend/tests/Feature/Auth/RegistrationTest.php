<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', $this->registrationPayload());

        $this->assertAuthenticated();
        $response->assertNoContent();
    }

    public function test_registration_creates_a_wallet_with_zero_balance(): void
    {
        $payload = $this->registrationPayload();

        $this->post('/register', $payload);

        $user = User::whereEmail($payload['email'])->firstOrFail();

        $this->assertNotNull($user->wallet);
        $this->assertSame(0, $user->wallet->balance);
    }

    public function test_registration_rejects_invalid_cpf(): void
    {
        $payload = $this->registrationPayload(['cpf' => '11111111111']);

        $response = $this->post('/register', $payload);

        $response->assertInvalid(['cpf']);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
    }

    public function test_registration_rejects_duplicate_cpf(): void
    {
        $existingUser = User::factory()->create();

        $payload = $this->registrationPayload([
            'cpf' => vsprintf('%d%d%d.%d%d%d.%d%d%d-%d%d', str_split($existingUser->cpf)),
        ]);

        $response = $this->post('/register', $payload);

        $response->assertInvalid(['cpf']);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
    }

    /**
     * Build a valid registration payload from the User factory, with a
     * plain-text password (the factory's is pre-hashed for direct DB use).
     *
     * @return array<string, string>
     */
    private function registrationPayload(array $overrides = []): array
    {
        $user = User::factory()->raw();

        return array_merge([
            'name' => $user['name'],
            'email' => $user['email'],
            'cpf' => $user['cpf'],
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }
}
