<?php
/**
 * Database migration system for FTP-deployable CMS
 */
class Migration {
    private static string $table = 'migrations';
    private static string $migrationsDir = 'database/migrations/';

    public static function run(): void {
        self::ensureMigrationsTableExists();
        
        $completed = self::getCompletedMigrations();
        $files = glob(self::$migrationsDir . '*.php');
        
        foreach ($files as $file) {
            $migrationName = basename($file, '.php');

            if (!in_array($migrationName, $completed)) {
                $__base = realpath(self::$migrationsDir);
                $__target = realpath($file);
                if ($__base === false || $__target === false || !str_starts_with($__target, $__base . DIRECTORY_SEPARATOR) || !is_file($__target)) {
                    error_log('Invalid migration path blocked: ' . ($file ?? 'unknown'));
                    continue;
                }
                require_once $__target;

                $className = self::filenameToClassName($migrationName);
                if (class_exists($className)) {
                    $migration = new $className();
                    $migration->up();

                    Database::query(
                        "INSERT INTO `".self::$table."` (migration) VALUES (?)",
                        [$migrationName]
                    );
                }
            }
        }
    }

    public static function rollback(): void {
        $lastMigration = Database::query(
            "SELECT migration FROM `".self::$table."` ORDER BY id DESC LIMIT 1"
        )->fetchColumn();
        
        if ($lastMigration) {
            $file = self::$migrationsDir . $lastMigration . '.php';
            if (file_exists($file)) {
                $__base = realpath(self::$migrationsDir);
                $__target = realpath($file);
                if ($__base === false || $__target === false || !str_starts_with($__target, $__base . DIRECTORY_SEPARATOR) || !is_file($__target)) {
                    error_log('Invalid migration path blocked: ' . ($file ?? 'unknown'));
                    return;
                }
                require_once $__target;

                $className = self::filenameToClassName($lastMigration);
                if (class_exists($className)) {
                    $migration = new $className();
                    $migration->down();

                    Database::query(
                        "DELETE FROM `".self::$table."` WHERE migration = ?",
                        [$lastMigration]
                    );
                }
            }
        }
    }

    private static function ensureMigrationsTableExists(): void {
        Database::query("
            CREATE TABLE IF NOT EXISTS `".self::$table."` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    private static function getCompletedMigrations(): array {
        return Database::query("SELECT migration FROM `".self::$table."`")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    private static function filenameToClassName(string $filename): string {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $filename)));
    }
}

abstract class MigrationBase {
    abstract public function up(): void;
    abstract public function down(): void;
    
    protected function execute(string $sql): void {
        Database::query($sql);
    }
}
