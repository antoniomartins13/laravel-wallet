<?php

namespace App\Services;

use App\DTOs\DepositDTO;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositService
{
    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly TransactionRepositoryInterface $transactions,
    ) {
    }

    public function deposit(DepositDTO $dto): Transaction
    {
        $transaction = DB::transaction(function () use ($dto) {
            $wallet = $this->wallets->findByIdForUpdate($dto->walletId);

            $this->wallets->incrementBalance($wallet, $dto->amountCents);

            return $this->transactions->create([
                'wallet_id' => $wallet->id,
                'type' => TransactionType::Deposit,
                'status' => TransactionStatus::Completed,
                'amount' => $dto->amountCents,
                'metadata' => [
                    'ip' => $dto->ip,
                    'user_agent' => $dto->userAgent,
                ],
            ]);
        });

        Log::channel('financial')->info('deposit.completed', [
            'transaction_id' => $transaction->id,
            'wallet_id' => $dto->walletId,
            'amount' => $dto->amountCents,
            'result' => 'success',
        ]);

        return $transaction;
    }
}
