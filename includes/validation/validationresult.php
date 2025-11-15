<?php
declare(strict_types=1);

namespace Includes\Validation;

final class ValidationResult
{
    private bool $passed;
    private array $errors;

    public function __construct(bool $passed, array $errors)
    {
        $this->passed = $passed;
        $this->errors = $errors;
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
}
