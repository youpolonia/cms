<?php

namespace Includes;

class ContentValidator
{
    private $validators = [];
    private $fieldTypeRegistry = [];
    private $customValidators = [];

    public function __construct(array $fieldTypeRegistry = [])
    {
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->registerDefaultValidators();
    }

    private function registerDefaultValidators(): void
    {
        $this->registerValidator('required', new Validators\RequiredValidator());
        $this->registerValidator('min', new Validators\MinValidator());
        $this->registerValidator('max', new Validators\MaxValidator());
        $this->registerValidator('regex', new Validators\RegexValidator());
    }

    public function registerValidator(string $name, ValidatorInterface $validator): void
    {
        $this->customValidators[$name] = $validator;
    }

    public function validate(array $data, array $rules): array
    {
        $errors = [];
        $this->lastError = '';

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldType = $this->fieldTypeRegistry[$field] ?? 'string';

            foreach ($this->parseRules($fieldRules) as $rule) {
                if (!$this->validateRule($value, $rule, $fieldType)) {
                    $errors[$field] = $this->lastError;
                    break;
                }
            }
        }

        return $errors;
    }

    private function parseRules(string $rules): array
    {
        return explode('|', $rules);
    }

    private function validateRule($value, string $rule, string $fieldType): bool
    {
        $ruleName = explode(':', $rule)[0];
        
        // Check custom validators first
        if (isset($this->customValidators[$ruleName])) {
            $validator = $this->customValidators[$ruleName];
            $valid = $validator->validate($value, $rule);
            if (!$valid) {
                $this->lastError = $validator->getErrorMessage();
            }
            return $valid;
        }

        // Check field type specific validators
        if (isset($this->validators[$fieldType][$ruleName])) {
            $validator = $this->validators[$fieldType][$ruleName];
            $valid = $validator->validate($value, $rule);
            if (!$valid) {
                $this->lastError = $validator->getErrorMessage();
            }
            return $valid;
        }

        // Fallback to default validators
        if (isset($this->validators['default'][$ruleName])) {
            $validator = $this->validators['default'][$ruleName];
            $valid = $validator->validate($value, $rule);
            if (!$valid) {
                $this->lastError = $validator->getErrorMessage();
            }
            return $valid;
        }

        return true;
    }
}

interface ValidatorInterface
{
    public function validate($value, string $rule = ''): bool;
    public function getErrorMessage(): string;
}

namespace Includes\Validators;

class RequiredValidator implements \Includes\ValidatorInterface
{
    private string $rule = '';

    public function validate($value, string $rule = ''): bool
    {
        $this->rule = $rule;
        return !empty($value);
    }

    public function getErrorMessage(): string
    {
        return 'This field is required';
    }
}

class MinValidator implements \Includes\ValidatorInterface
{
    private string $rule = '';

    public function validate($value, string $rule = ''): bool
    {
        $this->rule = $rule;
        $min = (int)(explode(':', $rule)[1] ?? 0);
        
        if (is_numeric($value)) {
            return $value >= $min;
        }
        if (is_string($value)) {
            return strlen($value) >= $min;
        }
        if (is_array($value)) {
            return count($value) >= $min;
        }
        return false;
    }

    public function getErrorMessage(): string
    {
        $min = explode(':', $this->rule)[1] ?? '';
        return "Value must be at least $min";
    }
}

class MaxValidator implements \Includes\ValidatorInterface
{
    private string $rule = '';

    public function validate($value, string $rule = ''): bool
    {
        $this->rule = $rule;
        $max = (int)(explode(':', $rule)[1] ?? PHP_INT_MAX);
        
        if (is_numeric($value)) {
            return $value <= $max;
        }
        if (is_string($value)) {
            return strlen($value) <= $max;
        }
        if (is_array($value)) {
            return count($value) <= $max;
        }
        return false;
    }

    public function getErrorMessage(): string
    {
        $max = explode(':', $this->rule)[1] ?? '';
        return "Value must be at most $max";
    }
}

class RegexValidator implements \Includes\ValidatorInterface
{
    private string $rule = '';

    public function validate($value, string $rule = ''): bool
    {
        $this->rule = $rule;
        $pattern = explode(':', $rule)[1] ?? '';
        return $pattern && preg_match($pattern, $value) === 1;
    }

    public function getErrorMessage(): string
    {
        return 'Value must match the required pattern';
    }
}
