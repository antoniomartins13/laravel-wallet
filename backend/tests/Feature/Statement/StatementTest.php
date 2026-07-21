<?php

namespace Tests\Feature\Statement;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_statement_lists_the_authenticated_users_transactions(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->deposit()->for($user->wallet, 'wallet')->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/transactions');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_statement_only_includes_the_authenticated_users_own_transactions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Transaction::factory()->deposit()->for($user->wallet, 'wallet')->count(2)->create();
        Transaction::factory()->deposit()->for($otherUser->wallet, 'wallet')->count(5)->create();

        $response = $this->actingAs($user)->getJson('/api/transactions');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $walletIds = collect($response->json('data'))->pluck('wallet_id')->unique();
        $this->assertEquals([$user->wallet->id], $walletIds->all());
    }

    public function test_statement_is_ordered_by_date_descending(): void
    {
        $user = User::factory()->create();

        $oldest = Transaction::factory()->deposit()->for($user->wallet, 'wallet')
            ->create(['created_at' => now()->subDays(2)]);
        $newest = Transaction::factory()->deposit()->for($user->wallet, 'wallet')
            ->create(['created_at' => now()]);
        $middle = Transaction::factory()->deposit()->for($user->wallet, 'wallet')
            ->create(['created_at' => now()->subDay()]);

        $response = $this->actingAs($user)->getJson('/api/transactions');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $newest->id);
        $response->assertJsonPath('data.1.id', $middle->id);
        $response->assertJsonPath('data.2.id', $oldest->id);
    }

    public function test_statement_is_paginated(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->deposit()->for($user->wallet, 'wallet')->count(20)->create();

        $response = $this->actingAs($user)->getJson('/api/transactions?per_page=5');

        $response->assertOk();
        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonPath('meta.total', 20);
        $response->assertJsonPath('meta.last_page', 4);
    }

    public function test_unauthenticated_user_cannot_view_the_statement(): void
    {
        $response = $this->getJson('/api/transactions');

        $response->assertUnauthorized();
    }

    public function test_statement_flags_whether_each_transaction_was_already_reversed(): void
    {
        $user = User::factory()->create();

        $depositId = $this->actingAs($user)
            ->postJson('/api/deposits', ['amount' => 5000])
            ->json('data.id');

        $this->actingAs($user)->postJson("/api/transactions/{$depositId}/reversal")->assertCreated();

        $response = $this->actingAs($user)->getJson('/api/transactions');

        $response->assertOk();

        $byId = collect($response->json('data'))->keyBy('id');
        $this->assertTrue($byId[$depositId]['is_reversed']);
        $reversalId = $byId->except($depositId)->keys()->first();
        $this->assertFalse($byId[$reversalId]['is_reversed']);
    }
}
