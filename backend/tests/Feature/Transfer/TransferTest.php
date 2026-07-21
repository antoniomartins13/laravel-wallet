<?php

namespace Tests\Feature\Transfer;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\User;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;

    public function test_transfer_moves_money_between_both_wallets_correctly(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 4000,
        ]);

        $response->assertCreated();

        $this->assertSame(6000, $sender->wallet->fresh()->balance);
        $this->assertSame(4000, $recipient->wallet->fresh()->balance);
    }

    public function test_transfer_creates_a_transfer_out_and_transfer_in_pair(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();

        $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 4000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $sender->wallet->id,
            'related_wallet_id' => $recipient->wallet->id,
            'type' => TransactionType::TransferOut->value,
            'status' => TransactionStatus::Completed->value,
            'amount' => 4000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $recipient->wallet->id,
            'related_wallet_id' => $sender->wallet->id,
            'type' => TransactionType::TransferIn->value,
            'status' => TransactionStatus::Completed->value,
            'amount' => 4000,
        ]);
    }

    public function test_transfer_with_insufficient_balance_is_rejected_with_a_clear_message(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 1000]);
        $recipient = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 4000,
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Saldo insuficiente para esta transferência.',
            'code' => 'INSUFFICIENT_BALANCE',
        ]);

        $this->assertSame(1000, $sender->wallet->fresh()->balance);
        $this->assertSame(0, $recipient->wallet->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_transfer_from_a_negative_balance_is_rejected(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => -2000]);
        $recipient = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 100,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('code', 'INSUFFICIENT_BALANCE');

        $this->assertSame(-2000, $sender->wallet->fresh()->balance);
        $this->assertSame(0, $recipient->wallet->fresh()->balance);
    }

    public function test_transferring_to_own_wallet_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->wallet->update(['balance' => 10000]);

        $response = $this->actingAs($user)->postJson('/api/transfers', [
            'to_wallet_id' => $user->wallet->id,
            'amount' => 1000,
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Não é possível transferir para a própria carteira.',
            'code' => 'SELF_TRANSFER',
        ]);

        $this->assertSame(10000, $user->wallet->fresh()->balance);
    }

    public function test_transfer_to_a_nonexistent_wallet_returns_not_found(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => (string) \Illuminate\Support\Str::uuid(),
            'amount' => 1000,
        ]);

        $response->assertNotFound();
        $response->assertExactJson([
            'message' => 'Carteira de destino não encontrada.',
            'code' => 'WALLET_NOT_FOUND',
        ]);

        $this->assertSame(10000, $sender->wallet->fresh()->balance);
    }

    public function test_transfer_ledger_entries_record_the_requesting_ip_and_user_agent(): void
    {
        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->withHeaders(['User-Agent' => 'TestAgent/1.0'])
            ->postJson('/api/transfers', [
                'to_wallet_id' => $recipient->wallet->id,
                'amount' => 4000,
            ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $sender->wallet->id,
            'type' => TransactionType::TransferOut->value,
            'metadata->ip' => '127.0.0.1',
            'metadata->user_agent' => 'TestAgent/1.0',
        ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $recipient->wallet->id,
            'type' => TransactionType::TransferIn->value,
            'metadata->ip' => '127.0.0.1',
            'metadata->user_agent' => 'TestAgent/1.0',
        ]);
    }

    public function test_unauthenticated_user_cannot_transfer(): void
    {
        $recipient = User::factory()->create();

        $response = $this->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 1000,
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Mocks the ledger write to fail right after the balances have already
     * been debited/credited within the same DB transaction, proving the
     * whole operation rolls back atomically — nothing partially persists.
     */
    public function test_transfer_rolls_back_everything_if_the_ledger_write_fails_after_the_debit(): void
    {
        config(['app.debug' => false]);

        $sender = User::factory()->create();
        $sender->wallet->update(['balance' => 10000]);
        $recipient = User::factory()->create();

        $transactions = Mockery::mock(TransactionRepositoryInterface::class);
        $transactions->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('simulated failure after the debit'));

        $this->app->instance(TransactionRepositoryInterface::class, $transactions);

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 4000,
        ]);

        $response->assertServerError();
        $response->assertExactJson(['message' => 'Server Error']);

        $this->assertSame(10000, $sender->wallet->fresh()->balance);
        $this->assertSame(0, $recipient->wallet->fresh()->balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_write_routes_are_rate_limited(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('financial', fn () => \Illuminate\Cache\RateLimiting\Limit::perMinute(2));

        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        for ($i = 0; $i < 2; $i++) {
            $this->actingAs($sender)->postJson('/api/transfers', [
                'to_wallet_id' => $recipient->wallet->id,
                'amount' => 1,
            ]);
        }

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'to_wallet_id' => $recipient->wallet->id,
            'amount' => 1,
        ]);

        $response->assertStatus(429);
    }
}
