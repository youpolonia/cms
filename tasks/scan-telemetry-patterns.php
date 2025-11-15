<?php
// Scheduled task to scan for new telemetry error patterns
require_once __DIR__ . '/../includes/system/TelemetryPatternAnalyzer.php';
require_once __DIR__ . '/../includes/system/NotificationManager.php';

define('KNOWN_PATTERNS_FILE', __DIR__ . '/../logs/known-error-patterns.json');
define('HOURS_TO_ANALYZE', 6);
define('MIN_OCCURRENCES', 3);

// Load known patterns
$knownPatterns = [];
if (file_exists(KNOWN_PATTERNS_FILE)) {
    $knownPatterns = json_decode(file_get_contents(KNOWN_PATTERNS_FILE), true) ?: [];
}

// Analyze recent patterns
$patterns = TelemetryPatternAnalyzer::analyzeRecentPatterns(HOURS_TO_ANALYZE, MIN_OCCURRENCES);

// Process found patterns
$newPatternsFound = false;
foreach ($patterns as $pattern) {
    // Create fingerprint from normalized message
    $fingerprint = md5($pattern['example_message']);
    
    if (!isset($knownPatterns[$fingerprint])) {
        // New pattern found
        $knownPatterns[$fingerprint] = [
            'first_seen' => date('c'),
            'message' => $pattern['example_message'],
            'modules' => $pattern['common_context']['module'] ?? []
        ];
        
        // Send notification
        NotificationManager::send(
            'warning',
            'telemetry-patterns',
            'New repeating error detected: ' . substr($pattern['example_message'], 0, 80) . '...',
            [
                'count' => $pattern['count'],
                'modules' => $pattern['common_context']['module'] ?? []
            ]
        );
        
        $newPatternsFound = true;
    }
}

// Save updated known patterns if new ones found
if ($newPatternsFound) {
    file_put_contents(
        KNOWN_PATTERNS_FILE, 
        json_encode($knownPatterns, JSON_PRETTY_PRINT)
    );
}

// Create empty file if doesn't exist
if (!file_exists(KNOWN_PATTERNS_FILE) && !empty($knownPatterns)) {
    file_put_contents(
        KNOWN_PATTERNS_FILE, 
        json_encode([], JSON_PRETTY_PRINT)
    );
}
