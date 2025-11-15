<?php
namespace Includes\Auth\Middleware;

use Includes\Database\Connection;
use admin\workers\WorkerPermissionsController;
use Includes\routingv2\MiddlewareInterface;
use Includes\routingv2\Request;
use Includes\routingv2\Response as RoutingResponse;

/**
 * WorkerAuthenticate - Middleware for worker authentication and permissions
 */
class WorkerAuthenticate implements MiddlewareInterface {
    protected $auth;
    protected $db;
    protected $jwtSecret;
    protected $tokenLifetime = 3600; // Default token lifetime: 1 hour

    public function __construct($auth, Connection $db = null, string $jwtSecret = null) {
        $this->auth = $auth;
        $this->db = $db;
        $this->jwtSecret = $jwtSecret ?? $_ENV['WORKER_JWT_SECRET'] ?? null;
        
        // Get token lifetime from config if available
        if (defined('WORKER_JWT_LIFETIME')) {
            $this->tokenLifetime = WORKER_JWT_LIFETIME;
        }
    }

    /**
     * Validate JWT token
     * 
     * @param string $token JWT token
     * @return array Token payload
     * @throws \RuntimeException If token is invalid
     */
    protected function validateJwt(string $token): array {
        if (!$this->jwtSecret) {
            throw new \RuntimeException('JWT secret not configured');
        }

        require_once __DIR__ . '/../../utilities/Base64Validator.php';
        $validator = new \Includes\utilities\Base64Validator();

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \RuntimeException('Invalid JWT format');
        }

        // Validate base64 encoding of signature and payload
        if (!$validator->isValidBase64($parts[2], true) || !$validator->isValidBase64($parts[1], true)) {
            throw new \RuntimeException('Invalid base64 encoding in JWT');
        }

        $signature = hash_hmac('sha256', "$parts[0].$parts[1]", $this->jwtSecret, true);
        $decodedSig = base64_decode(strtr($parts[2], '-_', '+/'), true);
        if ($decodedSig === false || !hash_equals($signature, $decodedSig)) {
            throw new \RuntimeException('Invalid JWT signature');
        }

        $decodedPayload = base64_decode(strtr($parts[1], '-_', '+/'), true);
        if ($decodedPayload === false) {
            throw new \RuntimeException('Invalid JWT payload encoding');
        }

        $payload = json_decode($decodedPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JWT payload');
        }
        
        // Log JWT token details for debugging PHASE5-WORKFLOW-STEP4
        if (class_exists('DebugWorkerMonitoringPhase5')) {
            \DebugWorkerMonitoringPhase5::logJwtDetails($payload);
        }
        
        // Check token expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \RuntimeException('JWT token has expired');
        }

        return $payload;
    }

    /**
     * Validate worker credentials
     * 
     * @param string $workerId Worker ID
     * @param string $secret Worker secret
     * @return bool True if credentials are valid
     * @throws \RuntimeException If database connection is not available
     */
    protected function validateWorkerCredentials(string $workerId, string $secret): bool {
        if (!$this->db) {
            throw new \RuntimeException('Database connection required for worker validation');
        }

        // Validate workerId format (UUID v4)
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $workerId)) {
            throw new \InvalidArgumentException('Invalid worker ID format');
        }

        $stmt = $this->db->prepare("
            SELECT secret_hash FROM workers
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$workerId]);
        $result = $stmt->fetch();

        return $result && password_verify($secret, $result['secret_hash']);
    }
    
    /**
     * Generate a new JWT token
     * 
     * @param string $workerId Worker ID
     * @param string $secret Worker secret
     * @return string JWT token
     */
    protected function generateJwt(string $workerId, string $secret): string {
        if (!$this->jwtSecret) {
            throw new \RuntimeException('JWT secret not configured');
        }
        
        require_once __DIR__ . '/../../utilities/Base64Validator.php';
        $validator = new \Includes\utilities\Base64Validator();
        
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->tokenLifetime;
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'worker_id' => $workerId,
            'secret' => $secret
        ];
        
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        $headerJson = json_encode($header);
        $payloadJson = json_encode($payload);
        
        if (!$validator->isValidBase64($headerJson, false) || !$validator->isValidBase64($payloadJson, false)) {
            throw new \RuntimeException('Invalid data for JWT encoding');
        }

        $headerEncoded = rtrim(strtr(base64_encode($headerJson), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');
        
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->jwtSecret, true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }
    
    /**
     * Refresh JWT token if it's close to expiration
     * 
     * @param array $payload Current token payload
     * @return string|null New JWT token or null if refresh not needed
     */
    protected function refreshTokenIfNeeded(array $payload): ?string {
        // Check if token is close to expiration (less than 10 minutes left)
        if (isset($payload['exp']) && isset($payload['worker_id']) && isset($payload['secret'])) {
            $timeLeft = $payload['exp'] - time();
            
            // If less than 10 minutes left, refresh the token
            if ($timeLeft < 600) {
                if (class_exists('DebugWorkerMonitoringPhase5')) {
                    \DebugWorkerMonitoringPhase5::logMessage('Refreshing JWT token with ' . $timeLeft . ' seconds left');
                }
                
                return $this->generateJwt($payload['worker_id'], $payload['secret']);
            }
        }
        
        return null;
    }

    /**
     * Process the request
     * 
     * @param Request $request Request object
     * @param callable $next Next middleware
     * @return RoutingResponse Response object
     */
    public function process(\Includes\routingv2\Request $request, callable $next): \Includes\routingv2\Response {
        try {
            // JWT validation for heartbeat requests
            if ($request->isHeartbeat) {
                if (empty($request->jwtToken)) {
                    return new RoutingResponse(401, ['error' => 'JWT token required for heartbeat']);
                }

                $payload = $this->validateJwt($request->jwtToken);
                if (!$this->validateWorkerCredentials($payload['worker_id'], $payload['secret'])) {
                    return new RoutingResponse(401, ['error' => 'Invalid worker credentials']);
                }
                
                // Check if token needs to be refreshed
                $newToken = $this->refreshTokenIfNeeded($payload);
                if ($newToken) {
                    // Add the new token to the response headers
                    $response = $next($request);
                    $response->headers['X-JWT-Refresh'] = $newToken;
                    
                    if (class_exists('DebugWorkerMonitoringPhase5')) {
                        \DebugWorkerMonitoringPhase5::logMessage('JWT token refreshed and included in response headers');
                    }
                    
                    return $response;
                }
            }
            // Standard session validation for other requests
            elseif (!$this->auth->isLoggedIn()) {
                return new RoutingResponse(401, ['error' => 'Unauthorized - Worker login required']);
            }

            // Check for required permission if specified
            if (!empty($request->requiredPermission)) {
                $userId = $this->auth->getUserId();
                $permissionsController = new WorkerPermissionsController($this->db);
                
                if (!$permissionsController->userHasPermission($userId, $request->requiredPermission)) {
                    return new RoutingResponse(403, ['error' => 'Forbidden - Insufficient permissions']);
                }
            }

            $response = $next($request);
            if (!$response instanceof \Includes\routingv2\Response) {
                return new RoutingResponse(200, $response);
            }
            return $response;
        } catch (\RuntimeException $e) {
            // Log the error for PHASE5-WORKFLOW-STEP4 debugging
            if (class_exists('DebugWorkerMonitoringPhase5')) {
                \DebugWorkerMonitoringPhase5::logError('WorkerAuthenticate error: ' . $e->getMessage(), $e);
            }
            
            return new RoutingResponse(401, ['error' => $e->getMessage()]);
        }
    }
}
