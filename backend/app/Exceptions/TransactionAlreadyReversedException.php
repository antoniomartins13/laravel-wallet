<?php

namespace App\Exceptions;

class TransactionAlreadyReversedException extends ApplicationException
{
    public function __construct(string $message = 'Esta transação já foi revertida.')
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'TRANSACTION_ALREADY_REVERSED';
    }

    public function statusCode(): int
    {
        return 409;
    }
}
