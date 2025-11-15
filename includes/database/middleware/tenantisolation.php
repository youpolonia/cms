<?php
class TenantIsolation {
    public static function handle($request) {
        $tenantId = self::getTenantFromRequest($request);
        
        if (!self::validateTenant($tenantId)) {
            self::sendErrorResponse('Invalid tenant context', 403);
        }

        self::setCurrentTenant($tenantId);
    }

    private static function getTenantFromRequest($request) {
        return $request['headers']['X-Tenant-Context'] ?? null;
    }

    private static function validateTenant($tenantId) {
        require_once __DIR__.'/../../utilities/TenantValidator.php';
        return TenantValidator::validate($tenantId);
    }

    private static function setCurrentTenant($tenantId) {
        $_SESSION['current_tenant'] = $tenantId;
    }

    private static function sendErrorResponse($message, $code) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'error' => [
                'code' => $code,
                'message' => $message,
                'timestamp' => gmdate('c')
            ]
        ]);
        exit;
    }
}

function getCurrentTenant() {
    return $_SESSION['current_tenant'] ?? null;
}
