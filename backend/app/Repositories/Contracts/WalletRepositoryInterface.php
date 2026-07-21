<?php

namespace App\Repositories\Contracts;

use App\Models\Wallet;

interface WalletRepositoryInterface
{
    /**
     * Find a wallet by id, locking the row for update within the current
     * database transaction.
     */
    public function findByIdForUpdate(string $id): Wallet;

    /**
     * Atomically add (or subtract, if negative) cents to the wallet's
     * balance. The balance is never floored at zero.
     */
    public function incrementBalance(Wallet $wallet, int $cents): Wallet;
}
