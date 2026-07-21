<?php

namespace App\Services;

use App\DTOs\TransferDTO;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\SelfTransferException;
use App\Exceptions\WalletNotFoundException;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferService
{
    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly TransactionRepositoryInterface $transactions,
    ) {
    }

    public function transfer(TransferDTO $dto): Transaction
    {
        if ($dto->fromWalletId === $dto->toWalletId) {
            $this->logRejected($dto, 'self_transfer');

            throw new SelfTransferException();
        }

        try {
            $transferOut = DB::transaction(function () use ($dto) {
                // Locking both wallets in a single, id-ordered query ensures
                // any two concurrent transfers always acquire row locks in
                // the same order, so they can never deadlock against
                // each other.
                $wallets = $this->wallets->lockManyForUpdate([$dto->fromWalletId, $dto->toWalletId]);

                $fromWallet = $wallets->firstWhere('id', $dto->fromWalletId);
                $toWallet = $wallets->firstWhere('id', $dto->toWalletId);

                if (! $fromWallet || ! $toWallet) {
                    throw new WalletNotFoundException();
                }

                if ($fromWallet->balance < $dto->amountCents) {
                    throw new InsufficientBalanceException();
                }

                $this->wallets->incrementBalance($fromWallet, -$dto->amountCents);
                $this->wallets->incrementBalance($toWallet, $dto->amountCents);

                $metadata = [
                    'ip' => $dto->ip,
                    'user_agent' => $dto->userAgent,
                ];

                $transferOut = $this->transactions->create([
                    'wallet_id' => $fromWallet->id,
                    'related_wallet_id' => $toWallet->id,
                    'type' => TransactionType::TransferOut,
                    'status' => TransactionStatus::Completed,
                    'amount' => $dto->amountCents,
                    'metadata' => $metadata,
                ]);

                $transferIn = $this->transactions->create([
                    'wallet_id' => $toWallet->id,
                    'related_wallet_id' => $fromWallet->id,
                    'related_transaction_id' => $transferOut->id,
                    'type' => TransactionType::TransferIn,
                    'status' => TransactionStatus::Completed,
                    'amount' => $dto->amountCents,
                    'metadata' => $metadata,
                ]);

                $this->transactions->update($transferOut, [
                    'related_transaction_id' => $transferIn->id,
                ]);

                return $transferOut;
            });
        } catch (WalletNotFoundException $e) {
            $this->logRejected($dto, 'wallet_not_found');

            throw $e;
        } catch (InsufficientBalanceException $e) {
            $this->logRejected($dto, 'insufficient_balance');

            throw $e;
        }

        Log::channel('financial')->info('transfer.completed', [
            'transaction_id' => $transferOut->id,
            'from_wallet_id' => $dto->fromWalletId,
            'to_wallet_id' => $dto->toWalletId,
            'amount' => $dto->amountCents,
            'result' => 'success',
        ]);

        return $transferOut;
    }

    private function logRejected(TransferDTO $dto, string $reason): void
    {
        Log::channel('financial')->warning('transfer.rejected', [
            'from_wallet_id' => $dto->fromWalletId,
            'to_wallet_id' => $dto->toWalletId,
            'amount' => $dto->amountCents,
            'result' => 'rejected',
            'reason' => $reason,
        ]);
    }
}
