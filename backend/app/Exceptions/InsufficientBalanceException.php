<?php

namespace App\Exceptions;

class InsufficientBalanceException extends ApplicationException
{
    public function __construct(string $message = 'Saldo insuficiente para esta transferência.')
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'INSUFFICIENT_BALANCE';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
