<?php

namespace App\DTOs;

use App\Models\Transaction;

final readonly class ReversalDTO
{
    public function __construct(
        public Transaction $transaction,
        public ?string $ip = null,
        public ?string $userAgent = null,
    ) {
    }
}
