<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class MigrationsController
{
    private string $migrationsDir;

    public function __construct()
    {
        $this->migrationsDir = \CMS_ROOT . '/database/migrations';
    }

    public function index(Request $request): void
    {
        $pdo = db();

        // Get executed migrations
        $stmt = $pdo->query("SELECT * FROM migrations ORDER BY batch DESC, id DESC");
        $executed = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $executedNames = array_column($executed, 'migration');

        // Scan for pending migrations
        $pending = $this->getPendingMigrations($executedNames);

        // Get last batch number
        $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $lastBatch = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['max_batch'] ?? 0);

        render('admin/migrations/index', [
            'executed' => $executed,
            'pending' => $pending,
            'lastBatch' => $lastBatch,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function run(Request $request): void
    {
        $pdo = db();

        // Get executed migrations
        $stmt = $pdo->query("SELECT migration FROM migrations");
        $executedNames = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Get pending migrations
        $pending = $this->getPendingMigrations($executedNames);

        if (empty($pending)) {
            Session::flash('error', 'No pending migrations to run.');
            Response::redirect('/admin/migrations');
        }

        // Get next batch number
        $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $batch = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['max_batch'] ?? 0) + 1;

        $ran = 0;
        $errors = [];

        foreach ($pending as $migration) {
            $result = $this->executeMigration($migration, 'up');

            if ($result['success']) {
                // Record migration
                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch, executed_at) VALUES (?, ?, NOW())");
                $stmt->execute([$migration, $batch]);
                $ran++;
            } else {
                $errors[] = "{$migration}: {$result['error']}";
                break; // Stop on first error
            }
        }

        if (empty($errors)) {
            Session::flash('success', "Successfully ran {$ran} migration(s) in batch {$batch}.");
        } else {
            Session::flash('error', "Ran {$ran} migration(s), then failed: " . implode('; ', $errors));
        }

        Response::redirect('/admin/migrations');
    }

    public function runSingle(Request $request): void
    {
        $migration = basename($request->post('migration', ''));

        if (empty($migration) || !preg_match('/^[\w\-]+\.php$/', $migration)) {
            Session::flash('error', 'Invalid migration file.');
            Response::redirect('/admin/migrations');
        }

        $pdo = db();

        // Check if already executed
        $stmt = $pdo->prepare("SELECT id FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
        if ($stmt->fetch()) {
            Session::flash('error', 'Migration already executed.');
            Response::redirect('/admin/migrations');
        }

        // Get next batch number
        $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $batch = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['max_batch'] ?? 0) + 1;

        $result = $this->executeMigration($migration, 'up');

        if ($result['success']) {
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch, executed_at) VALUES (?, ?, NOW())");
            $stmt->execute([$migration, $batch]);
            Session::flash('success', "Migration {$migration} executed successfully.");
        } else {
            Session::flash('error', "Migration failed: {$result['error']}");
        }

        Response::redirect('/admin/migrations');
    }

    public function rollback(Request $request): void
    {
        $pdo = db();

        // Get last batch
        $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations");
        $lastBatch = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['max_batch'] ?? 0);

        if ($lastBatch === 0) {
            Session::flash('error', 'Nothing to rollback.');
            Response::redirect('/admin/migrations');
        }

        // Get migrations in last batch
        $stmt = $pdo->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $rolledBack = 0;
        $errors = [];

        foreach ($migrations as $migration) {
            $result = $this->executeMigration($migration, 'down');

            if ($result['success']) {
                $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration = ?");
                $stmt->execute([$migration]);
                $rolledBack++;
            } else {
                $errors[] = "{$migration}: {$result['error']}";
                break;
            }
        }

        if (empty($errors)) {
            Session::flash('success', "Rolled back {$rolledBack} migration(s) from batch {$lastBatch}.");
        } else {
            Session::flash('error', "Rolled back {$rolledBack}, then failed: " . implode('; ', $errors));
        }

        Response::redirect('/admin/migrations');
    }

    public function create(Request $request): void
    {
        $name = trim($request->post('name', ''));
        $name = preg_replace('/[^a-z0-9_]/', '_', strtolower($name));

        if (empty($name) || strlen($name) < 3) {
            Session::flash('error', 'Migration name must be at least 3 characters.');
            Response::redirect('/admin/migrations');
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $filepath = $this->migrationsDir . '/' . $filename;

        $template = <<<'PHP'
<?php
declare(strict_types=1);

/**
 * Migration: %NAME%
 * Created: %DATE%
 */

return new class {
    public function up(\PDO $pdo): void
    {
        // Run migration
        // Example:
        // $pdo->exec("CREATE TABLE example (id INT PRIMARY KEY)");
    }

    public function down(\PDO $pdo): void
    {
        // Reverse migration
        // Example:
        // $pdo->exec("DROP TABLE IF EXISTS example");
    }
};
PHP;

        $content = str_replace(
            ['%NAME%', '%DATE%'],
            [$name, date('Y-m-d H:i:s')],
            $template
        );

        if (file_put_contents($filepath, $content) === false) {
            Session::flash('error', 'Failed to create migration file. Check directory permissions.');
            Response::redirect('/admin/migrations');
        }

        chmod($filepath, 0644);

        Session::flash('success', "Created migration: {$filename}");
        Response::redirect('/admin/migrations');
    }

    private function getPendingMigrations(array $executed): array
    {
        if (!is_dir($this->migrationsDir)) {
            return [];
        }

        $files = scandir($this->migrationsDir);
        $pending = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
            if (in_array($file, $executed)) continue;

            $pending[] = $file;
        }

        sort($pending);
        return $pending;
    }

    private function executeMigration(string $migration, string $direction): array
    {
        $filepath = $this->migrationsDir . '/' . $migration;

        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'Migration file not found'];
        }

        try {
            $pdo = db();
            $migrationResult = require $filepath;

            // Handle anonymous class (new style)
            if (is_object($migrationResult) && method_exists($migrationResult, $direction)) {
                $migrationResult->$direction($pdo);
                return ['success' => true, 'error' => null];
            }

            // Handle class-based migrations (old style using MigrationBase)
            $className = $this->filenameToClassName(pathinfo($migration, PATHINFO_FILENAME));
            if (class_exists($className) && method_exists($className, $direction)) {
                $pdo->beginTransaction();
                $instance = new $className();
                $instance->$direction();
                $pdo->commit();
                return ['success' => true, 'error' => null];
            }

            return ['success' => false, 'error' => "Migration missing {$direction}() method"];
        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function filenameToClassName(string $filename): string
    {
        // Remove timestamp prefix (e.g., 202508081800_)
        $name = preg_replace('/^\d+_/', '', $filename);
        // Convert snake_case to PascalCase
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
