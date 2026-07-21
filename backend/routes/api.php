<?php

use App\Http\Controllers\DepositController;
use App\Http\Controllers\ReversalController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/wallet', [WalletController::class, 'show']);
    Route::get('/wallets/lookup', [WalletController::class, 'lookup']);
    Route::get('/transactions', [StatementController::class, 'index']);

    Route::middleware('throttle:financial')->group(function () {
        Route::post('/deposits', [DepositController::class, 'store']);
        Route::post('/transfers', [TransferController::class, 'store']);
        Route::post('/transactions/{transaction}/reversal', [ReversalController::class, 'store']);
    });
});
