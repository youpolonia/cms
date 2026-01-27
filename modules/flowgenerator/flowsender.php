<?php
class FlowSender {
    private static $circuitOpen = false;
    private static $lastFailure = 0;

    public static function sendToN8n(string $url, array $payload): bool {
        if (self::$circuitOpen) {
            // Check if we should attempt to close circuit
            if (time() - self::$lastFailure > 300) { // 5 minute cooldown
                self::$circuitOpen = false;
            } else {
                return false;
            }
        }

        $config = json_decode(file_get_contents(__DIR__ . '/../../config/n8n_settings.json'), true);
        $headers = ['Content-Type: application/json'];
        if (!empty($config['webhook_secret'])) {
            $headers[] = 'X-N8N-Webhook-Secret: ' . $config['webhook_secret'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        // On failure, open circuit and record time
        self::$circuitOpen = true;
        self::$lastFailure = time();
        return false;
    }
}
