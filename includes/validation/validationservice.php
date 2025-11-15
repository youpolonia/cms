<?php
declare(strict_types=1);

namespace Includes\Validation;

final class ValidationService
{
    private array $rules = [];

    public function __construct()
    {
        $this->loadStandardRules();
    }

    public function validate(array $data, array $rules): ValidationResult
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $this->applyRule($data, $field, $rule, $errors);
            }
        }

        return new ValidationResult(empty($errors), $errors);
    }

    public function addRule(string $name, callable $validator): void
    {
        $this->rules[$name] = $validator;
    }

    private function applyRule(array $data, string $field, string $rule, array &$errors): void
    {
        $params = explode(':', $rule);
        $ruleName = $params[0];
        
        if (!isset($this->rules[$ruleName])) {
            throw new \InvalidArgumentException("Unknown validation rule: $ruleName");
        }

        $value = $data[$field] ?? null;
        $isValid = $this->rules[$ruleName]($value, ...array_slice($params, 1));

        if (!$isValid) {
            $errors[$field][] = $ruleName;
        }
    }

    private function loadStandardRules(): void
    {
        require_once __DIR__ . '/standardrules.php';
        $this->rules = StandardRules::getRules();
    }
}
