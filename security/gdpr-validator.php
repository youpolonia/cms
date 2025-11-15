<?php
/**
 * GDPR Data Validation Utilities
 * 
 * Provides input sanitization and business logic validation for GDPR compliance
 */

class GDPRValidator {
    /**
     * Validate personal data fields
     * @param array $data Input data to validate
     * @return array Validated data or throws ValidationException
     */
    public static function validatePersonalData(array $data): array {
        $validated = [];
        
        // Required GDPR fields validation
        if (empty($data['consent_type'])) {
            throw new ValidationException('Consent type is required');
        }

        // Email validation
        if (isset($data['email'])) {
            $validated['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            if (!filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
                throw new ValidationException('Invalid email format');
            }
        }

        // Date validation (for consent dates)
        if (isset($data['consent_date'])) {
            $validated['consent_date'] = self::validateDate($data['consent_date']);
        }

        // IP address anonymization
        if (isset($data['ip_address'])) {
            $validated['ip_address'] = self::anonymizeIp($data['ip_address']);
        }

        return $validated;
    }

    /**
     * Anonymize IP address (last octet for IPv4, last 80 bits for IPv6)
     */
    private static function anonymizeIp(string $ip): string {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return preg_replace('/:[^:]+$/', ':0000', $ip);
        }
        return '0.0.0.0';
    }

    /**
     * Validate date format (ISO 8601)
     */
    private static function validateDate(string $date): string {
        $d = DateTime::createFromFormat('Y-m-d\TH:i:sP', $date);
        if (!$d || $d->format('Y-m-d\TH:i:sP') !== $date) {
            throw new ValidationException('Invalid date format, expected ISO 8601');
        }
        return $date;
    }

    /**
     * Validate consent options
     */
    public static function validateConsentOptions(array $options): array {
        $allowed = ['required', 'optional', 'withdrawn'];
        foreach ($options as $key => $value) {
            if (!in_array($value, $allowed, true)) {
                throw new ValidationException("Invalid consent option: $value");
            }
        }
        return $options;
    }
}

class ValidationException extends \RuntimeException {}
