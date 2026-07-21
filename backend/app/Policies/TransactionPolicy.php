<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Only the two wallets involved in the transaction (the owner of
     * wallet_id, and — for a transfer leg — the owner of related_wallet_id)
     * may request its reversal.
     */
    public function reverse(User $user, Transaction $transaction): bool
    {
        $walletId = $user->wallet?->id;

        return $walletId !== null
            && ($walletId === $transaction->wallet_id || $walletId === $transaction->related_wallet_id);
    }
}
