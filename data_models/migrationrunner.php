<?php
namespace Database;

use PDO;
use PDOException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class MigrationRunner {
    protected $connection;
    protected $migrationsPath;
    protected $migrationsTable = 'migrations';

    public function __construct(PDO $connection, string $migrationsPath) {
        $this->connection = $connection;
        $this->migrationsPath = rtrim($migrationsPath, '/') . '/';
        SchemaBuilder::init($connection);
    }

    public function runMigration(string $migration): void {
        $this->ensureMigrationsTableExists();
        $batch = $this->getNextBatchNumber();

        try {
            $this->connection->beginTransaction();

            $base = realpath($this->migrationsPath);
            $target = realpath($this->migrationsPath . $migration);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: migration");
                throw new \RuntimeException("Invalid migration path");
            }
            require_once $target;
            $className = $this->getMigrationClassName($migration);
            if (!class_exists($className)) {
                throw new \RuntimeException("Migration class $className not found");
            }
            if (!method_exists($className, 'up')) {
                throw new \RuntimeException("Migration $className does not have an up() method");
            }
            $className::up($this->connection);
            
            $this->recordMigration($migration, $batch);
            $this->connection->commit();
            
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new DatabaseException("Migration failed: $migration - " . $e->getMessage());
        }
    }

    public function runMigrations(): array {
        $this->ensureMigrationsTableExists();
        $batch = $this->getNextBatchNumber();
        $executedMigrations = [];

        foreach ($this->getPendingMigrations() as $migration) {
            try {
                $this->connection->beginTransaction();

                $base = realpath($this->migrationsPath);
                $target = realpath($this->migrationsPath . $migration);
                if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                    error_log("SECURITY: blocked dynamic include: migration");
                    throw new \RuntimeException("Invalid migration path");
                }
                require_once $target;
                $className = $this->getMigrationClassName($migration);
                if (!class_exists($className)) {
                    throw new \RuntimeException("Migration class $className not found");
                }
                if (!method_exists($className, 'up')) {
                    throw new \RuntimeException("Migration $className does not have an up() method");
                }
                $className::up($this->connection);
                
                $this->recordMigration($migration, $batch);
                $this->connection->commit();
                
                $executedMigrations[] = $migration;
            } catch (PDOException $e) {
                $this->connection->rollBack();
                throw new DatabaseException("Migration failed: $migration - " . $e->getMessage());
            }
        }

        return $executedMigrations;
    }

    public static function executeStatic(string $sql): void {
        global $dbConnection;
        if (!$dbConnection) {
            throw new DatabaseException("Database connection not initialized");
        }
        $dbConnection->exec($sql);
    }

    protected function ensureMigrationsTableExists(): void {
        $tables = $this->connection->query("SHOW TABLES LIKE '{$this->migrationsTable}'")->fetchAll();
        if (empty($tables)) {
            // Assuming CreateMigrationsTable is in the same namespace and accessible
            // If it's a migration file itself, this logic might need adjustment
            // For now, assuming it's a utility class or part of initial setup
            $migrationFile = '0000_00_00_000000_create_migrations_table.php'; // Placeholder
            $base = realpath($this->migrationsPath);
            $target = realpath($this->migrationsPath . $migrationFile);
            if ($base !== false && $target !== false && substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) === 0 && is_file($target)) {
                 require_once $target;
                 $className = $this->getMigrationClassName($migrationFile);
                 if (class_exists($className) && method_exists($className, 'up')) {
                     $className::up($this->connection);
                 } else {
                    // Fallback to direct schema creation if migration file is problematic
                    SchemaBuilder::create($this->migrationsTable, function (Blueprint $table) {
                        $table->id();
                        $table->string('migration');
                        $table->integer('batch');
                    });
                 }
            } else {
                 // Fallback to direct schema creation if migration file doesn't exist
                 SchemaBuilder::create($this->migrationsTable, function (Blueprint $table) {
                    $table->id();
                    $table->string('migration');
                    $table->integer('batch');
                });
            }
        }
    }

    protected function getNextBatchNumber(): int {
        $stmt = $this->connection->query("SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['max_batch'] ?? 0) + 1;
    }

    protected function getPendingMigrations(): array {
        $executed = $this->getExecutedMigrations();
        $available = $this->getAvailableMigrations();
        
        return array_diff($available, $executed);
    }

    protected function getExecutedMigrations(): array {
        $stmt = $this->connection->query("SELECT migration FROM {$this->migrationsTable} ORDER BY migration");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    protected function getAvailableMigrations(): array {
        $migrations = [];
        if (!is_dir($this->migrationsPath)) {
            // Handle case where migrations directory might not exist yet
            return [];
        }
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->migrationsPath, FilesystemIterator::SKIP_DOTS));
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $migrations[] = $file->getFilename();
            }
        }
        
        sort($migrations);
        return $migrations;
    }

    protected function recordMigration(string $migration, int $batch): void {
        $stmt = $this->connection->prepare(
            "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)"
        );
        $stmt->execute([$migration, $batch]);
    }

    protected function getMigrationClassName(string $migrationFile): string {
        // Remove .php extension
        $nameWithoutExtension = substr($migrationFile, 0, -4);

        // Split by underscore
        $parts = explode('_', $nameWithoutExtension);

        // Remove timestamp parts (assuming format YYYY_MM_DD_HHMMSS_name)
        // This will keep parts after the 6th underscore if that's the naming convention
        // Or adjust based on the actual convention. For YYYYMMDD_HHMMSS_name, it's after the 2nd.
        // Given the example 20240523_132054_phase6_user_activity_logs
        // We need to skip the first two parts if they are date and time.
        
        // A more robust way to handle common timestamp prefixes:
        // Matches YYYY_MM_DD_HHMMSS_ or YYYYMMDDHHMMSS_ or YYYYMMDD_HHMMSS_
        $name = preg_replace('/^(\d{4}_\d{2}_\d{2}_\d{6}_|\d{14}_|\d{8}_\d{6}_)/', '', $nameWithoutExtension);
        
        $classNameParts = explode('_', $name);
        $className = '';
        foreach ($classNameParts as $part) {
            $className .= ucfirst(strtolower($part));
        }

        if (empty($className)) {
             // Fallback if all parts were considered timestamp
            $className = ucfirst(strtolower(str_replace('_', '', $nameWithoutExtension)));
        }
        
        // Assuming migrations are in a sub-namespace like Database\Migrations
        return "Database\\Migrations\\$className";
    }
}
