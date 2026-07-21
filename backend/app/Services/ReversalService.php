<?php

namespace App\Services;

use App\DTOs\ReversalDTO;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\TransactionAlreadyReversedException;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReversalService
{
    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly TransactionRepositoryInterface $transactions,
    ) {
    }

    public function reverse(ReversalDTO $dto): Transaction
    {
        try {
            $reversal = match ($dto->transaction->type) {
                TransactionType::Deposit => $this->reverseDeposit($dto),
                TransactionType::TransferOut, TransactionType::TransferIn => $this->reverseTransfer($dto),
                default => throw new TransactionAlreadyReversedException('Esta transação não pode ser revertida.'),
            };
        } catch (TransactionAlreadyReversedException $e) {
            Log::channel('financial')->warning('reversal.rejected', [
                'transaction_id' => $dto->transaction->id,
                'wallet_id' => $dto->transaction->wallet_id,
                'amount' => $dto->transaction->amount,
                'result' => 'rejected',
                'reason' => 'already_reversed',
            ]);

            throw $e;
        }

        Log::channel('financial')->info('reversal.completed', [
            'transaction_id' => $reversal->id,
            'reference_id' => $dto->transaction->id,
            'wallet_id' => $reversal->wallet_id,
            'amount' => $reversal->amount,
            'result' => 'success',
        ]);

        return $reversal;
    }

    private function reverseDeposit(ReversalDTO $dto): Transaction
    {
        return DB::transaction(function () use ($dto) {
            $original = $dto->transaction;

            $this->assertNotAlreadyReversed($original);

            $wallet = $this->wallets->findByIdForUpdate($original->wallet_id);

            // Undoing a deposit may take the balance negative if the money
            // was already spent — that's expected, reversals never floor.
            $this->wallets->incrementBalance($wallet, -$original->amount);

            return $this->transactions->create([
                'wallet_id' => $wallet->id,
                'type' => TransactionType::Reversal,
                'status' => TransactionStatus::Completed,
                'amount' => $original->amount,
                'reference_id' => $original->id,
                'metadata' => [
                    'ip' => $dto->ip,
                    'user_agent' => $dto->userAgent,
                ],
            ]);
        });
    }

    private function reverseTransfer(ReversalDTO $dto): Transaction
    {
        return DB::transaction(function () use ($dto) {
            $leg = $dto->transaction;
            $sibling = $leg->relatedTransaction()->lockForUpdate()->firstOrFail();

            [$transferOut, $transferIn] = $leg->type === TransactionType::TransferOut
                ? [$leg, $sibling]
                : [$sibling, $leg];

            $this->assertNotAlreadyReversed($transferOut);
            $this->assertNotAlreadyReversed($transferIn);

            $wallets = $this->wallets->lockManyForUpdate([$transferOut->wallet_id, $transferIn->wallet_id]);
            $senderWallet = $wallets->firstWhere('id', $transferOut->wallet_id);
            $recipientWallet = $wallets->firstWhere('id', $transferIn->wallet_id);

            // Give the sender their money back, claw it back from the
            // recipient — who may already have spent it, going negative.
            $this->wallets->incrementBalance($senderWallet, $transferOut->amount);
            $this->wallets->incrementBalance($recipientWallet, -$transferIn->amount);

            $metadata = [
                'ip' => $dto->ip,
                'user_agent' => $dto->userAgent,
            ];

            $reversalOut = $this->transactions->create([
                'wallet_id' => $senderWallet->id,
                'related_wallet_id' => $recipientWallet->id,
                'type' => TransactionType::Reversal,
                'status' => TransactionStatus::Completed,
                'amount' => $transferOut->amount,
                'reference_id' => $transferOut->id,
                'metadata' => $metadata,
            ]);

            $this->transactions->create([
                'wallet_id' => $recipientWallet->id,
                'related_wallet_id' => $senderWallet->id,
                'type' => TransactionType::Reversal,
                'status' => TransactionStatus::Completed,
                'amount' => $transferIn->amount,
                'reference_id' => $transferIn->id,
                'metadata' => $metadata,
            ]);

            return $reversalOut;
        });
    }

    private function assertNotAlreadyReversed(Transaction $transaction): void
    {
        if (Transaction::where('reference_id', $transaction->id)->exists()) {
            throw new TransactionAlreadyReversedException();
        }
    }
}
