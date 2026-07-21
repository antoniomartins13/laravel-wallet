<?php

namespace App\Repositories\Contracts;

use App\Models\Wallet;
use Illuminate\Support\Collection;

interface WalletRepositoryInterface
{
    /**
     * Find a wallet by id, locking the row for update within the current
     * database transaction.
     */
    public function findByIdForUpdate(string $id): Wallet;

    /**
     * Lock and return the wallets matching the given ids, in a deterministic
     * order (by id) so that concurrent transfers always acquire locks in
     * the same order and can never deadlock against each other.
     *
     * @param  array<int, string>  $ids
     * @return Collection<int, Wallet>
     */
    public function lockManyForUpdate(array $ids): Collection;

    /**
     * Atomically add (or subtract, if negative) cents to the wallet's
     * balance. The balance is never floored at zero.
     */
    public function incrementBalance(Wallet $wallet, int $cents): Wallet;
}
