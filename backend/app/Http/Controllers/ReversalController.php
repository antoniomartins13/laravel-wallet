<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\ReversalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ReversalController extends Controller
{
    public function __construct(
        private readonly ReversalService $reversalService,
    ) {
    }

    public function store(Transaction $transaction): JsonResponse
    {
        Gate::authorize('reverse', $transaction);

        $reversal = $this->reversalService->reverse($transaction);

        return TransactionResource::make($reversal)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
