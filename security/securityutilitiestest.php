<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once 'securityutilities.php';

class SecurityUtilitiesTest {
    public static function runTests() {
        // Test XSS protection
        $xssTest = '
<script>alert("XSS")</script>';
        $escaped = SecurityUtilities::escapeOutput($xssTest);
        echo "XSS Test: " . ($escaped === htmlspecialchars($xssTest, ENT_QUOTES) ? "PASSED" : "FAILED") . "\n";

        // Test input sanitization
        $inputTest = '<b>Test</b>';
        $sanitized = SecurityUtilities::sanitizeInput($inputTest);
        echo "Input Sanitization: " . ($sanitized === 'Test' ? "PASSED" : "FAILED") . "\n";

        // Test CSRF token generation/validation
        require_once __DIR__ . '/../config.php';
        require_once __DIR__ . '/../core/session_boot.php';
        cms_session_start('public');
        $token = SecurityUtilities::generateCsrfToken();
        echo "CSRF Generation: " . (strlen($token) === 64 ? "PASSED" : "FAILED") . "\n";
        echo "CSRF Validation: " . (SecurityUtilities::validateCsrfToken($token) ? "PASSED" : "FAILED") . "\n";

        // Test Laravel remnant removal
        $laravelCode = 'Route::get("/test"); Schema::create("test");';
        $cleaned = SecurityUtilities::removeLaravelRemnants($laravelCode);
        echo "Laravel Removal: " . (strpos($cleaned, 'Route::') === false && strpos($cleaned, 'Schema::') === false ? "PASSED" : "FAILED") . "\n";
    }
}

SecurityUtilitiesTest::runTests();
