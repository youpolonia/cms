<?php
require_once __DIR__ . '/../themes/core/themestoragehandler.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $themes = glob(__DIR__ . '/../themes/*', GLOB_ONLYDIR);
            $themeList = array_map('basename', $themes);
            echo json_encode($themeList);
            break;

        case 'load':
            $themeId = $_GET['id'] ?? '';
            if (empty($themeId)) {
                throw new Exception('Theme ID required');
            }
            $themePath = __DIR__ . "/../themes/{$themeId}/theme.json";
            if (!file_exists($themePath)) {
                throw new Exception('Theme not found');
            }
            echo file_get_contents($themePath);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = ThemeStorageHandler::saveThemeVersion(
                $data['id'],
                $data['config'],
                $data['createdBy'] ?? 'admin',
                $data['notes'] ?? ''
            );
            echo json_encode(['success' => $result !== false]);
            break;

        case 'versions':
            $themeId = $_GET['id'] ?? '';
            $versions = ThemeStorageHandler::getThemeVersions($themeId);
            echo json_encode($versions);
            break;

        case 'restore':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = ThemeStorageHandler::restoreThemeVersion(
                $data['themeId'],
                $data['version']
            );
            echo json_encode(['success' => $result !== false]);
            break;

        case 'preview':
            $themeId = $_GET['id'] ?? '';
            $themePath = __DIR__ . "/../themes/{$themeId}/theme.json";
            if (!file_exists($themePath)) {
                throw new Exception('Theme not found');
            }
            $themeData = json_decode(file_get_contents($themePath), true);
            header('Content-Type: text/html');
            echo $themeData['preview_html'] ?? '
<div>Preview not available</div>';
            break;

        case 'presets':
            $presets = glob(__DIR__ . '/../themes/presets/*.json');
            $presetList = array_map(function($path) {
                $data = json_decode(file_get_contents($path), true);
                return [
                    'id' => basename($path, '.json'),
                    'name' => $data['name'] ?? 'Unnamed Preset'
                ];
            }, $presets);
            echo json_encode($presetList);
            break;

        case 'preset':
            $presetId = $_GET['id'] ?? '';
            $presetPath = __DIR__ . "/../themes/presets/{$presetId}.json";
            if (!file_exists($presetPath)) {
                throw new Exception('Preset not found');
            }
            echo file_get_contents($presetPath);
            break;

        case 'save-styles':
            $data = json_decode(file_get_contents('php://input'), true);
            $themePath = __DIR__ . "/../themes/{$data['themeId']}/theme.json";
            $themeData = json_decode(file_get_contents($themePath), true);
            $themeData['css'] = $data['css'];
            file_put_contents($themePath, json_encode($themeData, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
