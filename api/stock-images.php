<?php
/**
 * Stock Images API - Pexels Integration
 * Returns free stock images for Media Library
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../admin/models/settingsmodel.php';

// Check if admin is logged in
cms_session_start('admin');
if (empty($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'images' => []]);
    exit;
}

$query = $_GET['q'] ?? '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['images' => []]);
    exit;
}

// Get Pexels API Key from settings
$settingsModel = new SettingsModel();
$pexelsApiKey = $settingsModel->getValue('pexels_api_key') ?? '';

if (empty($pexelsApiKey)) {
    // Return sample images if no API key
    echo json_encode([
        'images' => [
            ['url' => 'https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg', 'preview' => 'https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg?auto=compress&cs=tinysrgb&w=400', 'alt' => 'Office workspace'],
            ['url' => 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg', 'preview' => 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=400', 'alt' => 'Team meeting'],
            ['url' => 'https://images.pexels.com/photos/1714208/pexels-photo-1714208.jpeg', 'preview' => 'https://images.pexels.com/photos/1714208/pexels-photo-1714208.jpeg?auto=compress&cs=tinysrgb&w=400', 'alt' => 'Nature landscape'],
        ],
        'note' => 'Sample images - configure Pexels API key in Settings for full search'
    ]);
    exit;
}

// Call Pexels API
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&per_page=24',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: ' . $pexelsApiKey],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode(['error' => 'Pexels API error', 'images' => []]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['photos']) || !is_array($data['photos'])) {
    echo json_encode(['error' => 'Invalid API response', 'images' => []]);
    exit;
}

$images = [];
foreach ($data['photos'] as $photo) {
    $images[] = [
        'url' => $photo['src']['original'] ?? $photo['src']['large2x'] ?? '',
        'preview' => $photo['src']['medium'] ?? $photo['src']['small'] ?? '',
        'alt' => $photo['alt'] ?? 'Stock image',
        'photographer' => $photo['photographer'] ?? ''
    ];
}

echo json_encode(['images' => $images]);
