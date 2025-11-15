<?php
/**
 * GDPR Request Validation Middleware
 * 
 * Validates and sanitizes GDPR-related requests
 */

require_once __DIR__.'/../security/gdpr-validator.php';

class GDPRRequestCheck {
    public function handle(array $request): array {
        try {
            // Validate personal data if present
            if (isset($request['personal_data'])) {
                $request['personal_data'] = GDPRValidator::validatePersonalData(
                    $request['personal_data']
                );
            }

            // Validate consent options if present
            if (isset($request['consent_options'])) {
                $request['consent_options'] = GDPRValidator::validateConsentOptions(
                    $request['consent_options']
                );
            }

            // Validate request purpose (required for GDPR compliance)
            if (empty($request['purpose'])) {
                throw new ValidationException('Processing purpose must be specified');
            }
            $request['purpose'] = htmlspecialchars($request['purpose'], ENT_QUOTES, 'UTF-8');

            return $request;
        } catch (ValidationException $e) {
            // Log validation error
            error_log("GDPR validation failed: " . $e->getMessage());
            throw $e;
        }
    }
}
