<?php
/**
 * Database Migration Runner
 * 
 * Executes pending database migrations in a shared hosting environment
 */

class DatabaseMigrationRunner {
    private $migrationsDir = [
        __DIR__ . '/../../database/migrations/',
        __DIR__ . '/../../database/migrations/'
    ];
    private $completedMigrationsFile = __DIR__ . '/../../storage/migrations/completed.json';
    
    public function __construct() {
        $this->ensureStorageDirectory();
    }
    
    private function ensureStorageDirectory() {
        $dir = dirname($this->completedMigrationsFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    public function run() {
        $completed = $this->loadCompletedMigrations();
        $pending = $this->findPendingMigrations($completed);
        
        if (empty($pending)) {
            echo "No pending migrations found.\n";
            return;
        }
        
        foreach ($pending as $migration) {
            try {
                $this->executeMigration($migration);
                $this->markMigrationComplete($migration, $completed);
                echo "Successfully executed migration: " . basename($migration) . "\n";
            } catch (Exception $e) {
                echo "Failed to execute migration: " . basename($migration) . "\n";
                echo "Error: " . $e->getMessage() . "\n";
                break;
            }
        }
    }
    
    private function loadCompletedMigrations() {
        if (!file_exists($this->completedMigrationsFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->completedMigrationsFile), true);
    }
    
    private function findPendingMigrations($completed) {
        $pending = [];
        foreach ($this->migrationsDir as $dir) {
            if (!file_exists($dir)) continue;
            
            $files = scandir($dir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
                if (in_array($file, $completed)) continue;
                
                $pending[] = $dir . $file;
            }
        }
        usort($pending, function($a, $b) {
            return strcmp(basename($a), basename($b));
        });
        return $pending;
    }
    
    private function executeMigration($path) {
        require_once $path;
        $className = $this->getMigrationClassName($path);
        
        if (!class_exists($className)) {
            throw new Exception("Migration class $className not found");
        }
        
        $migration = new $className();
        $migration->migrate();
    }
    
    private function getMigrationClassName($path) {
        $filename = basename($path, '.php');
        $parts = explode('_', $filename);
        array_shift($parts); // Remove timestamp
        $className = implode('', array_map('ucfirst', $parts));
        return $className;
    }
    
    private function markMigrationComplete($path, &$completed) {
        $completed[] = basename($path);
        file_put_contents($this->completedMigrationsFile, json_encode($completed, JSON_PRETTY_PRINT));
    }
}

// Execute migrations if run directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $runner = new DatabaseMigrationRunner();
    $runner->run();
}
