<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['wallet_id', 'related_wallet_id', 'related_transaction_id', 'type', 'status', 'amount', 'reference_id', 'metadata'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function relatedWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }

    public function reference(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reference_id');
    }

    /**
     * The sibling leg of a transfer pair (transfer_out <-> transfer_in).
     */
    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id');
    }

    /**
     * The reversal transaction that reverses this one, if any.
     */
    public function reversedBy(): HasOne
    {
        return $this->hasOne(Transaction::class, 'reference_id');
    }
}
