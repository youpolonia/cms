<?php
declare(strict_types=1);
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

/**
 * Marker Expiration Test Endpoint
 * Provides web-accessible testing for expiration system
 */
class MarkerExpirationTest
{
    public static function handleRequest(): void
    {
        header('Content-Type: application/json');
        
        try {
            $action = $_GET['action'] ?? 'check';
            $result = [];
            
            switch ($action) {
                case 'check':
                    $result = [
                        'expiring' => MarkerExpiration::checkExpiringMarkers(),
                        'expired' => MarkerExpiration::processExpiredMarkers()
                    ];
                    break;
                    
                case 'simulate':
                    $result = self::simulateExpiration();
                    break;
                    
                default:
                    throw new InvalidArgumentException("Invalid action");
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private static function simulateExpiration(): array
    {
        $db = \core\Database::connection();
        
        // Create test marker that expires in 1 minute
        $expirationTime = time() + 60;
        $db->insert('markers', [
            'title' => 'Test Expiring Marker',
            'user_id' => 1,
            'expiration_date' => $expirationTime,
            'created_at' => time()
        ]);
        $markerId = $db->lastInsertId();
        
        return [
            'marker_id' => $markerId,
            'expires_at' => $expirationTime,
            'check_url' => "/MarkerExpirationTest.php?action=check"
        ];
    }
}

// Handle request if executed directly
if (php_sapi_name() !== 'cli') {
    MarkerExpirationTest::handleRequest();
}
