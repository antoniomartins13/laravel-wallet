<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'related_wallet_id' => $this->related_wallet_id,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'amount' => $this->amount,
            'reference_id' => $this->reference_id,
            'created_at' => $this->created_at,
        ];
    }
}
