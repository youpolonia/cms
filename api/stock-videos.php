<?php
/**
 * Stock Videos API - Pexels Integration
 * Returns free stock videos for Theme Builder
 * 
 * API Key is stored in Settings: Admin > Settings > integrations > pexels_api_key
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../admin/models/settingsmodel.php';

// Check if admin is logged in
cms_session_start('admin');
if (empty($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$query = $_GET['q'] ?? '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['videos' => []]);
    exit;
}

// Get Pexels API Key from settings
$settingsModel = new SettingsModel();
$pexelsApiKey = $settingsModel->getValue('pexels_api_key') ?? '';

// If no API key, return sample videos with info
if (empty($pexelsApiKey)) {
    // Return curated sample videos based on common queries
    $sampleVideos = [
        'nature' => [
            ['url' => 'https://player.vimeo.com/external/370331493.sd.mp4?s=e90dcaba73c19e0e36f03406b47bbd6992dd6c1c&profile_id=139&oauth2_token_id=57447761', 'preview' => 'https://images.pexels.com/videos/3571264/free-video-3571264.jpg?auto=compress&cs=tinysrgb&dpr=1&w=500', 'duration' => '0:15'],
            ['url' => 'https://player.vimeo.com/external/371834259.sd.mp4?s=dc47c86abc3fded85be27b9a56f6fa2cb32de1c4&profile_id=139&oauth2_token_id=57447761', 'preview' => 'https://images.pexels.com/videos/3629519/free-video-3629519.jpg?auto=compress&cs=tinysrgb&dpr=1&w=500', 'duration' => '0:20'],
        ],
        'business' => [
            ['url' => 'https://player.vimeo.com/external/371433846.sd.mp4?s=236da2f3c0fd273d2c6d9a064f3ae35579b2bbdf&profile_id=139&oauth2_token_id=57447761', 'preview' => 'https://images.pexels.com/videos/3209828/free-video-3209828.jpg?auto=compress&cs=tinysrgb&dpr=1&w=500', 'duration' => '0:12'],
        ],
        'technology' => [
            ['url' => 'https://player.vimeo.com/external/434045526.sd.mp4?s=c27eecc69a27dbc4ff2b87d38afc35f1a9e7c02d&profile_id=139&oauth2_token_id=57447761', 'preview' => 'https://images.pexels.com/videos/5752729/pexels-photo-5752729.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500', 'duration' => '0:18'],
        ],
        'abstract' => [
            ['url' => 'https://player.vimeo.com/external/403295268.sd.mp4?s=67de71d7a454ec04123e0c38051a47e85a6e6d50&profile_id=139&oauth2_token_id=57447761', 'preview' => 'https://images.pexels.com/videos/4763824/pexels-photo-4763824.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500', 'duration' => '0:10'],
        ],
        'ocean' => [
            ['url' => 'https://player.vimeo.com/external/368484050.sd.mp4?s=0ce60c77096631e4c11f6d019e0c06e3da0dc90e&profile_id=139&oauth2_token_id=57447761', 'preview' => 'https://images.pexels.com/videos/1093662/free-video-1093662.jpg?auto=compress&cs=tinysrgb&dpr=1&w=500', 'duration' => '0:22'],
        ],
        'city' => [
            ['url' => 'https://player.vimeo.com/external/403296910.sd.mp4?s=2f9e6f1b81f7d3f8d8f0e1e5e2a3b4c5d6e7f8a9&profile_id=139&oauth2_token_id=57447761', 'preview' => 'https://images.pexels.com/videos/4328404/pexels-photo-4328404.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500', 'duration' => '0:14'],
        ],
    ];
    
    // Find matching category
    $queryLower = strtolower($query);
    $results = [];
    
    foreach ($sampleVideos as $category => $videos) {
        if (strpos($queryLower, $category) !== false || strpos($category, $queryLower) !== false) {
            $results = array_merge($results, $videos);
        }
    }
    
    // If no match, return all samples
    if (empty($results)) {
        foreach ($sampleVideos as $videos) {
            $results = array_merge($results, $videos);
        }
    }
    
    echo json_encode([
        'videos' => $results,
        'note' => 'Demo videos. Add Pexels API key in Admin > Settings > Integrations for full library.',
        'needsApiKey' => true
    ]);
    exit;
}

// With API key - fetch from Pexels
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.pexels.com/videos/search?query=' . urlencode($query) . '&per_page=12&size=medium',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $pexelsApiKey
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo json_encode(['error' => 'API request failed (code: ' . $httpCode . ')', 'videos' => []]);
    exit;
}

$data = json_decode($response, true);
$videos = [];

if (!empty($data['videos'])) {
    foreach ($data['videos'] as $video) {
        // Get the best quality video file
        $videoFile = null;
        foreach ($video['video_files'] as $file) {
            if ($file['quality'] === 'sd' || $file['quality'] === 'hd') {
                $videoFile = $file;
                break;
            }
        }
        
        if ($videoFile) {
            $videos[] = [
                'url' => $videoFile['link'],
                'preview' => $video['image'],
                'duration' => gmdate('i:s', $video['duration'])
            ];
        }
    }
}

echo json_encode(['videos' => $videos]);
