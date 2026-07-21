<?php

namespace App\Http\Controllers;

use App\DTOs\ReversalDTO;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\ReversalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ReversalController extends Controller
{
    public function __construct(
        private readonly ReversalService $reversalService,
    ) {
    }

    public function store(Request $request, Transaction $transaction): JsonResponse
    {
        Gate::authorize('reverse', $transaction);

        $dto = new ReversalDTO(
            transaction: $transaction,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $reversal = $this->reversalService->reverse($dto);

        return TransactionResource::make($reversal)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
