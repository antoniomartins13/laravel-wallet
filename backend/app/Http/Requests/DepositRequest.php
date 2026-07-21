<?php

namespace App\Http\Requests;

use App\DTOs\DepositDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDTO(): DepositDTO
    {
        return new DepositDTO(
            walletId: $this->user()->wallet->id,
            amountCents: $this->integer('amount'),
            ip: $this->ip(),
            userAgent: $this->userAgent(),
        );
    }
}
