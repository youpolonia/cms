<?php
namespace Includes;

class ContentFederationValidator {
    public static function validateShareRequest(array $data): array {
        $rules = [
            'content_id' => 'required|string',
            'target_tenants' => 'required|array',
            'version_hash' => 'required|string',
            'permissions' => 'required|array'
        ];
        return self::validateApiRequest($data, $rules);
    }

    public static function validateSyncRequest(array $data): array {
        $rules = [
            'content_id' => 'required|string',
            'source_tenant' => 'required|tenant',
            'version_history' => 'required|array'
        ];
        return self::validateApiRequest($data, $rules);
    }

    public static function validateResolveRequest(array $data): array {
        $rules = [
            'content_id' => 'required|string',
            'conflict_type' => 'required|in:timestamp,manual,branch',
            'versions' => 'required|array'
        ];
        return self::validateApiRequest($data, $rules);
    }

    public static function validateVersionMetadata(array $version): array {
        $rules = [
            'hash' => 'required|string',
            'timestamp' => 'required|numeric',
            'author' => 'required|string',
            'changes' => 'required|array'
        ];
        return self::validate($version, $rules);
    }

    private static function validate(array $data, array $rules): array {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $rules = explode('|', $ruleSet);
            foreach ($rules as $rule) {
                if (!self::passesRule($value, $rule)) {
                    $errors[$field] = self::formatError($field, $rule, $value);
                    break;
                }
            }
        }
        return $errors;
    }

    private static function validateApiRequest(array $request, array $rules): array {
        $errors = self::validate($request, $rules);
        if (!empty($errors)) {
            return [
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Request validation failed',
                    'errors' => $errors,
                    'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
                ]
            ];
        }
        return [];
    }

    private static function passesRule($value, string $rule): bool {
        switch ($rule) {
            case 'required': return !empty($value);
            case 'email': return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'numeric': return is_numeric($value);
            case 'string': return is_string($value);
            case 'array': return is_array($value);
            case 'min': return strlen($value) >= ((int)explode(':', $rule)[1] ?? 0);
            case 'tenant': return preg_match('/^[a-f0-9]{32}$/', $value) === 1;
            case 'in': return in_array($value, explode(',', explode(':', $rule)[1] ?? ''));
            default: return true;
        }
    }

    private static function formatError(string $field, string $rule, $value): string {
        $messages = [
            'required' => "Field {$field} is required",
            'email' => "Field {$field} must be a valid email",
            'numeric' => "Field {$field} must be numeric",
            'string' => "Field {$field} must be a string",
            'array' => "Field {$field} must be an array",
            'min' => "Field {$field} must be at least " . (explode(':', $rule)[1] ?? '') . " characters",
            'tenant' => "Invalid tenant context format",
            'in' => "Field {$field} must be one of: " . str_replace(':', ', ', explode(':', $rule)[1] ?? '')
        ];
        return $messages[$rule] ?? "Validation failed for {$field}";
    }
}
