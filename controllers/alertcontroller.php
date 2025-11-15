<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

class AlertController {
    public function addAlert(array $request): array {
        $alertManager = AlertManager::getInstance();
        $success = $alertManager->addAlert(
            $request['type'] ?? 'warning',
            $request['message'] ?? '',
            $request['conditions'] ?? []
        );
        
        return [
            'success' => $success,
            'alertId' => $success ? array_key_last($alertManager->getActiveAlerts()) : null
        ];
    }

    public function toggleAlert(array $request): array {
        $alertManager = AlertManager::getInstance();
        return [
            'success' => $alertManager->toggleAlert(
                $request['alertId'] ?? '',
                $request['active'] ?? false
            )
        ];
    }

    public function listAlerts(): array {
        return AlertManager::getInstance()->getActiveAlerts();
    }

    public function checkAlerts(): array {
        return AlertManager::getInstance()->checkAlerts();
    }
}
