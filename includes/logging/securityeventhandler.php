<?php
namespace Logging;

class SecurityEventHandler {
    public static function handleEvent(string $eventType, array $data = []): void {
        $logger = Logger::getInstance('security');
        
        $logData = [
            'event' => $eventType,
            'data' => $data,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $logger->warning('Security event detected', $logData);
    }
}
