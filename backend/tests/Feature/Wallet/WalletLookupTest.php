<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_a_recipient_wallet_by_email(): void
    {
        $searcher = User::factory()->create();
        $recipient = User::factory()->create();

        $response = $this->actingAs($searcher)->getJson('/api/wallets/lookup?email='.$recipient->email);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'wallet_id' => $recipient->wallet->id,
                'name' => $recipient->name,
            ],
        ]);
    }

    public function test_it_finds_a_recipient_wallet_by_cpf(): void
    {
        $searcher = User::factory()->create();
        $recipient = User::factory()->create();

        $response = $this->actingAs($searcher)->getJson('/api/wallets/lookup?cpf='.$recipient->cpf);

        $response->assertOk();
        $response->assertJsonPath('data.wallet_id', $recipient->wallet->id);
    }

    public function test_it_never_exposes_the_recipients_balance(): void
    {
        $searcher = User::factory()->create();
        $recipient = User::factory()->create();
        $recipient->wallet->update(['balance' => 999999]);

        $response = $this->actingAs($searcher)->getJson('/api/wallets/lookup?email='.$recipient->email);

        $response->assertOk();
        $response->assertJsonMissingPath('data.balance');
    }

    public function test_it_returns_not_found_for_an_unknown_recipient(): void
    {
        $searcher = User::factory()->create();

        $response = $this->actingAs($searcher)->getJson('/api/wallets/lookup?email=nobody@example.com');

        $response->assertNotFound();
    }

    public function test_it_requires_either_email_or_cpf(): void
    {
        $searcher = User::factory()->create();

        $response = $this->actingAs($searcher)->getJson('/api/wallets/lookup');

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_search_recipients(): void
    {
        $response = $this->getJson('/api/wallets/lookup?email=someone@example.com');

        $response->assertUnauthorized();
    }
}
