<?php

namespace App\Exceptions;

use Exception;

class OpenAIServiceException extends Exception
{
    public function __construct(string $message = "OpenAI service error", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}