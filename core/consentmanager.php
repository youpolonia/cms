<?php
/**
 * GDPR-compliant consent management system
 */
class ConsentManager {
    private static $storagePath = __DIR__ . '/../storage/consents/';
    private static $cookieName = 'gdpr_consents';
    private static $cookieExpiry = 365; // Days
    private static $consentVersions = [
        'essential' => '1.0',
        'analytics' => '1.1',
        'marketing' => '1.0'
    ];

    public static function initialize() {
        if (!file_exists(self::$storagePath)) {
            mkdir(self::$storagePath, 0755, true);
        }
    }

    public static function giveConsent($type, $userId = null) {
        self::initialize();
        $consentId = uniqid('consent_', true);
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $consentData = [
            'id' => $consentId,
            'type' => $type,
            'version' => self::$consentVersions[$type] ?? '1.0',
            'given_at' => time(),
            'user_id' => $userId,
            'ip_hash' => hash('sha256', $ip),
            'user_agent_hash' => hash('sha256', $userAgent),
            'withdrawn' => false
        ];

        // Store in file system (could be replaced with DB)
        $file = self::$storagePath . $consentId . '.json';
        file_put_contents($file, json_encode($consentData));

        // Update cookie
        self::updateCookieConsents($type, true);

        // Log consent
        self::logConsentAction('given', $type, $userId, $ip);

        return $consentId;
    }

    public static function withdrawConsent($consentId) {
        self::initialize();
        $file = self::$storagePath . $consentId . '.json';
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            $data['withdrawn'] = true;
            $data['withdrawn_at'] = time();
            file_put_contents($file, json_encode($data));

            // Update cookie
            self::updateCookieConsents($data['type'], false);

            // Log withdrawal
            self::logConsentAction('withdrawn', $data['type'], 
                $data['user_id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            
            return true;
        }
        return false;
    }

    public static function hasConsent($type, $userId = null) {
        // Check cookie first
        if (!self::checkCookieConsent($type)) {
            return false;
        }

        // Check stored consents
        $consents = glob(self::$storagePath . '*.json');
        foreach ($consents as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data['type'] === $type && 
                !$data['withdrawn'] && 
                ($userId === null || $data['user_id'] === $userId)) {
                return true;
            }
        }
        return false;
    }

    private static function updateCookieConsents($type, $value) {
        $consents = $_COOKIE[self::$cookieName] ?? '{}';
        $consents = json_decode($consents, true);
        $consents[$type] = $value;
        setcookie(
            self::$cookieName,
            json_encode($consents),
            time() + (self::$cookieExpiry * 24 * 60 * 60),
            '/',
            '',
            true,
            true
        );
    }

    private static function checkCookieConsent($type) {
        $consents = $_COOKIE[self::$cookieName] ?? '{}';
        $consents = json_decode($consents, true);
        return $consents[$type] ?? false;
    }

    private static function logConsentAction($action, $type, $userId, $ip) {
        $logFile = self::$storagePath . 'consent_log_' . date('Y-m-d') . '.log';
        $logEntry = sprintf(
            "[%s] %s: type=%s user=%s ip=%s\n",
            date('c'),
            $action,
            $type,
            $userId ?? 'guest',
            $ip
        );
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    public static function getConsentVersions() {
        return self::$consentVersions;
    }
}
