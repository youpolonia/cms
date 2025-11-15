<?php
namespace CMS\Realtime;

class SessionValidator {
    protected $db;
    protected $encryptionKey;

    public function __construct(\PDO $db, string $encryptionKey) {
        $this->db = $db;
        $this->encryptionKey = $encryptionKey;
    }

    public function validateDocumentAccess(string $userId, string $documentId): bool {
        $stmt = $this->db->prepare("
            SELECT 1 FROM document_permissions
            WHERE user_id = :user_id
            AND document_id = :document_id
            AND (permission = 'edit' OR permission = 'view')
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':document_id' => $documentId
        ]);
        return (bool)$stmt->fetch();
    }

    public function validateSession(string $token, string $documentId): bool {
        try {
            // Decrypt token
            $decrypted = $this->decryptToken($token);
            $data = json_decode($decrypted, true);

            if (!$data || !isset($data['session_id']) || !isset($data['user_id'])) {
                return false;
            }

            // Check session in database
            $stmt = $this->db->prepare("
                SELECT 1 FROM user_sessions 
                WHERE session_id = :session_id 
                AND user_id = :user_id 
                AND expires_at > NOW()
            ");
            $stmt->execute([
                ':session_id' => $data['session_id'],
                ':user_id' => $data['user_id']
            ]);

            if (!$stmt->fetch()) {
                return false;
            }

            // Check document access
            $stmt = $this->db->prepare("
                SELECT 1 FROM document_permissions 
                WHERE user_id = :user_id 
                AND document_id = :document_id
                AND (permission = 'edit' OR permission = 'view')
            ");
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':document_id' => $documentId
            ]);

            return (bool)$stmt->fetch();
        } catch (\Exception $e) {
            error_log("Session validation error: " . $e->getMessage());
            return false;
        }
    }

    protected function decryptToken(string $token): string {
        $iv = substr($token, 0, 16);
        $encrypted = substr($token, 16);
        return openssl_decrypt(
            $encrypted,
            'aes-256-cbc',
            $this->encryptionKey,
            0,
            $iv
        );
    }

    public function generateToken(string $sessionId, string $userId): string {
        $iv = random_bytes(16);
        $data = json_encode([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'created_at' => time()
        ]);
        $encrypted = openssl_encrypt(
            $data,
            'aes-256-cbc',
            $this->encryptionKey,
            0,
            $iv
        );
        return $iv . $encrypted;
    }
}
