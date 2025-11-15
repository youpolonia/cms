<?php
/**
 * Emergency Access Controls
 * Framework-free implementation for PHP 8.1+
 */
class EmergencyMode {
    private static string $configFile = '/var/www/html/cms/config/emergency.json';
    private static array $defaultConfig = [
        'active' => false,
        'allowed_ips' => [],
        'basic_auth' => [
            'username' => '',
            'password' => ''
        ]
    ];

    public static function activate(string $username, string $password, array $allowedIPs = []): bool {
        $config = [
            'active' => true,
            'allowed_ips' => $allowedIPs,
            'basic_auth' => [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ]
        ];

        if (!file_put_contents(self::$configFile, json_encode($config))) {
            return false;
        }

        SecurityLogger::log('SYSTEM_CHANGE', 'Emergency mode activated', [
            'by' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        return true;
    }

    public static function deactivate(): bool {
        if (!file_put_contents(self::$configFile, json_encode(self::$defaultConfig))) {
            return false;
        }

        SecurityLogger::log('SYSTEM_CHANGE', 'Emergency mode deactivated', [
            'by' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        return true;
    }

    public static function isActive(): bool {
        if (!file_exists(self::$configFile)) return false;
        
        $config = json_decode(file_get_contents(self::$configFile), true);
        return $config['active'] ?? false;
    }

    public static function verifyAccess(): bool {
        if (!self::isActive()) return true;

        // Check IP whitelist
        $config = json_decode(file_get_contents(self::$configFile), true);
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (!empty($config['allowed_ips']) && !in_array($clientIP, $config['allowed_ips'])) {
            SecurityLogger::log('PERMISSION_DENIED', 'Emergency mode IP restriction', [
                'ip' => $clientIP
            ]);
            return false;
        }

        // Check basic auth
        if (!empty($config['basic_auth']['username'])) {
            if (!isset($_SERVER['PHP_AUTH_USER']) || 
                !isset($_SERVER['PHP_AUTH_PW']) ||
                $_SERVER['PHP_AUTH_USER'] !== $config['basic_auth']['username'] ||
                !password_verify($_SERVER['PHP_AUTH_PW'], $config['basic_auth']['password'])) {
                SecurityLogger::log('AUTH_FAILURE', 'Emergency mode auth failure', [
                    'ip' => $clientIP
                ]);
                return false;
            }
        }

        return true;
    }
}
