<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Database\DatabaseConnection;
use CMS\Services\CacheManager;

class QueryAnalyzer {
    private DatabaseConnection $db;
    private CacheManager $cacheManager;
    private array $config;

    public function __construct(
        DatabaseConnection $db,
        CacheManager $cacheManager,
        array $config = []
    ) {
        $this->db = $db;
        $this->cacheManager = $cacheManager;
        $this->config = $config;
    }

    /**
     * Analyze query performance
     */
    public function analyzeQuery(string $query, array $params = []): array {
        $cacheKey = 'query_' . md5($query . serialize($params));
        
        if ($cached = $this->cacheManager->getCachedContent($cacheKey)) {
            return $cached;
        }

        $start = microtime(true);
        $result = $this->db->fetchAll($query, $params);
        $executionTime = microtime(true) - $start;

        $analysis = [
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'result_count' => count($result),
            'query_type' => $this->getQueryType($query),
            'is_select' => stripos($query, 'SELECT') === 0,
            'has_joins' => preg_match('/\bJOIN\b/i', $query),
            'has_subqueries' => preg_match('/\(SELECT/i', $query),
            'has_wildcard' => strpos($query, '*') !== false,
            'has_limit' => preg_match('/\bLIMIT\b/i', $query),
            'suggestions' => []
        ];

        if ($executionTime > ($this->config['slow_query_threshold'] ?? 0.1)) {
            $analysis['suggestions'][] = 'Slow query detected';
            $analysis['suggestions'] = array_merge(
                $analysis['suggestions'],
                $this->getOptimizationSuggestions($query)
            );
        }

        $this->cacheManager->cacheContent($cacheKey, $analysis, $this->config['query_ttl'] ?? 3600);
        
        return $analysis;
    }

    private function getQueryType(string $query): string {
        $query = trim($query);
        $firstWord = strtoupper(strtok($query, ' '));
        
        return match($firstWord) {
            'SELECT' => 'SELECT',
            'INSERT' => 'INSERT',
            'UPDATE' => 'UPDATE',
            'DELETE' => 'DELETE',
            'CREATE' => 'CREATE',
            'ALTER' => 'ALTER',
            'DROP' => 'DROP',
            default => 'OTHER'
        };
    }

    private function getOptimizationSuggestions(string $query): array {
        $suggestions = [];
        
        if (preg_match('/\bSELECT\b.*\bFROM\b.*\bWHERE\b.*\bLIKE\b.*%/i', $query)) {
            $suggestions[] = 'Avoid leading wildcards in LIKE clauses';
        }

        if (preg_match('/\bSELECT\b.*\bFROM\b.*\bORDER BY\b.*\bRAND\(\)/i', $query)) {
            $suggestions[] = 'Avoid ORDER BY RAND() - consider alternative approaches';
        }

        if (preg_match('/\bSELECT\b.*\*\bFROM/i', $query)) {
            $suggestions[] = 'Avoid SELECT * - specify columns explicitly';
        }

        if (preg_match('/\bSELECT\b.*\bGROUP BY\b.*\bHAVING\b/i', $query)) {
            $suggestions[] = 'Consider filtering with WHERE before GROUP BY instead of HAVING';
        }

        if (preg_match('/\bINSERT\b.*\bVALUES\b.*\(.*\).*\(.*\)/i', $query)) {
            $suggestions[] = 'Consider batch inserts for multiple rows';
        }

        return $suggestions;
    }

    /**
     * Log slow queries
     */
    public function logSlowQuery(string $query, float $executionTime, ?string $context = null): bool {
        return $this->db->insert('query_logs', [
            'query' => substr($query, 0, 2000),
            'execution_time' => $executionTime,
            'context' => $context,
            'logged_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get query performance statistics
     */
    public function getQueryStats(?string $timePeriod = '7d'): array {
        $interval = $this->parseTimePeriod($timePeriod);

        return $this->db->fetchAll(
            "SELECT 
                query_type,
                COUNT(*) as total_queries,
                AVG(execution_time) as avg_time,
                MAX(execution_time) as max_time,
                SUM(CASE WHEN execution_time > ? THEN 1 ELSE 0 END) as slow_queries
             FROM query_logs
             WHERE logged_at > DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY query_type
             ORDER BY total_queries DESC",
            [$this->config['slow_query_threshold'] ?? 0.1, $interval]
        );
    }

    private function parseTimePeriod(string $period): int {
        $unit = substr($period, -1);
        $value = (int)substr($period, 0, -1);

        return match($unit) {
            'd' => $value,
            'w' => $value * 7,
            'm' => $value * 30,
            'y' => $value * 365,
            default => 7
        };
    }

    /**
     * Explain query execution plan
     */
    public function explainQuery(string $query): array {
        $explainQuery = "EXPLAIN " . $query;
        return $this->db->fetchAll($explainQuery);
    }
}
