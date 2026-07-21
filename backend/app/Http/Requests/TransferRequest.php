<?php

namespace App\Http\Requests;

use App\DTOs\TransferDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Existence of the destination wallet is intentionally NOT validated
     * here (only its format): a missing wallet is a domain-level 404
     * (WalletNotFoundException), not a 422 validation error.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'to_wallet_id' => ['required', 'uuid'],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDTO(): TransferDTO
    {
        return new TransferDTO(
            fromWalletId: $this->user()->wallet->id,
            toWalletId: $this->string('to_wallet_id')->toString(),
            amountCents: $this->integer('amount'),
            ip: $this->ip(),
            userAgent: $this->userAgent(),
        );
    }
}
