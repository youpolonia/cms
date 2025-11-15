<?php
declare(strict_types=1);

namespace Includes\Validation;

final class StandardRules
{
    public static function getRules(): array
    {
        return [
            'required' => fn($value): bool => !empty($value),
            'email' => fn($value): bool => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'minLength' => fn($value, int $min): bool => strlen((string)$value) >= $min,
            'maxLength' => fn($value, int $max): bool => strlen((string)$value) <= $max,
            'numeric' => fn($value): bool => is_numeric($value),
            'alphanumeric' => fn($value): bool => ctype_alnum((string)$value),
            'regex' => fn($value, string $pattern): bool => preg_match($pattern, (string)$value) === 1,
            'in' => fn($value, string $options): bool => in_array($value, explode(',', $options)),
            'date' => fn($value): bool => strtotime($value) !== false,
            'url' => fn($value): bool => filter_var($value, FILTER_VALIDATE_URL) !== false
        ];
    }
}
