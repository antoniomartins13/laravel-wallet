<?php

namespace App\Services;

use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class StatementService
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactions,
    ) {
    }

    public function paginate(string $walletId, int $perPage): LengthAwarePaginator
    {
        return $this->transactions->paginateForWallet($walletId, $perPage);
    }
}
