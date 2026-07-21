<?php

namespace App\Http\Controllers;

use App\Http\Resources\WalletResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request): WalletResource
    {
        return WalletResource::make($request->user()->wallet);
    }

    /**
     * Resolve a transfer recipient by email or CPF, for the "search
     * recipient" step of the transfer flow. Intentionally returns only the
     * wallet id and owner name — never the recipient's balance or other PII.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['nullable', 'string', 'email'],
            'cpf' => ['nullable', 'string', 'digits:11'],
        ]);

        if (! $request->filled('email') && ! $request->filled('cpf')) {
            abort(422, 'Informe e-mail ou CPF.');
        }

        $recipient = User::query()
            ->when(
                $request->filled('email'),
                fn ($query) => $query->where('email', $request->string('email')),
            )
            ->when(
                $request->filled('cpf'),
                fn ($query) => $query->where('cpf', preg_replace('/\D/', '', (string) $request->string('cpf'))),
            )
            ->first();

        abort_if($recipient?->wallet === null, 404, 'Destinatário não encontrado.');

        return response()->json([
            'data' => [
                'wallet_id' => $recipient->wallet->id,
                'name' => $recipient->name,
            ],
        ]);
    }
}
