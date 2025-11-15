<?php
namespace Includes;

class Validator {
    public static function validate(array $data, array $rules): array {
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

    public static function validateApiRequest(array $request, array $rules): array {
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

    public static function validateTenantContext(string $tenantId): bool {
        return preg_match('/^[a-f0-9]{32}$/', $tenantId) === 1;
    }

    public static function validateBulkOperations(array $operations): array {
        $errors = [];
        foreach ($operations as $i => $op) {
            if (empty($op['method'])) $errors[$i]['method'] = 'Method is required';
            if (empty($op['path'])) $errors[$i]['path'] = 'Path is required';
        }
        return $errors;
    }

    public static function validateFederationShareRequest(array $data): array {
        return self::validateApiRequest($data, [
            'content_id' => 'required|string',
            'target_tenants' => 'required|array',
            'version_hash' => 'required|string',
            'permissions' => 'required|array'
        ]);
    }

    public static function validateFederationSyncRequest(array $data): array {
        return self::validateApiRequest($data, [
            'content_id' => 'required|string',
            'source_tenant' => 'required|tenant',
            'version_history' => 'required|array'
        ]);
    }

    public static function validateFederationResolveRequest(array $data): array {
        return self::validateApiRequest($data, [
            'content_id' => 'required|string',
            'conflict_type' => 'required|in:timestamp,manual,branch',
            'versions' => 'required|array'
        ]);
    }

    public static function validateVersionMetadata(array $version): array {
        return self::validate($version, [
            'hash' => 'required|string',
            'timestamp' => 'required|numeric',
            'author' => 'required|string',
            'changes' => 'required|array'
        ]);
    }

    private static function passesRule($value, string $rule): bool {
        switch ($rule) {
            case 'required': return !empty($value);
            case 'email': return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'numeric': return is_numeric($value);
            case 'string': return is_string($value);
            case 'array': return is_array($value);
            case 'min': return strlen($value) >= ((int)explode(':', $rule)[1] ?? 0);
            case 'tenant': return self::validateTenantContext($value);
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
