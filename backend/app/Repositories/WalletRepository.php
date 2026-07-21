<?php

namespace App\Repositories;

use App\Models\Wallet;
use App\Repositories\Contracts\WalletRepositoryInterface;
use Illuminate\Support\Collection;

class WalletRepository implements WalletRepositoryInterface
{
    public function findByIdForUpdate(string $id): Wallet
    {
        return Wallet::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function lockManyForUpdate(array $ids): Collection
    {
        return Wallet::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
    }

    public function incrementBalance(Wallet $wallet, int $cents): Wallet
    {
        $wallet->increment('balance', $cents);

        return $wallet->refresh();
    }
}
