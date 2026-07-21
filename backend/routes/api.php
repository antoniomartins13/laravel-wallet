<?php

use App\Http\Controllers\DepositController;
use App\Http\Controllers\ReversalController;
use App\Http\Controllers\TransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('throttle:financial')->group(function () {
        Route::post('/deposits', [DepositController::class, 'store']);
        Route::post('/transfers', [TransferController::class, 'store']);
        Route::post('/transactions/{transaction}/reversal', [ReversalController::class, 'store']);
    });
});
