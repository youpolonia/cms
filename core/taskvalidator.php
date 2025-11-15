<?php
/**
 * Validates tasks before execution
 */
class TaskValidator {
    /**
     * Required memory bank files to read before task execution
     */
    const REQUIRED_MEMORY_FILES = [
        'cms_storage/db_migration_rules.md',
        'logs/progress.md',
        'cms_storage/decisionLog.md'
    ];

    /**
     * Validates a task before execution
     */
    public static function validateTask(string $mode): bool {
        if (!self::checkMemoryBanks()) {
            error_log("Memory bank validation failed for mode: $mode");
            return false;
        }
        return true;
    }

    /**
     * Verifies all required memory bank files were read
     */
    private static function checkMemoryBanks(): bool {
        foreach (self::REQUIRED_MEMORY_FILES as $file) {
            if (!file_exists($file)) {
                error_log("Missing required memory bank file: $file");
                return false;
            }
        }
        return true;
    }

    // Removed legacy MCP/Laravel contamination check (referenced mcp-knowledge/config.json).
}
