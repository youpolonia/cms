<?php
/**
 * Input Validation Middleware
 * 
 * Validates all API input data against defined rules
 */
class InputValidation {
    private $validationRules = [
        'email' => [
            'filter' => FILTER_VALIDATE_EMAIL,
            'message' => 'Invalid email format'
        ],
        'password' => [
            'callback' => [self::class, 'validatePassword'],
            'message' => 'Password must contain: 8+ chars, upper/lower case, number, special char'
        ],
        'username' => [
            'regex' => '/^[a-zA-Z0-9_]{3,20}$/',
            'message' => 'Username must be 3-20 alphanumeric chars'
        ]
    ];

    public function __invoke(array $request, callable $next): array {
        $errors = [];
        
        // Validate request body
        foreach ($request['body'] as $field => $value) {
            if (isset($this->validationRules[$field])) {
                $valid = $this->validateField($field, $value);
                if ($valid !== true) {
                    $errors[$field] = $valid;
                }
            }
        }

        // Validate query params
        foreach ($request['query'] as $field => $value) {
            if (isset($this->validationRules[$field])) {
                $valid = $this->validateField($field, $value);
                if ($valid !== true) {
                    $errors[$field] = $valid;
                }
            }
        }

        if (!empty($errors)) {
            return [
                'status' => 400,
                'body' => ['errors' => $errors]
            ];
        }

        return $next($request);
    }

    private function validateField(string $field, $value) {
        $rule = $this->validationRules[$field];
        
        if (isset($rule['filter'])) {
            if (!filter_var($value, $rule['filter'])) {
                return $rule['message'];
            }
        }
        
        if (isset($rule['regex'])) {
            if (!preg_match($rule['regex'], $value)) {
                return $rule['message'];
            }
        }
        
        if (isset($rule['callback'])) {
            if (!call_user_func($rule['callback'], $value)) {
                return $rule['message'];
            }
        }

        return true;
    }

    public static function validatePassword(string $password): bool {
        $config = $GLOBALS['config']['auth']['password'] ?? [];
        $hasUpper = $config['require_mixed_case'] ? preg_match('/[A-Z]/', $password) : true;
        $hasLower = $config['require_mixed_case'] ? preg_match('/[a-z]/', $password) : true;
        $hasNumber = $config['require_numbers'] ? preg_match('/[0-9]/', $password) : true;
        $hasSpecial = $config['require_special_chars'] ? preg_match('/[^A-Za-z0-9]/', $password) : true;
        
        return strlen($password) >= ($config['min_length'] ?? 8)
            && $hasUpper
            && $hasLower 
            && $hasNumber
            && $hasSpecial;
    }
}
