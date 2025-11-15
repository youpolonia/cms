<?php
/**
 * Base Migration Template
 *
 * This template provides the standard structure for all migrations.
 * Extend this class and implement your specific migration logic.
 */

use Includes\Validation\ValidationHelper;
use Includes\Validation\ValidationException;

abstract class BaseMigration {
    /**
     * Apply the migration
     * 
     * @param \PDO $pdo Database connection
     * @return bool Success status
     */
    public static function migrate(\PDO $pdo, array $input = []): bool {
        try {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            error_log("Migration: Starting migration");
            
            // Validate input if provided
            if (!empty($input)) {
                static::validateInput($input);
            }
            
            // Implement your migration logic here
            
            return true;
        } catch (\PDOException $e) {
            error_log("Migration failed: " . $e->getMessage());
            
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
                error_log("Rolled back transaction due to error");
            }
            
            return false;
        }
    }
    
    /**
     * Reverse the migration
     * 
     * @param \PDO $pdo Database connection
     * @return bool Success status
     */
    public static function rollback(\PDO $pdo, array $input = []): bool {
        try {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            error_log("Rollback: Starting rollback");
            
            // Validate input if provided
            if (!empty($input)) {
                static::validateInput($input);
            }
            
            // Implement your rollback logic here
            
            return true;
        } catch (\PDOException $e) {
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Test the migration by applying and then rolling back
     * 
     * @param \PDO $pdo Database connection
     * @return bool Success status
     */
    public static function test(\PDO $pdo): bool {
        try {
            error_log("Test: Starting migration test");
            
            if (!static::migrate($pdo)) {
                error_log("Test failed: Migration failed");
                return false;
            }
            
            error_log("Test: Migration successful, testing rollback");
            
            if (!static::rollback($pdo)) {
                error_log("Test failed: Rollback failed");
                return false;
            }
            
            error_log("Test completed successfully");
            return true;
        } catch (\PDOException $e) {
            error_log("Test failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate input data against migration rules
     *
     * @param array $input Input data to validate
     * @throws ValidationException On validation failure
     */
    protected static function validateInput(array $input): void
    {
        // Example validation rules - override in child classes
        $rules = [
            // 'field_name' => ['required', 'string', 'max:255']
        ];
        
        if (!empty($rules)) {
            ValidationHelper::validateOrFail($input, $rules);
        }
    }
}
