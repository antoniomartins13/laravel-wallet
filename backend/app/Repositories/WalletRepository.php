<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Repositories\Contracts\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface
{
    public function findByIdForUpdate(string $id): Wallet
    {
        return Wallet::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function incrementBalance(Wallet $wallet, int $cents): Wallet
    {
        $wallet->increment('balance', $cents);

        return $wallet->refresh();
    }
}
