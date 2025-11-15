<?php
/**
 * Task Validator - enforces project rules and prevents banned content
 */
class TaskValidator {
    public static function validateTask($mode) {
        // Validate memory bank files exist
        if (!self::validateMemoryBank()) {
            error_log("Memory bank validation failed");
            return false;
        }

        // Block any Laravel-related patterns
        if (self::containsLaravelPatterns($mode)) {
            error_log("Prohibited content detected: Laravel patterns are banned");
            return false;
        }

        return true;
    }

    private static function validateMemoryBank() {
        $requiredFiles = [
            __DIR__.'/../../memory-bank/db_migration_rules.md',
            __DIR__.'/../../memory-bank/progress.md',
            __DIR__.'/../../memory-bank/decisionLog.md',
            __DIR__.'/../../memory-bank/phase3_plan.md'
        ];

        foreach ($requiredFiles as $file) {
            if (!file_exists($file)) {
                return false;
            }
        }
        return true;
    }

    private static function containsLaravelPatterns($content) {
        $bannedPatterns = [
            '/laravel/i',
            '/up\(\)/',
            '/down\(\)/',
            '/Schema::/',
            '/artisan/i'
        ];

        foreach ($bannedPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        return false;
    }
}
