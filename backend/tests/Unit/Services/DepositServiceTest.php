<?php

namespace Tests\Unit\Services;

use App\DTOs\DepositDTO;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Services\DepositService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Pure unit test: the repositories are mocked via their interfaces, so this
 * exercises only DepositService's orchestration logic — no Eloquent query,
 * no migration, no real row is ever touched. This is the concrete payoff of
 * depending on WalletRepositoryInterface/TransactionRepositoryInterface
 * instead of the Eloquent repositories directly.
 */
class DepositServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_locks_the_wallet_credits_it_and_writes_a_completed_deposit_to_the_ledger(): void
    {
        $dto = new DepositDTO(walletId: 'wallet-uuid', amountCents: 3000);

        $wallet = new Wallet();
        $wallet->id = 'wallet-uuid';
        $wallet->balance = -5000;

        $expectedTransaction = new Transaction();

        $wallets = Mockery::mock(WalletRepositoryInterface::class);
        $wallets->shouldReceive('findByIdForUpdate')
            ->once()
            ->ordered()
            ->with('wallet-uuid')
            ->andReturn($wallet);

        $wallets->shouldReceive('incrementBalance')
            ->once()
            ->ordered()
            ->with($wallet, 3000)
            ->andReturn($wallet);

        $transactions = Mockery::mock(TransactionRepositoryInterface::class);
        $transactions->shouldReceive('create')
            ->once()
            ->ordered()
            ->with([
                'wallet_id' => 'wallet-uuid',
                'type' => TransactionType::Deposit,
                'status' => TransactionStatus::Completed,
                'amount' => 3000,
            ])
            ->andReturn($expectedTransaction);

        $service = new DepositService($wallets, $transactions);

        $result = $service->deposit($dto);

        $this->assertSame($expectedTransaction, $result);
    }

    public function test_it_never_touches_the_wallet_repository_with_a_stale_instance(): void
    {
        $dto = new DepositDTO(walletId: 'wallet-uuid', amountCents: 1500);

        $lockedWallet = new Wallet();
        $lockedWallet->id = 'wallet-uuid';
        $lockedWallet->balance = 0;

        /** @var WalletRepositoryInterface&MockInterface $wallets */
        $wallets = Mockery::mock(WalletRepositoryInterface::class);
        $wallets->shouldReceive('findByIdForUpdate')->once()->andReturn($lockedWallet);
        $wallets->shouldReceive('incrementBalance')
            ->once()
            ->withArgs(fn (Wallet $wallet, int $cents) => $wallet === $lockedWallet && $cents === 1500)
            ->andReturn($lockedWallet);

        /** @var TransactionRepositoryInterface&MockInterface $transactions */
        $transactions = Mockery::mock(TransactionRepositoryInterface::class);
        $transactions->shouldReceive('create')->once()->andReturn(new Transaction());

        $service = new DepositService($wallets, $transactions);

        $service->deposit($dto);

        $wallets->shouldHaveReceived('incrementBalance')->once();
    }
}
