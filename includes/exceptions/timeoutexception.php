<?php

namespace Includes\Exceptions;

/**
 * TimeoutException represents network operation timeouts
 */
class TimeoutException extends NetworkFailureException 
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) 
    {
        parent::__construct("Timeout: " . $message, $code, $previous);
    }
}
