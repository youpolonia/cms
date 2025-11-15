<?php
class GdprDataHandler {
    const PII_FIELDS = ['email', 'phone', 'ssn', 'address', 'credit_card'];
    
    public static function encryptField(string $value): string {
        $key = self::getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decryptField(string $encrypted): string {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
        $value = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
        return openssl_decrypt($value, 'aes-256-cbc', self::getEncryptionKey(), 0, $iv);
    }
    
    public static function pseudonymize(array $data): array {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), self::PII_FIELDS)) {
                $data[$key] = self::maskValue($value);
            } elseif (is_array($value)) {
                $data[$key] = self::pseudonymize($value);
            }
        }
        return $data;
    }

    private static function maskValue(string $value): string {
        $length = strlen($value);
        if ($length < 4) {
            return '****';
        }
        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }

    public static function containsPii(array $data): bool {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), self::PII_FIELDS)) {
                return true;
            }
            if (is_array($value) && self::containsPii($value)) {
                return true;
            }
        }
        return false;
    }

    private static function getEncryptionKey(): string {
        static $key;
        if (!$key) {
            $conn = \core\Database::connection();
            $result = $conn->query("
                SELECT key_value
                FROM encryption_keys
                WHERE version = (SELECT MAX(version) FROM encryption_keys)
            ");
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            $key = $row['key_value'] ?? '';
            if (empty($key)) {
                throw new Exception("No encryption key found in database");
            }
        }
        return $key;
    }

    public static function getCurrentKeyVersion(): int {
        static $version;
        if (!$version) {
            $conn = \core\Database::connection();
            $result = $conn->query("SELECT MAX(version) as current_version FROM encryption_keys");
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            $version = (int)($row['current_version'] ?? 0);
        }
        return $version;
    }

    public static function rotateKey(): void {
        $newKey = bin2hex(random_bytes(32));
        $conn = \core\Database::connection();
        $stmt = $conn->prepare("INSERT INTO encryption_keys (key_value) VALUES (?)");
        $stmt->execute([$newKey]);
    }
}
