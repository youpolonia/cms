<?php

class Phase11_Deployment_Validator {
    const MIGRATION_PATTERN = '/^Migration_\d{4}_[a-zA-Z0-9_]+$/';
    
    public static function validateMigrationFiles() {
        $errors = [];
        $migrationDir = 'database/migrations/';
        
        foreach (glob($migrationDir . '*.php') as $file) {
            $filename = basename($file);
            $className = str_replace('.php', '', $filename);
            
            // Check filename pattern
            if (!preg_match(self::MIGRATION_PATTERN, $className)) {
                $errors[] = "Invalid migration filename: $filename";
                continue;
            }
            
            // Check class structure
            require_once $file;
            if (!class_exists($className)) {
                $errors[] = "Class $className not found in $filename";
                continue;
            }
            
            // Check required methods
            $requiredMethods = ['execute', 'rollback'];
            foreach ($requiredMethods as $method) {
                if (!method_exists($className, $method)) {
                    $errors[] = "Missing required method $method in $className";
                }
            }
            
            // Check for framework patterns
            $content = file_get_contents($file);
            $forbiddenPatterns = [
                '/use Illuminate/',
                '/extends Migration/',
                '/Schema::/',
                '/DB::/'
            ];
            
            foreach ($forbiddenPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $errors[] = "Framework pattern detected in $filename: $pattern";
                }
            }
        }
        
        if (empty($errors)) {
            echo "All migration files validated successfully.\n";
            return true;
        }
        
        echo "Validation errors found:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
        return false;
    }
    
    public static function validateApiEndpoints() {
        // Will be implemented after API documentation generation
        return true;
    }
}

// Execute validation
Phase11_Deployment_Validator::validateMigrationFiles();
