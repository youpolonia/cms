<?php

namespace CMS\Logging;

class QueryLogger extends Logger
{
    const QUERY = 'query';

    public static function logQuery(
        string $query, 
        array $bindings = [], 
        float $executionTime = 0.0,
        int $affectedRows = 0,
        ?string $connection = null
    ) {
        $context = [
            'query' => $query,
            'bindings' => $bindings,
            'execution_time' => $executionTime,
            'affected_rows' => $affectedRows,
            'connection' => $connection
        ];

        parent::log(self::QUERY, 'Database query executed', $context, 'query');
    }

    public static function logSlowQuery(
        string $query,
        array $bindings = [],
        float $executionTime = 0.0,
        float $threshold = 1.0,
        ?string $connection = null
    ) {
        if ($executionTime >= $threshold) {
            $context = [
                'query' => $query,
                'bindings' => $bindings,
                'execution_time' => $executionTime,
                'threshold' => $threshold,
                'connection' => $connection
            ];

            parent::log(self::WARNING, 'Slow database query detected', $context, 'query');
        }
    }

    public static function logError(
        string $query,
        array $bindings = [],
        string $errorMessage,
        ?string $connection = null
    ) {
        $context = [
            'query' => $query,
            'bindings' => $bindings,
            'error' => $errorMessage,
            'connection' => $connection
        ];

        parent::log(self::ERROR, 'Database query error', $context, 'query');
    }
}
