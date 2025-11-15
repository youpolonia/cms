<?php
declare(strict_types=1);

class Migrator {
    /**
     * Get list of pending migrations
     * @return array<string> List of migration filenames without .php extension
     */
    public function getPendingMigrations(): array {
        return $this->getPendingMigrationFiles();
    }

    /**
     * Get list of completed migrations with batch info
     * @return array
<array{migration: string, batch: int, created_at: string}>
     */
    public
 function getCompletedMigrations(): array {
        $stmt = $this->pdo->query("SELECT migration, batch, created_at FROM {$this->migrationsTable} ORDER BY batch, id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPendingMigrationFiles(): array {
        $allFiles = [];
        $directoryIterator = new RecursiveDirectoryIterator($this->migrationsDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile() && in_array($fileInfo->getExtension(), ['php', 'sql'])) {
                $subPathName = $iterator->getSubPathname(); // e.g., "2025_05_10_152000_create_remember_tokens_table.php" or "phase1/0001_create_workers_table.php"
                // Remove .php or .sql extension
                $migrationName = preg_replace('/\.(php|sql)$/i', '', $subPathName);

                if (!$this->isMigrated($migrationName)) {
                    // Store the migration name (relative path without extension) as the key
                    $allFiles[$migrationName] = $fileInfo->getRealPath(); // Value is full path, though not strictly used by current runMigration
                }
            }
        }
        
        // Custom sort to ensure chronological order based on YYYY_MM_DD_HHMMSS prefix
        uksort($allFiles, function ($keyA, $keyB) {
            // Get the filename part for timestamp extraction
            $basenameA = basename($keyA);
            $basenameB = basename($keyB);

            preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})/', $basenameA, $matchesA);
            $timestampA = $matchesA[1] ?? null;

            preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})/', $basenameB, $matchesB);
            $timestampB = $matchesB[1] ?? null;

            // If both have standard timestamps, compare them
            if ($timestampA && $timestampB) {
                if ($timestampA === $timestampB) {
                    // If timestamps are identical, compare the full original keys
                    return strnatcmp($keyA, $keyB);
                }
                return strcmp($timestampA, $timestampB);
            }
            // Fallback for names that might not strictly follow the YYYY_MM_DD_HHMMSS_ prefix,
            // or if one has a timestamp and the other doesn't.
            // This will prioritize timestamped migrations earlier if one is missing it.
            if ($timestampA && !$timestampB) return -1; // A comes first
            if (!$timestampA && $timestampB) return 1;  // B comes first
            
            // If neither has a recognizable timestamp prefix in basename, natural sort full keys
            return strnatcmp($keyA, $keyB);
        });

        return array_keys($allFiles); // Return the sorted migration names (keys)
    }

    private PDO $pdo;
    private string $migrationsDir;
    private string $migrationsTable = 'migrations';

    public function __construct(PDO $pdo, string $migrationsDir) {
        $this->pdo = $pdo;
        $this->migrationsDir = rtrim($migrationsDir, '/') . '/';
        $this->ensureMigrationsTableExists();
    }

    private function ensureMigrationsTableExists(): void {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_migration (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        /* exec disabled: was $this->pdo->exec($sql) */
    }

    public function migrate(): void {
        $this->pdo->beginTransaction();
        try {
            $batch = $this->getNextBatchNumber();
            $migrations = $this->getPendingMigrations();

            foreach ($migrations as $migration) {
                $this->runMigration($migration, $batch);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $originalException = $e;
            try {
                // Only attempt rollback if a transaction is actually active
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
            } catch (PDOException $rollbackException) {
                // Log the rollback failure, but prioritize the original exception for reporting
                error_log("Rollback failed during migration: " . $rollbackException->getMessage() . ". Original error: " . $originalException->getMessage());
                // It's more informative to throw the original exception that caused the migration to fail
            }
            throw $originalException; // Re-throw the original exception
        }
    }

    public function rollback(int $steps = 1): void {
        $this->pdo->beginTransaction();
        try {
            $batches = $this->getBatchesToRollback($steps);
            
            foreach ($batches as $batch) {
                $migrations = $this->getMigrationsForBatch($batch);
                
                foreach ($migrations as $migration) {
                    $this->runRollback($migration);
                }
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function getNextBatchNumber(): int {
        $stmt = $this->pdo->query("SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}");
        $result = $stmt->fetch();
        return ($result['max_batch'] ?? 0) + 1;
    }


    private function isMigrated(string $migration): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->migrationsTable} WHERE migration = ?");
        $stmt->execute([$migration]);
        return (bool)$stmt->fetch();
    }

    private function runMigration(string $migrationNameParam, int $batch): void {
        $originalMigrationName = $migrationNameParam;
        $fileExtension = pathinfo($originalMigrationName, PATHINFO_EXTENSION);
        // The $originalMigrationName already includes subdirectories if any, but not the .php or .sql yet
        // The getPendingMigrationFiles now returns names like 'phase1/0001_create_workers_table'
        // So, we need to append the correct extension.
        // However, the $filePath was constructed using $originalMigrationName . '.php'
        // This needs to be dynamic based on the actual file found.
        // Let's reconstruct the full path properly.
        // The $originalMigrationName is now the relative path without extension.
        // We need to find the actual file that corresponds to this name.
        
        $possiblePhpPath = $this->migrationsDir . $originalMigrationName . '.php';
        $possibleSqlPath = $this->migrationsDir . $originalMigrationName . '.sql';
        
        $filePath = null;
        $actualFileExtension = null;

        if (file_exists($possiblePhpPath)) {
            $filePath = $possiblePhpPath;
            $actualFileExtension = 'php';
        } elseif (file_exists($possibleSqlPath)) {
            $filePath = $possibleSqlPath;
            $actualFileExtension = 'sql';
        }

        if (!$filePath) {
            echo "Error: Migration file for '$originalMigrationName' not found at expected paths.\n";
            throw new Exception("Migration file for '$originalMigrationName' not found.");
        }

        $migrationExecuted = false;

        if ($actualFileExtension === 'sql') {
            $sqlContent = file_get_contents($filePath);
            if ($sqlContent === false) {
                throw new Exception("Failed to read SQL migration file: $filePath");
            }
            /* exec disabled: was $this->pdo->exec($sqlContent) */
            echo "Executed SQL migration file: $originalMigrationName.sql\n";
            $migrationExecuted = true;
        } elseif ($actualFileExtension === 'php') {
            // Execute the PHP script. It might return a value, define a class, or run SQL directly.
            $scriptReturnValue = require_once $filePath;

            if (is_array($scriptReturnValue) && isset($scriptReturnValue['up']) && is_string($scriptReturnValue['up'])) {
                // Type C: Script returned an array with SQL
                /* exec disabled: was $this->pdo->exec($scriptReturnValue['up']) */
                echo "Executed SQL from array in PHP migration file: $originalMigrationName.php\n";
                $migrationExecuted = true;
            } else {
                // Not type C. Try Type A (class-based). Type B (direct execution) would have run during require_once.
                $className = $this->getMigrationClassName($originalMigrationName);
                if (class_exists($className, false)) { // Set autoload to false as we've already required the file
                    // Pass PDO and migration name to the constructor
                    $instance = new $className($this->pdo, $originalMigrationName);
                    if (method_exists($instance, 'setUseTransaction')) {
                        $instance->setUseTransaction(false); // Prevent individual migration from starting its own transaction
                    }
                    if (method_exists($instance, 'migrate')) {
                        // The 'up' method in the base Migration class handles transactions and calls 'apply'
                        // So we call 'up()' directly. The PDO instance is already passed to constructor.
                        $instance->migrate();
                        $migrationExecuted = true;
                    } else {
                        echo "Warning: Migration class $className found for $originalMigrationName.php but has no 'up' method.\n";
                    }
                } else {
                     // Check if $scriptReturnValue is an object with an up() method (anonymous class case)
                     if (is_object($scriptReturnValue) && method_exists($scriptReturnValue, 'up')) {
                        // This is an anonymous class with an up() method
                        echo "Executing anonymous class migration: $originalMigrationName.php\n";
                        $scriptReturnValue->up($this->pdo);
                        $migrationExecuted = true;
                     } elseif ($scriptReturnValue !== false && $scriptReturnValue !== 1 && !is_array($scriptReturnValue)) {
                        // If require_once returned something other than true (1) or an array, it might be an issue
                        // or a script that echoed something and then exited.
                        // For Type B (direct execution PHP scripts), they should manage their own output.
                        // We assume success if no class was found and no array returned, and require_once didn't fail.
                        echo "Executed direct PHP migration script: $originalMigrationName.php\n";
                        $migrationExecuted = true;
                     } elseif ($scriptReturnValue === 1 || $scriptReturnValue === true) {
                        // Standard return for require_once of a file that doesn't explicitly return a value
                        // but might have executed code (Type B).
                        echo "Executed direct PHP migration script (assumed): $originalMigrationName.php\n";
                        $migrationExecuted = true;
                     }
                }
            }
        }

        if ($migrationExecuted) {
            $stmt = $this->pdo->prepare("INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)");
            // Log with the name without extension, as this is what getPendingMigrationFiles provides
            $stmt->execute([$originalMigrationName, $batch]);
        } else {
            echo "Warning: Migration $originalMigrationName was not executed by known patterns and not logged.\n";
        }
    }

    private function runRollback(string $migrationNameParam): void {
        $originalMigrationName = $migrationNameParam;
        
        $possiblePhpPath = $this->migrationsDir . $originalMigrationName . '.php';
        $possibleSqlPath = $this->migrationsDir . $originalMigrationName . '.sql';
        
        $filePath = null;
        $actualFileExtension = null;

        if (file_exists($possiblePhpPath)) {
            $filePath = $possiblePhpPath;
            $actualFileExtension = 'php';
        } elseif (file_exists($possibleSqlPath)) {
            $filePath = $possibleSqlPath;
            $actualFileExtension = 'sql';
        }

        if (!$filePath) {
            echo "Error: Migration file for rollback '$originalMigrationName' not found.\n";
            return; // Cannot rollback if file not found
        }

        if ($actualFileExtension === 'sql') {
            // SQL files typically don't have a "down" method.
            // Log or decide on a strategy for SQL rollbacks if necessary.
            echo "Notice: Rollback for SQL migration '$originalMigrationName.sql' is not automatically supported by executing 'down'. Manual rollback may be required.\n";
        } elseif ($actualFileExtension === 'php') {
            $scriptReturnValue = require_once $filePath; // Ensure class is loaded if defined

            if (is_array($scriptReturnValue) && isset($scriptReturnValue['down']) && is_string($scriptReturnValue['down'])) {
                /* exec disabled: was $this->pdo->exec($scriptReturnValue['down']) */
                echo "Executed SQL (down) from array in PHP migration file: $originalMigrationName.php\n";
            } else {
                $className = $this->getMigrationClassName($originalMigrationName);
                if (class_exists($className, false)) {
                    $instance = new $className();
                    if (method_exists($instance, 'rollback')) {
                        $instance->rollback($this->pdo);
                    } else {
                        echo "Warning: Migration class $className found for $originalMigrationName.php but has no 'down' method for rollback.\n";
                    }
                } else {
                    echo "Warning: Cannot rollback $originalMigrationName.php. No class $className found and not an array-returning script with 'down' SQL.\n";
                }
            }
        }

        $stmt = $this->pdo->prepare("DELETE FROM {$this->migrationsTable} WHERE migration = ?");
        $stmt->execute([$originalMigrationName]);
    }

    private function getMigrationClassName(string $migrationName): string {
        // First try exact filename match (without .php extension)
        $baseName = basename($migrationName, '.php');
        if (class_exists($baseName)) {
            return $baseName;
        }
        
        $processedName = $migrationName; // Use a new variable for manipulation
        // Remove timestamp prefix if present (format: YYYY_MM_DD_HHMMSS_)
        if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_(.+)/', $processedName, $matches)) {
            $processedName = $matches[1];
        }
        
        $parts = explode('_', $processedName);
        $parts = array_map('ucfirst', $parts);
        $className = implode('', $parts);
        
        // Ensure valid PHP class name (can't start with number)
        if (is_numeric(substr($className, 0, 1))) {
            $className = 'Migration' . $className;
        }
        
        return $className;
    }

    private function getBatchesToRollback(int $steps): array {
        $stmt = $this->pdo->query("SELECT DISTINCT batch FROM {$this->migrationsTable} ORDER BY batch DESC LIMIT {$steps}");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getMigrationsForBatch(int $batch): array {
        $stmt = $this->pdo->prepare("SELECT migration FROM {$this->migrationsTable} WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
