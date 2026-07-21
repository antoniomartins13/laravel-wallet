<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Services\StatementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatementController extends Controller
{
    public function __construct(
        private readonly StatementService $statementService,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        $transactions = $this->statementService->paginate($request->user()->wallet->id, $perPage);

        return TransactionResource::collection($transactions);
    }
}
