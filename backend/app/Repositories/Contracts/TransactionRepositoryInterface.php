<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Transaction;
}
