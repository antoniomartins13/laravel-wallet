<?php

namespace Tests\Feature\Deposit;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_increases_the_wallet_balance(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/deposits', [
            'amount' => 3000,
        ]);

        $response->assertCreated();

        $this->assertSame(3000, $user->wallet->fresh()->balance);
    }

    /**
     * The challenge's key requirement: if the balance is negative for any
     * reason, a deposit must still simply add to it (never floor at zero).
     */
    public function test_deposit_is_added_even_when_the_balance_is_negative(): void
    {
        $user = User::factory()->create();
        $user->wallet->update(['balance' => -5000]);

        $response = $this->actingAs($user)->postJson('/api/deposits', [
            'amount' => 3000,
        ]);

        $response->assertCreated();

        $this->assertSame(-2000, $user->wallet->fresh()->balance);
    }

    public function test_zero_amount_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/deposits', [
            'amount' => 0,
        ]);

        $response->assertUnprocessable();
        $response->assertInvalid(['amount']);
        $this->assertSame(0, $user->wallet->fresh()->balance);
    }

    public function test_negative_amount_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/deposits', [
            'amount' => -100,
        ]);

        $response->assertUnprocessable();
        $response->assertInvalid(['amount']);
        $this->assertSame(0, $user->wallet->fresh()->balance);
    }

    public function test_unauthenticated_user_cannot_deposit(): void
    {
        $response = $this->postJson('/api/deposits', [
            'amount' => 3000,
        ]);

        $response->assertUnauthorized();
    }

    public function test_deposit_creates_a_ledger_entry_with_the_correct_type_and_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/deposits', [
            'amount' => 3000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $user->wallet->id,
            'type' => TransactionType::Deposit->value,
            'status' => TransactionStatus::Completed->value,
            'amount' => 3000,
            'related_wallet_id' => null,
        ]);
    }

    public function test_deposit_ledger_entry_records_the_requesting_ip_and_user_agent(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeaders(['User-Agent' => 'TestAgent/1.0'])
            ->postJson('/api/deposits', ['amount' => 3000]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $user->wallet->id,
            'metadata->ip' => '127.0.0.1',
            'metadata->user_agent' => 'TestAgent/1.0',
        ]);
    }
}
