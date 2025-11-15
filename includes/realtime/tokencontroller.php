<?php
namespace CMS\Realtime;

require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../Auth/SessionManager.php'; // For CMS\Auth\SessionManager
require_once __DIR__ . '/sessionvalidator.php';   // For CMS\Realtime\SessionValidator

class TokenController {
    protected $validator;
    protected $sessionManager;

    public function __construct() {
        $this->sessionManager = new \CMS\Auth\SessionManager();
        $this->validator = new SessionValidator(
            $this->getDatabaseConnection(),
            (defined('WS_ENCRYPTION_KEY') ? WS_ENCRYPTION_KEY : '')
        );
    }

    public function generateToken(string $documentId): array {
        $session = $this->sessionManager->getCurrentSession();
        if (!$session || !$session['user_id']) {
            return ['error' => 'Not authenticated'];
        }

        // Verify document access first
        if (!$this->validator->validateDocumentAccess($session['user_id'], $documentId)) {
            return ['error' => 'No document access'];
        }

        $token = $this->validator->generateToken(
            $session['session_id'],
            $session['user_id']
        );

        return [
            'token' => $token,
            'expires_in' => 3600 // 1 hour
        ];
    }

    protected function getDatabaseConnection(): \PDO {
        return \core\Database::connection();
    }
}
