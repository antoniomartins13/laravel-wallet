<?php

namespace App\Exceptions;

use Exception;

/**
 * Base class for domain/business-rule exceptions that the global exception
 * handler maps to a standardized JSON error response (message + code).
 */
abstract class ApplicationException extends Exception
{
    abstract public function errorCode(): string;

    abstract public function statusCode(): int;
}
