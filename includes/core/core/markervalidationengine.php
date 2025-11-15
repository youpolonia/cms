<?php
declare(strict_types=1);

/**
 * Marker Validation Engine - Validates marker templates and content
 */
class MarkerValidationEngine
{
    private static array $rules = [];
    private static array $customValidators = [];

    /**
     * Register validation rules
     */
    public static function registerRules(array $rules): void
    {
        self::$rules = array_merge(self::$rules, $rules);
    }

    /**
     * Add custom validator function
     */
    public static function addValidator(string $name, callable $validator): void
    {
        self::$customValidators[$name] = $validator;
    }

    /**
     * Validate template against rules
     */
    public static function validateTemplate(array $template): array
    {
        $errors = [];
        
        foreach ($template['fields'] ?? [] as $fieldName => $fieldConfig) {
            $fieldErrors = self::validateField($fieldName, $fieldConfig);
            if (!empty($fieldErrors)) {
                $errors[$fieldName] = $fieldErrors;
            }
        }

        return $errors;
    }

    private static function validateField(string $name, array $config): array
    {
        $errors = [];
        $rules = $config['validation'] ?? [];

        foreach ($rules as $rule => $params) {
            if (isset(self::$customValidators[$rule])) {
                $result = call_user_func(
                    self::$customValidators[$rule],
                    $name,
                    $config,
                    $params
                );
                if ($result !== true) {
                    $errors[$rule] = $result;
                }
            } elseif (method_exists(self::class, $rule)) {
                $result = self::$rule($name, $config, $params);
                if ($result !== true) {
                    $errors[$rule] = $result;
                }
            }
        }

        return $errors;
    }

    // Built-in validators
    private static function required(string $name, array $config): bool
    {
        return !empty($config['value']);
    }

    private static function maxLength(string $name, array $config, int $max): bool
    {
        return strlen($config['value'] ?? '') <= $max;
    }

    private static function pattern(string $name, array $config, string $regex): bool
    {
        return preg_match($regex, $config['value'] ?? '') === 1;
    }
}
