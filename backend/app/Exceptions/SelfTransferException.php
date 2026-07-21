<?php

namespace App\Exceptions;

class SelfTransferException extends ApplicationException
{
    public function __construct(string $message = 'Não é possível transferir para a própria carteira.')
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'SELF_TRANSFER';
    }

    public function statusCode(): int
    {
        return 422;
    }
}
