<?php

namespace Tests\Feature\Reversal;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReversalTest extends TestCase
{
    use RefreshDatabase;

    public function test_reversal_of_a_deposit_returns_the_balance_and_creates_a_reversal_transaction_with_reference_id(): void
    {
        $user = User::factory()->create();

        $depositId = $this->actingAs($user)
            ->postJson('/api/deposits', ['amount' => 5000])
            ->json('data.id');

        $response = $this->actingAs($user)->postJson("/api/transactions/{$depositId}/reversal");

        $response->assertCreated();

        $this->assertSame(0, $user->wallet->fresh()->balance);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $user->wallet->id,
            'type' => TransactionType::Reversal->value,
            'status' => TransactionStatus::Completed->value,
            'amount' => 5000,
            'reference_id' => $depositId,
        ]);
    }

    public function test_reversal_of_a_transfer_returns_the_balance_to_both_wallets(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();

        $transferOutId = $this->actingAs($sender)
            ->postJson('/api/transfers', [
                'to_wallet_id' => $recipient->wallet->id,
                'amount' => 4000,
            ])
            ->json('data.id');

        $response = $this->actingAs($sender)->postJson("/api/transactions/{$transferOutId}/reversal");

        $response->assertCreated();

        $this->assertSame(10000, $sender->wallet->fresh()->balance);
        $this->assertSame(0, $recipient->wallet->fresh()->balance);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $sender->wallet->id,
            'type' => TransactionType::Reversal->value,
            'amount' => 4000,
            'reference_id' => $transferOutId,
        ]);

        $originalTransferIn = Transaction::where('wallet_id', $recipient->wallet->id)
            ->where('type', TransactionType::TransferIn)
            ->firstOrFail();

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $recipient->wallet->id,
            'type' => TransactionType::Reversal->value,
            'amount' => 4000,
            'reference_id' => $originalTransferIn->id,
        ]);
    }

    /**
     * The challenge's key scenario: recipient already spent the transferred
     * money, then the transfer gets reversed — balance may go negative.
     */
    public function test_reversal_of_a_transfer_can_leave_the_recipients_balance_negative(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();
        $thirdParty = User::factory()->create();

        $transferOutId = $this->actingAs($sender)
            ->postJson('/api/transfers', [
                'to_wallet_id' => $recipient->wallet->id,
                'amount' => 4000,
            ])
            ->json('data.id');

        // Recipient spends everything they just received.
        $this->actingAs($recipient)->postJson('/api/transfers', [
            'to_wallet_id' => $thirdParty->wallet->id,
            'amount' => 4000,
        ])->assertCreated();

        $this->assertSame(0, $recipient->wallet->fresh()->balance);

        $response = $this->actingAs($sender)->postJson("/api/transactions/{$transferOutId}/reversal");

        $response->assertCreated();

        $this->assertSame(10000, $sender->wallet->fresh()->balance);
        $this->assertSame(-4000, $recipient->wallet->fresh()->balance);
    }

    public function test_reversing_an_already_reversed_transaction_returns_conflict(): void
    {
        $user = User::factory()->create();

        $depositId = $this->actingAs($user)
            ->postJson('/api/deposits', ['amount' => 5000])
            ->json('data.id');

        $this->actingAs($user)->postJson("/api/transactions/{$depositId}/reversal")->assertCreated();

        $response = $this->actingAs($user)->postJson("/api/transactions/{$depositId}/reversal");

        $response->assertStatus(409);
        $response->assertExactJson([
            'message' => 'Esta transação já foi revertida.',
            'code' => 'TRANSACTION_ALREADY_REVERSED',
        ]);
    }

    public function test_only_participants_of_the_transaction_can_request_its_reversal(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();
        $outsider = User::factory()->create();

        $transferOutId = $this->actingAs($sender)
            ->postJson('/api/transfers', [
                'to_wallet_id' => $recipient->wallet->id,
                'amount' => 4000,
            ])
            ->json('data.id');

        $response = $this->actingAs($outsider)->postJson("/api/transactions/{$transferOutId}/reversal");

        $response->assertForbidden();

        $this->assertSame(6000, $sender->wallet->fresh()->balance);
        $this->assertSame(4000, $recipient->wallet->fresh()->balance);
    }

    public function test_the_recipient_can_also_request_reversal_of_a_transfer_they_did_not_initiate(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();

        $transferOutId = $this->actingAs($sender)
            ->postJson('/api/transfers', [
                'to_wallet_id' => $recipient->wallet->id,
                'amount' => 4000,
            ])
            ->json('data.id');

        $response = $this->actingAs($recipient)->postJson("/api/transactions/{$transferOutId}/reversal");

        $response->assertCreated();
        $this->assertSame(10000, $sender->wallet->fresh()->balance);
        $this->assertSame(0, $recipient->wallet->fresh()->balance);
    }

    public function test_unauthenticated_user_cannot_request_a_reversal(): void
    {
        $user = User::factory()->create();

        // Built directly (not via the HTTP API + actingAs) so this test
        // never authenticates the shared guard used across calls.
        $deposit = Transaction::factory()->deposit()->for($user->wallet, 'wallet')->create();

        $response = $this->postJson("/api/transactions/{$deposit->id}/reversal");

        $response->assertUnauthorized();
    }

    public function test_reversal_ledger_entry_records_the_requesting_ip_and_user_agent(): void
    {
        $user = User::factory()->create();

        $depositId = $this->actingAs($user)
            ->postJson('/api/deposits', ['amount' => 5000])
            ->json('data.id');

        $this->actingAs($user)
            ->withHeaders(['User-Agent' => 'TestAgent/1.0'])
            ->postJson("/api/transactions/{$depositId}/reversal");

        $this->assertDatabaseHas('transactions', [
            'reference_id' => $depositId,
            'metadata->ip' => '127.0.0.1',
            'metadata->user_agent' => 'TestAgent/1.0',
        ]);
    }
}
