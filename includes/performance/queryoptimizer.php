<?php
declare(strict_types=1);

class QueryOptimizer {
    public static function analyze(string $query): array {
        return [
            'query' => $query,
            'analysis' => self::performAnalysis($query),
            'suggestions' => self::generateSuggestions($query)
        ];
    }

    private static function performAnalysis(string $query): array {
        return [
            'join_count' => substr_count(strtoupper($query), 'JOIN'),
            'subquery_count' => substr_count($query, '(') - substr_count($query, ')'),
            'full_scan_risk' => str_contains($query, 'WHERE 1=1') || !str_contains($query, 'WHERE')
        ];
    }

    private static function generateSuggestions(string $query): array {
        $suggestions = [];
        
        if (str_contains($query, 'SELECT *')) {
            $suggestions[] = 'Replace SELECT * with specific columns';
        }

        if (substr_count(strtoupper($query), 'JOIN') > 3) {
            $suggestions[] = 'Consider denormalizing data or using views';
        }

        return $suggestions;
    }
}
