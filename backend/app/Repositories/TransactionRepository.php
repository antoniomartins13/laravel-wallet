<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $attributes): Transaction
    {
        return Transaction::create($attributes);
    }

    public function update(Transaction $transaction, array $attributes): Transaction
    {
        $transaction->update($attributes);

        return $transaction;
    }

    public function paginateForWallet(string $walletId, int $perPage): LengthAwarePaginator
    {
        return Transaction::where('wallet_id', $walletId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
