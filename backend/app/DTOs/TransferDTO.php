<?php

namespace App\DTOs;

final readonly class TransferDTO
{
    public function __construct(
        public string $fromWalletId,
        public string $toWalletId,
        public int $amountCents,
    ) {
    }
}
