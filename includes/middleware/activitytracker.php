<?php
/**
 * Client Activity Tracking Middleware
 * Automatically logs client activities during requests
 */

class ActivityTracker {
    protected $activityModel;
    protected $excludedRoutes = [
        'admin/activity', // Prevent recursive logging
        'api/analytics'   // Exclude analytics endpoints
    ];

    public function __construct() {
        $this->activityModel = new ClientActivity();
    }

    public function handle($request, $next) {
        $response = $next($request);

        // Skip if route is excluded
        if (in_array($request->route, $this->excludedRoutes)) {
            return $response;
        }

        // Get client ID from session or request
        $clientId = $_SESSION['client_id'] ?? $request->get('client_id');
        if (!$clientId) {
            return $response;
        }

        // Get user ID if authenticated
        $userId = $_SESSION['user_id'] ?? null;

        // GDPR-compliant logging with encryption and pseudonymization
        $encryptedClientId = GdprDataHandler::encryptField($clientId);
        $maskedParams = GdprDataHandler::pseudonymize($request->params);
        
        $this->activityModel->logActivity(
            $encryptedClientId,
            $request->method . ' ' . $request->route,
            [
                'params' => $maskedParams,
                'original_params_hash' => hash('sha256', json_encode($request->params)),
                'status' => http_response_code(),
                'encryption_version' => GdprDataHandler::getCurrentKeyVersion()
            ],
            $userId
        );
        
        // Audit sensitive data access
        if (GdprDataHandler::containsPii($request->params)) {
            AuditLogger::logAccess(
                $userId,
                'activity_log',
                'Client activity recording',
                $encryptedClientId
            );
        }

        return $response;
    }
}
