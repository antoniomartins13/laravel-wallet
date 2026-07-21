<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Http\Resources\TransactionResource;
use App\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DepositController extends Controller
{
    public function __construct(
        private readonly DepositService $depositService,
    ) {
    }

    public function store(DepositRequest $request): JsonResponse
    {
        $transaction = $this->depositService->deposit($request->toDTO());

        return TransactionResource::make($transaction)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
