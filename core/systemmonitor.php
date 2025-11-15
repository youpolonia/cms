<?php
require_once __DIR__.'/../utilities/TokenMonitor.php';
require_once __DIR__ . '/modetokenguard.php';

class SystemMonitor {
    const MAX_TOKENS = 64000; // System token limit
    const EMERGENCY_ENDPOINT = '/api/system/emergency';
    
    public static function checkSystemHealth() {
        // Check both system-wide and mode-specific limits
        $currentUsage = self::getCurrentTokenUsage();
        
        if (!TokenMonitor::checkUsage($currentUsage, self::MAX_TOKENS) ||
            !ModeTokenGuard::enforceModeLimits()) {
            return self::triggerSafetyProtocols();
        }
        return true;
    }

    private static function getCurrentTokenUsage() {
        // Implementation would interface with environment API
        return 30000; // Example value
    }

    private static function triggerSafetyProtocols(): bool {
        // 1. Save critical state
        self::saveEmergencyState();
        
        // 2. Log emergency
        self::logEmergency();
        
        // 3. POST to emergency endpoint
        return self::postToEmergencyEndpoint();
    }

    private static function saveEmergencyState(): void {
        $state = [
            'timestamp' => time(),
            'token_usage' => self::getCurrentTokenUsage(),
            'mode' => ModeTokenGuard::getCurrentMode()
        ];

        file_put_contents(
            __DIR__.'/../logs/emergency_state.json',
            json_encode($state)
        );
    }

    private static function logEmergency(): void {
        $logEntry = date('Y-m-d H:i:s')." - System-wide token limit exceeded\n";
        file_put_contents(
            __DIR__.'/../logs/quota_log.md',
            $logEntry,
            FILE_APPEND
        );
    }

    private static function postToEmergencyEndpoint(): bool {
        $payload = [
            'timestamp' => time(),
            'token_usage' => self::getCurrentTokenUsage(),
            'mode' => ModeTokenGuard::getCurrentMode(),
            'emergency_type' => 'token_limit_exceeded',
            'model' => TokenMonitor::getCurrentModel(),
            'quota_status' => TokenMonitor::getQuotaStatus(),
            'fallback_attempted' => ModeTokenGuard::wasFallbackAttempted()
        ];

        $retryCount = 0;
        $maxRetries = 3;
        $success = false;

        while ($retryCount < $maxRetries && !$success) {
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, self::EMERGENCY_ENDPOINT);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-Emergency-Token: ' . self::generateEmergencyToken()
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $success = true;
                    self::logEmergencyResponse($response);
                } else {
                    throw new Exception("Emergency endpoint returned HTTP $httpCode");
                }
            } catch (Exception $e) {
                $retryCount++;
                if ($retryCount < $maxRetries) {
                    sleep(1 << $retryCount); // Exponential backoff
                }
                self::logEmergencyError($e->getMessage());
            }
        }

        return $success;
    }

    private static function generateEmergencyToken(): string {
        $model = TokenMonitor::getCurrentModel();
        $time = time();
        $key = 'emergency_secret_key_' . $model;
        return hash_hmac('sha256', $time, $key);
    }

    private static function logEmergencyResponse(string $response): void {
        file_put_contents(
            __DIR__.'/../logs/emergency_log.md',
            date('Y-m-d H:i:s')." - Emergency response: $response\n",
            FILE_APPEND
        );
    }

    private static function logEmergencyError(string $error): void {
        file_put_contents(
            __DIR__.'/../logs/emergency_errors.md',
            date('Y-m-d H:i:s')." - Emergency error: $error\n",
            FILE_APPEND
        );
    }
}
