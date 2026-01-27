<?php

if (!defined('CMS_ROOT')) {
    die('Access denied');
}

define('DEPLOY_TARGETS_FILE', CMS_ROOT . '/config/deploy_targets.json');

function deploy_targets_load(): array
{
    if (!file_exists(DEPLOY_TARGETS_FILE)) {
        return [
            'ok' => false,
            'error' => 'Deploy targets configuration file not found.',
            'targets' => []
        ];
    }

    $jsonContent = @file_get_contents(DEPLOY_TARGETS_FILE);
    if ($jsonContent === false) {
        return [
            'ok' => false,
            'error' => 'Unable to read deploy targets configuration.',
            'targets' => []
        ];
    }

    $decoded = json_decode($jsonContent, true);
    if (!is_array($decoded) || !isset($decoded['targets']) || !is_array($decoded['targets'])) {
        return [
            'ok' => false,
            'error' => 'Invalid deploy targets configuration format.',
            'targets' => []
        ];
    }

    $normalized = [];
    foreach ($decoded['targets'] as $key => $target) {
        if (!is_array($target)) {
            continue;
        }

        $method = $target['method'] ?? 'ftp';
        if ($method !== 'ftp') {
            continue;
        }

        $host = $target['host'] ?? '';
        if (empty($host)) {
            continue;
        }

        $normalized[$key] = [
            'label' => $target['label'] ?? $key,
            'method' => 'ftp',
            'host' => $host,
            'port' => isset($target['port']) ? (int)$target['port'] : 21,
            'username' => $target['username'] ?? '',
            'password' => $target['password'] ?? '',
            'path' => $target['path'] ?? '/',
            'passive' => isset($target['passive']) ? (bool)$target['passive'] : true,
            'ssl' => isset($target['ssl']) ? (bool)$target['ssl'] : false
        ];
    }

    return [
        'ok' => true,
        'error' => null,
        'targets' => $normalized
    ];
}

function deploy_package_to_target(string $targetKey): array
{
    try {
        $targetsInfo = deploy_targets_load();
        if (!$targetsInfo['ok']) {
            return [
                'ok' => false,
                'error' => $targetsInfo['error'],
                'target' => null,
                'remote_path' => null,
                'package_url' => null,
                'size' => null
            ];
        }

        $targets = $targetsInfo['targets'];
        if (!isset($targets[$targetKey])) {
            return [
                'ok' => false,
                'error' => 'Selected deploy target not found.',
                'target' => null,
                'remote_path' => null,
                'package_url' => null,
                'size' => null
            ];
        }

        $target = $targets[$targetKey];

        require_once CMS_ROOT . '/core/export_package.php';
        $packageInfo = export_build_package();

        if (!$packageInfo['ok']) {
            return [
                'ok' => false,
                'error' => $packageInfo['error'] ?? 'Failed to build export package.',
                'target' => $target['label'],
                'remote_path' => null,
                'package_url' => null,
                'size' => null
            ];
        }

        $localFile = $packageInfo['file'];
        $remoteFilename = basename($localFile);

        if ($target['ssl']) {
            $conn = @ftp_ssl_connect($target['host'], $target['port'], 30);
        } else {
            $conn = @ftp_connect($target['host'], $target['port'], 30);
        }

        if (!$conn) {
            return [
                'ok' => false,
                'error' => 'Unable to connect to deploy target server.',
                'target' => $target['label'],
                'remote_path' => null,
                'package_url' => $packageInfo['url'],
                'size' => $packageInfo['size']
            ];
        }

        if (!@ftp_login($conn, $target['username'], $target['password'])) {
            @ftp_close($conn);
            return [
                'ok' => false,
                'error' => 'Authentication failed on deploy target server.',
                'target' => $target['label'],
                'remote_path' => null,
                'package_url' => $packageInfo['url'],
                'size' => $packageInfo['size']
            ];
        }

        if ($target['passive']) {
            ftp_pasv($conn, true);
        }

        @ftp_chdir($conn, $target['path']);

        $uploadResult = @ftp_put($conn, $remoteFilename, $localFile, FTP_BINARY);
        $remotePath = rtrim($target['path'], '/') . '/' . $remoteFilename;

        @ftp_close($conn);

        if (!$uploadResult) {
            return [
                'ok' => false,
                'error' => 'Failed to upload package to deploy target.',
                'target' => $target['label'],
                'remote_path' => null,
                'package_url' => $packageInfo['url'],
                'size' => $packageInfo['size']
            ];
        }

        return [
            'ok' => true,
            'error' => null,
            'target' => $target['label'],
            'remote_path' => $remotePath,
            'package_url' => $packageInfo['url'],
            'size' => $packageInfo['size']
        ];

    } catch (Exception $e) {
        error_log('Deploy error: ' . get_class($e) . ': ' . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'An unexpected error occurred during deployment.',
            'target' => isset($target) ? $target['label'] : null,
            'remote_path' => null,
            'package_url' => isset($packageInfo) ? ($packageInfo['url'] ?? null) : null,
            'size' => isset($packageInfo) ? ($packageInfo['size'] ?? null) : null
        ];
    }
}
