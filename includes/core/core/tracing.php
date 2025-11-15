<?php
namespace Core;

class Tracing {
    /**
     * Minimal tracing implementation for QueryDashboard
     * Provides basic OpenTelemetry-like interface
     */
    public static function startSpan(string $name): array {
        return [
            'name' => $name,
            'start' => microtime(true),
            'attributes' => []
        ];
    }

    public static function endSpan(array $span): array {
        return array_merge($span, [
            'end' => microtime(true),
            'duration' => microtime(true) - $span['start']
        ]);
    }

    public static function recordException(\Throwable $e): void {
        // Basic exception recording
        error_log("Tracing exception: " . $e->getMessage());
    }
}
