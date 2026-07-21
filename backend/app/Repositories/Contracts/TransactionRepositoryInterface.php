<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Transaction;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Transaction $transaction, array $attributes): Transaction;

    /**
     * Paginate a wallet's ledger, newest first.
     *
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function paginateForWallet(string $walletId, int $perPage): LengthAwarePaginator;
}
