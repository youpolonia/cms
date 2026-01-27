<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../../core/csrf.php';

class PermissionVerifier {
    public static function verifyPermission($roleId, $permissionId) {
        $db = \core\Database::connection();
        
        try {
            $stmt = $db->prepare("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?");
            $stmt->execute([$roleId, $permissionId]);
            return ['hasPermission' => $stmt->rowCount() > 0];
        } catch (PDOException $e) {
            return ['hasPermission' => false, 'error' => $e->getMessage()];
        }
    }
}

// Test endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $result = PermissionVerifier::verifyPermission(
        $input['roleId'] ?? 0,
        $input['permissionId'] ?? 0
    );
    
    echo json_encode($result);
    exit;
}
