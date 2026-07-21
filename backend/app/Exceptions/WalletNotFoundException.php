<?php

namespace App\Exceptions;

class WalletNotFoundException extends ApplicationException
{
    public function __construct(string $message = 'Carteira de destino não encontrada.')
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'WALLET_NOT_FOUND';
    }

    public function statusCode(): int
    {
        return 404;
    }
}
