<?php

namespace App\Providers;

use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\WalletRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }
}
