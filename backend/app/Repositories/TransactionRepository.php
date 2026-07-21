<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $attributes): Transaction
    {
        return Transaction::create($attributes);
    }
}
