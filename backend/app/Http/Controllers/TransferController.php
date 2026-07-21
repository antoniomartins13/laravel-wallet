<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TransferController extends Controller
{
    public function __construct(
        private readonly TransferService $transferService,
    ) {
    }

    public function store(TransferRequest $request): JsonResponse
    {
        $transaction = $this->transferService->transfer($request->toDTO());

        return TransactionResource::make($transaction)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
