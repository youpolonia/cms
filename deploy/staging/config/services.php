<?php
/**
 * Service Configuration - Staging Environment
 * Framework-free PHP 8.1+ implementation
 */

class ServiceConfig {
    public static function getServices() {
        return [
            'api_endpoint' => $_ENV['API_ENDPOINT'] ?? 'https://api.staging.example.com',
            'auth_service' => [
                'url' => $_ENV['AUTH_SERVICE'] ?? 'https://auth.staging.example.com',
                'timeout' => 30
            ],
            'storage' => [
                'type' => 's3',
                'bucket' => $_ENV['STORAGE_BUCKET'] ?? 'cms-staging'
            ]
        ];
    }
}
