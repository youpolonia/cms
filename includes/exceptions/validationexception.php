<?php

namespace Includes\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $errors = [];
    protected $status = 422;

    public function __construct(array $errors = [], string $message = 'Validation failed')
    {
        parent::__construct($message, $this->status);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render()
    {
        return [
            'message' => $this->getMessage(),
            'errors' => $this->errors,
            'status' => $this->status
        ];
    }
}
