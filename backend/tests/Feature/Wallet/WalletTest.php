<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_authenticated_users_own_wallet(): void
    {
        $user = User::factory()->create();
        $user->wallet->update(['balance' => 4200]);

        $response = $this->actingAs($user)->getJson('/api/wallet');

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->wallet->id);
        $response->assertJsonPath('data.balance', 4200);
    }

    public function test_unauthenticated_user_cannot_view_the_wallet(): void
    {
        $response = $this->getJson('/api/wallet');

        $response->assertUnauthorized();
    }
}
