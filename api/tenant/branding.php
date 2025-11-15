<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/thememanager.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
    
    if (empty($tenantId)) {
        throw new RuntimeException('Missing X-Tenant-ID header');
    }

    switch ($method) {
        case 'GET':
            $config = \includes\ThemeManager::getActiveTheme($tenantId);
            echo json_encode([
                'status' => 'success',
                'data' => $config
            ]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                throw new RuntimeException('Invalid input data');
            }

            // Handle file uploads
            if (!empty($_FILES['logo'])) {
                $uploadDir = "themes/{$tenantId}/assets/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $input['logo'] = self::handleFileUpload($_FILES['logo'], $uploadDir);
            }

            // Save config
            $configPath = "themes/{$tenantId}/theme.json";
            file_put_contents($configPath, json_encode($input));
            \includes\ThemeManager::clearCache($tenantId);

            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function handleFileUpload(array $file, string $targetDir): string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new RuntimeException('Invalid file type');
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_'.md5_file($file['tmp_name']).'.'.$ext;
    $targetPath = $targetDir.$filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('File upload failed');
    }

    return $filename;
}
