<?php

namespace Includes\Database;

/**
 * Custom exception class for database operations
 */
class DatabaseException extends \RuntimeException 
{
    // Specific error types
    public const CONNECTION_FAILED = 100;
    public const QUERY_FAILED = 200;
    public const CONFIGURATION_ERROR = 300;
    public const POOL_EXHAUSTED = 400;

    /**
     * @param string $message Error message
     * @param int $code Error code (use class constants)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null) 
    {
        parent::__construct($message, $code, $previous);
    }
}
