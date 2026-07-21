<?php

namespace App\DTOs;

final readonly class DepositDTO
{
    public function __construct(
        public string $walletId,
        public int $amountCents,
        public ?string $ip = null,
        public ?string $userAgent = null,
    ) {
    }
}
