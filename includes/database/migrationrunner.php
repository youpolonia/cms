<?php
declare(strict_types=1);

namespace Includes\Database;

use PDO;
use PDOException;
use RuntimeException;
use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Migration runner for schema management
 */
class MigrationRunner
{
    /**
     * @var PDO Database connection
     */
    protected PDO $pdo;
    
    /**
     * @var string Migrations directory
     */
    protected string $migrationsDir;
    
    /**
     * @var string Migrations table name
     */
    protected string $migrationsTable = 'migrations';
    
    /**
     * @var array Applied migrations
     */
    protected array $appliedMigrations = [];
    
    /**
     * @var array Migration files
     */
    protected array $migrationFiles = [];
    
    /**
     * @var array Migration instances
     */
    protected array $migrations = [];
    
    /**
     * @var int Current batch number
     */
    protected int $currentBatch = 0;
    
    /**
     * Constructor
     *
     * @param PDO $pdo
     * @param string $migrationsDir
     */
    public function __construct(PDO $pdo, string $migrationsDir)
    {
        $this->pdo = $pdo;
        $this->migrationsDir = rtrim($migrationsDir, '/');
    }
    
    /**
     * Set the migrations table name
     *
     * @param string $table
     * @return $this
     */
    public function setMigrationsTable(string $table): self
    {
        $this->migrationsTable = $table;
        return $this;
    }
    
    /**
     * Run all pending migrations (instance method)
     *
     * @return array Applied migrations
     * @throws RuntimeException
     */
    public function run(): array
    {
        return self::runStatic($this->pdo, $this->migrationsDir);
    }

    /**
     * Run all pending migrations (static method)
     *
     * @param PDO $pdo Database connection
     * @param string $migrationsDir Path to migrations directory
     * @param string $migrationsTable Optional table name (default: 'migrations')
     * @return array Applied migrations
     * @throws RuntimeException
     */
    public static function runStatic(PDO $pdo, string $migrationsDir, string $migrationsTable = 'migrations'): array
    {
        self::ensureMigrationsTableExistsStatic($pdo, $migrationsTable);
        $appliedMigrations = self::loadAppliedMigrationsStatic($pdo, $migrationsTable);
        $migrationFiles = self::loadMigrationFilesStatic($migrationsDir);
        
        $newMigrations = [];
        $currentBatch = self::getCurrentBatchStatic($pdo, $migrationsTable);
        
        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($migrationName, $appliedMigrations)) {
                self::runMigrationStatic($pdo, $file, $migrationsTable, $currentBatch + 1);
                $newMigrations[] = $migrationName;
            }
        }
        
        return $newMigrations;
    }
    
    /**
     * Rollback the last batch of migrations
     *
     * @return array Rolled back migrations
     * @throws RuntimeException
     */
    public function rollback(): array
    {
        $this->ensureMigrationsTableExists();
        $this->loadAppliedMigrations();
        
        $lastBatch = $this->getLastBatchNumber();
        $migrationsToRollback = $this->getMigrationsInBatch($lastBatch);
        
        $rolledBackMigrations = [];
        
        foreach (array_reverse($migrationsToRollback) as $migration) {
            $this->rollbackMigration($migration);
            $rolledBackMigrations[] = $migration;
        }
        
        return $rolledBackMigrations;
    }
    
    /**
     * Reset all migrations
     *
     * @return array Rolled back migrations
     * @throws RuntimeException
     */
    public function reset(): array
    {
        $this->ensureMigrationsTableExists();
        $this->loadAppliedMigrations();
        
        $rolledBackMigrations = [];
        
        foreach (array_reverse($this->appliedMigrations) as $migration) {
            $this->rollbackMigration($migration);
            $rolledBackMigrations[] = $migration;
        }
        
        return $rolledBackMigrations;
    }
    
    /**
     * Refresh all migrations (reset and run)
     *
     * @return array Applied migrations
     * @throws RuntimeException
     */
    public function refresh(): array
    {
        $this->reset();
        return $this->run();
    }
    
    /**
     * Get all migration files
     *
     * @return array
     */
    public function getMigrationFiles(): array
    {
        $this->loadMigrationFiles();
        return $this->migrationFiles;
    }
    
    /**
     * Get all applied migrations
     *
     * @return array
     */
    public function getAppliedMigrations(): array
    {
        $this->loadAppliedMigrations();
        return $this->appliedMigrations;
    }
    
    /**
     * Get pending migrations
     *
     * @return array
     */
    public function getPendingMigrations(): array
    {
        $this->loadAppliedMigrations();
        $this->loadMigrationFiles();
        
        $pendingMigrations = [];
        
        foreach ($this->migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($migrationName, $this->appliedMigrations)) {
                $pendingMigrations[] = $migrationName;
            }
        }
        
        return $pendingMigrations;
    }
    
    /**
     * Ensure the migrations table exists
     *
     * @return void
     * @throws RuntimeException
     */
    protected function ensureMigrationsTableExists(): void
    {
        self::ensureMigrationsTableExistsStatic($this->pdo, $this->migrationsTable);
    }

    protected static function ensureMigrationsTableExistsStatic(PDO $pdo, string $migrationsTable): void
    {
        try {
            $pdo->query("SELECT 1 FROM `{$migrationsTable}` LIMIT 1");
        } catch (PDOException $e) {
            // Table doesn't exist, create it
            /* exec disabled: was $pdo->exec("
                CREATE TABLE `{$migrationsTable}` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `migration` VARCHAR(255) NOT NULL,
                    `batch` INT UNSIGNED NOT NULL,
                    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `{$migrationsTable}_migration_unique` (`migration`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ") */
        }
    }
    
    /**
     * Load applied migrations from the database
     *
     * @return void
     */
    protected function loadAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM `{$this->migrationsTable}` ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    protected static function loadAppliedMigrationsStatic(PDO $pdo, string $migrationsTable): array
    {
        $stmt = $pdo->query("SELECT migration FROM `{$migrationsTable}` ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
        // Batch processing handled in instance method
    
    /**
     * Load migration files from the migrations directory
     *
     * @return void
     */
    protected function loadMigrationFiles(): void
    {
        $this->migrationFiles = [];
        
        if (!is_dir($this->migrationsDir)) {
            return;
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->migrationsDir)
        );
        
        $phpFiles = new RegexIterator($files, '/^.+\.php$/i', RegexIterator::GET_MATCH);
        
        foreach ($phpFiles as $file) {
            $this->migrationFiles[] = $file[0];
        }
        
        sort($this->migrationFiles);
    }
    
    /**
     * Run a migration
     *
     * @param string $file
     * @return void
     * @throws RuntimeException
     */
    protected function runMigration(string $file): void
    {
        $migrationName = pathinfo($file, PATHINFO_FILENAME);
        $className = $this->getMigrationClassName($file);

        if (!class_exists($className)) {
            $__base = realpath($this->migrationsDir);
            $__target = realpath($file);
            if ($__base === false || $__target === false || !str_starts_with($__target, $__base . DIRECTORY_SEPARATOR) || !is_file($__target)) {
                error_log('Invalid migration path blocked (runner@308): ' . ($file ?? 'unknown'));
                return;
            }
            require_once $__target;
        }

        if (!class_exists($className)) {
            throw new RuntimeException("Migration class {$className} not found in {$file}");
        }
        
        $migration = new $className($this->pdo, $migrationName);
        
        if (!method_exists($migration, 'migrate') || !method_exists($migration, 'rollback')) {
            throw new RuntimeException("Migration class {$className} must implement migrate() and rollback() methods");
        }
        
        try {
            $migration->migrate();
            $this->recordMigration($migrationName);
        } catch (\Exception $e) {
            throw new RuntimeException("Migration failed: {$migrationName} - {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * Rollback a migration
     *
     * @param string $migrationName
     * @return void
     * @throws RuntimeException
     */
    protected function rollbackMigration(string $migrationName): void
    {
        $file = $this->findMigrationFile($migrationName);
        
        if (!$file) {
            throw new RuntimeException("Migration file for {$migrationName} not found");
        }
        
        $className = $this->getMigrationClassName($file);

        if (!class_exists($className)) {
            $__base = realpath($this->migrationsDir);
            $__target = realpath($file);
            if ($__base === false || $__target === false || !str_starts_with($__target, $__base . DIRECTORY_SEPARATOR) || !is_file($__target)) {
                error_log('Invalid migration path blocked (runner@347): ' . ($file ?? 'unknown'));
                return;
            }
            require_once $__target;
        }

        if (!class_exists($className)) {
            throw new RuntimeException("Migration class {$className} not found in {$file}");
        }

        $migration = new $className($this->pdo, $migrationName);

        if (!$migration instanceof Migration) {
            throw new RuntimeException("Migration class {$className} must extend " . Migration::class);
        }
        
        try {
            $migration->rollback();
            $this->removeMigrationRecord($migrationName);
        } catch (\Exception $e) {
            throw new RuntimeException("Migration rollback failed: {$migrationName} - {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * Record a migration in the database
     *
     * @param string $migrationName
     * @return void
     */
    protected function recordMigration(string $migrationName): void
    {
        $batch = $this->currentBatch + 1;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO `{$this->migrationsTable}` (migration, batch)
            VALUES (?, ?)
        ");
        
        $stmt->execute([$migrationName, $batch]);
        
        $this->appliedMigrations[] = $migrationName;
        $this->currentBatch = $batch;
    }
    
    /**
     * Remove a migration record from the database
     *
     * @param string $migrationName
     * @return void
     */
    protected function removeMigrationRecord(string $migrationName): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM `{$this->migrationsTable}`
            WHERE migration = ?
        ");
        
        $stmt->execute([$migrationName]);
        
        $key = array_search($migrationName, $this->appliedMigrations);
        
        if ($key !== false) {
            unset($this->appliedMigrations[$key]);
            $this->appliedMigrations = array_values($this->appliedMigrations);
        }
    }
    
    /**
     * Get the last batch number
     *
     * @return int
     */
    protected function getLastBatchNumber(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(batch) FROM `{$this->migrationsTable}`");
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get migrations in a batch
     *
     * @param int $batch
     * @return array
     */
    protected function getMigrationsInBatch(int $batch): array
    {
        $stmt = $this->pdo->prepare("
            SELECT migration FROM `{$this->migrationsTable}`
            WHERE batch = ?
            ORDER BY id ASC
        ");
        
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Find a migration file by name
     *
     * @param string $migrationName
     * @return string|null
     */
    protected function findMigrationFile(string $migrationName): ?string
    {
        $this->loadMigrationFiles();
        
        foreach ($this->migrationFiles as $file) {
            if (pathinfo($file, PATHINFO_FILENAME) === $migrationName) {
                return $file;
            }
        }
        
        return null;
    }
    
    /**
     * Get the migration class name from a file
     *
     * @param string $file
     * @return string
     */
    protected function getMigrationClassName(string $file): string
    {
        $migrationName = pathinfo($file, PATHINFO_FILENAME);
        
        // Try to extract the class name from the file
        $content = file_get_contents($file);
        preg_match('/class\s+([a-zA-Z0-9_]+)\s+extends\s+/i', $content, $matches);
        
        if (isset($matches[1])) {
            return $matches[1];
        }
        
        // Fall back to the migration name
        return $migrationName;
    }
}
