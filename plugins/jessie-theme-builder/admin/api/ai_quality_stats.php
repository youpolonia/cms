<?php
/**
 * JTB AI Quality Stats API
 * GET-only endpoint returning JSON statistics for AI layout generation quality
 *
 * @package JessieThemeBuilder
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(dirname(dirname(__DIR__)))));
}

require_once CMS_ROOT . '/config.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once CMS_ROOT . '/admin/includes/auth.php';

// Check admin authentication
if (!AdminAuth::isAuthenticated()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// Method check - GET only
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Access denied. DEV_MODE required.']);
    exit;
}

// Parameters
$limit = min(max((int)($_GET['limit'] ?? 500), 1), 5000);

// Initialize response structure
$response = [
    'ok' => true,
    'total_entries' => 0,
    'avg_score' => 0,
    'status_counts' => [
        'REJECT' => 0,
        'ACCEPTABLE' => 0,
        'GOOD' => 0,
        'EXCELLENT' => 0
    ],
    'attempt_counts' => [
        1 => 0,
        2 => 0,
        3 => 0
    ],
    'forced_accept_count' => 0,
    'top_violations' => [],
    'top_warnings' => []
];

// Read log file
$logFile = CMS_ROOT . '/logs/ai-quality.log';

if (!file_exists($logFile) || !is_readable($logFile)) {
    echo json_encode($response);
    exit;
}

// Efficient tail-like reading for large files
$entries = [];
$fileSize = filesize($logFile);

if ($fileSize > 0) {
    // For small files, just read all
    if ($fileSize < 1024 * 1024) { // < 1MB
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false) {
            $lines = array_slice($lines, -$limit);
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if ($entry !== null) {
                    $entries[] = $entry;
                }
            }
        }
    } else {
        // For larger files, read from end
        $handle = fopen($logFile, 'r');
        if ($handle) {
            $buffer = '';
            $chunkSize = 8192;
            $position = $fileSize;
            $linesFound = [];

            while ($position > 0 && count($linesFound) < $limit) {
                $readSize = min($chunkSize, $position);
                $position -= $readSize;
                fseek($handle, $position);
                $buffer = fread($handle, $readSize) . $buffer;

                $lines = explode("\n", $buffer);
                $buffer = array_shift($lines); // Keep incomplete line

                foreach (array_reverse($lines) as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $entry = json_decode($line, true);
                    if ($entry !== null) {
                        array_unshift($linesFound, $entry);
                        if (count($linesFound) >= $limit) break;
                    }
                }
            }

            // Process remaining buffer
            if (!empty($buffer) && count($linesFound) < $limit) {
                $entry = json_decode(trim($buffer), true);
                if ($entry !== null) {
                    array_unshift($linesFound, $entry);
                }
            }

            fclose($handle);
            $entries = array_slice($linesFound, -$limit);
        }
    }
}

// Calculate statistics
$totalEntries = count($entries);
$sumScore = 0;
$violationCounts = [];
$warningCounts = [];

foreach ($entries as $entry) {
    $sumScore += $entry['score'] ?? 0;

    $status = $entry['status'] ?? 'REJECT';
    if (isset($response['status_counts'][$status])) {
        $response['status_counts'][$status]++;
    }

    $attempt = $entry['attempt'] ?? 1;
    if (isset($response['attempt_counts'][$attempt])) {
        $response['attempt_counts'][$attempt]++;
    }

    if (!empty($entry['forced_accept'])) {
        $response['forced_accept_count']++;
    }

    foreach ($entry['violations'] ?? [] as $v) {
        $code = trim(explode(':', $v)[0]);
        $violationCounts[$code] = ($violationCounts[$code] ?? 0) + 1;
    }

    foreach ($entry['warnings'] ?? [] as $w) {
        $code = trim(explode(':', $w)[0]);
        $warningCounts[$code] = ($warningCounts[$code] ?? 0) + 1;
    }
}

$response['total_entries'] = $totalEntries;
$response['avg_score'] = $totalEntries > 0 ? round($sumScore / $totalEntries, 2) : 0;

// Build top violations
arsort($violationCounts);
$topV = [];
$i = 0;
foreach ($violationCounts as $code => $count) {
    if ($i >= 10) break;
    $topV[] = ['code' => $code, 'count' => $count];
    $i++;
}
$response['top_violations'] = $topV;

// Build top warnings
arsort($warningCounts);
$topW = [];
$i = 0;
foreach ($warningCounts as $code => $count) {
    if ($i >= 10) break;
    $topW[] = ['code' => $code, 'count' => $count];
    $i++;
}
$response['top_warnings'] = $topW;

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
