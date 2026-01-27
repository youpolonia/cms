<?php
require_once __DIR__ . '/themebuilder.php';

$themeName = $_GET['theme'] ?? '';
if (empty($themeName)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Theme parameter required']);
    exit;
}

$builder = new ThemeBuilder($themeName);
header('Content-Type: application/json');
echo json_encode(['locked' => $builder->isLocked()]);
