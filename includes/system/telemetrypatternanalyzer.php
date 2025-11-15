<?php
class TelemetryPatternAnalyzer {
    const MAX_LINES = 2000;
    const MIN_HOURS = 1;
    const MAX_HOURS = 168; // 1 week
    const MIN_OCCURRENCES = 1;
    const MAX_OCCURRENCES = 1000;

    public static function analyzeRecentPatterns(int $hours = 6, int $minOccurrences = 3): array {
        // Validate input parameters
        $hours = max(self::MIN_HOURS, min($hours, self::MAX_HOURS));
        $minOccurrences = max(self::MIN_OCCURRENCES, min($minOccurrences, self::MAX_OCCURRENCES));

        $logPath = __DIR__ . '/../../logs/telemetry.log';
        if (!file_exists($logPath) || !is_readable($logPath)) {
            return [];
        }

        $cutoff = time() - ($hours * 3600);
        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_slice($lines, -self::MAX_LINES); // Only process recent lines

        $patterns = [];
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry || !isset($entry['timestamp']) || !isset($entry['message'])) {
                continue;
            }

            $entryTime = strtotime($entry['timestamp']);
            if ($entryTime < $cutoff) {
                continue;
            }

            $fingerprint = self::createFingerprint($entry['message']);
            if (!isset($patterns[$fingerprint])) {
                $patterns[$fingerprint] = [
                    'count' => 0,
                    'first_occurrence' => $entry['timestamp'],
                    'last_occurrence' => $entry['timestamp'],
                    'example_message' => $entry['message'],
                    'context_fields' => [],
                    'types' => []
                ];
            }

            $patterns[$fingerprint]['count']++;
            $patterns[$fingerprint]['last_occurrence'] = $entry['timestamp'];
            
            // Track types
            if (isset($entry['type'])) {
                $patterns[$fingerprint]['types'][$entry['type']] = 
                    ($patterns[$fingerprint]['types'][$entry['type']] ?? 0) + 1;
            }

            // Extract common context fields
            if (isset($entry['context']) && is_array($entry['context'])) {
                foreach ($entry['context'] as $key => $value) {
                    if (!isset($patterns[$fingerprint]['context_fields'][$key])) {
                        $patterns[$fingerprint]['context_fields'][$key] = [];
                    }
                    $valueKey = is_scalar($value) ? $value : json_encode($value);
                    $patterns[$fingerprint]['context_fields'][$key][$valueKey] = 
                        ($patterns[$fingerprint]['context_fields'][$key][$valueKey] ?? 0) + 1;
                }
            }
        }

        // Filter by minimum occurrences
        $patterns = array_filter($patterns, function($pattern) use ($minOccurrences) {
            return $pattern['count'] >= $minOccurrences;
        });

        // Sort by count descending
        uasort($patterns, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return $patterns;
    }

    public static function generateReport(int $hours = 6, int $minOccurrences = 3): array {
        // Log report generation attempt
        self::logAccess('report_generation', [
            'hours' => $hours,
            'min_occurrences' => $minOccurrences,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        $patterns = self::analyzeRecentPatterns($hours, $minOccurrences);
        $report = [];

        foreach ($patterns as $fingerprint => $data) {
            $report[] = [
                'fingerprint' => $fingerprint,
                'count' => $data['count'],
                'example_message' => $data['example_message'],
                'time_range' => [
                    'first' => $data['first_occurrence'],
                    'last' => $data['last_occurrence']
                ],
                'common_types' => array_keys($data['types']),
                'common_context' => array_map(function($field) {
                    arsort($field);
                    return array_slice(array_keys($field), 0, 3);
                }, $data['context_fields'])
            ];
        }

        return $report;
    }

    private static function createFingerprint(string $message): string {
        // Normalize message by removing dynamic parts
        $normalized = preg_replace([
            '/\d+/',              // Numbers
            '/\[.*?\]/',          // Anything in brackets
            '/\b(line|file):.*?\b/i', // Line/file references
            '/\b[a-f0-9]{8,}\b/i' // Hex IDs
        ], '[DYN]', $message);

        return md5($normalized);
    }

    private static function logAccess(string $action, array $context = []): void {
        $logPath = __DIR__ . '/../../logs/telemetry_access.log';
        $entry = [
            'timestamp' => date('c'),
            'action' => $action,
            'context' => $context
        ];
        file_put_contents($logPath, json_encode($entry) . PHP_EOL, FILE_APPEND);
    }
}
