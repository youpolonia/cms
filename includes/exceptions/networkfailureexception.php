<?php

namespace Includes\Exceptions;

/**
 * NetworkFailureException represents failures in network operations
 */
class NetworkFailureException extends \RuntimeException 
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) 
    {
        parent::__construct("Network failure: " . $message, $code, $previous);
    }
}
