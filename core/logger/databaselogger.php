<?php
require_once __DIR__ . '/database.php';

/**
 * Database logger implementation
 */
namespace Core\Logger;

class DatabaseLogger implements LoggerInterface
{
    private \PDO $pdo;
    private string $tableName = 'system_logs';
    private array $requiredColumns = [
        'id', 'timestamp', 'level', 'message', 'context'
    ];

    /**
     * Constructor - establishes database connection
     * 
     * @param array $dbConfig Database configuration (optional):
     *   - table: Log table name (optional, defaults to 'system_logs')
     */
    public function __construct(array $dbConfig = [])
    {
        if (isset($dbConfig['table'])) {
            $this->tableName = $dbConfig['table'];
        }

        $this->pdo = \core\Database::connection();
        $this->validateTableStructure();
    }

    /**
     * Validate that the log table has required columns
     * 
     * @throws \RuntimeException If table structure is invalid
     */
    private function validateTableStructure(): void
    {
        try {
            $stmt = $this->pdo->query("DESCRIBE {$this->tableName}");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            foreach ($this->requiredColumns as $column) {
                if (!in_array($column, $columns)) {
                    throw new \RuntimeException("Missing required column: {$column}");
                }
            }
        } catch (\PDOException $e) {
            throw new \RuntimeException("Failed to validate table structure: " . $e->getMessage());
        }
    }

    /**
     * Log a message to database
     * 
     * @param string $message The log message
     * @param array $context Additional context data
     * @param string $level Log level
     * @return bool True if log was successful
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        try {
            $query = "INSERT INTO {$this->tableName}
                     (timestamp, level, message, context)
                     VALUES (:timestamp, :level, :message, :context)";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':timestamp' => $this->getTimestamp(),
                ':level' => $level,
                ':message' => $message,
                ':context' => $this->serializeContext($context)
            ]);
        } catch (\Throwable $e) {
            $this->handleError($e->getMessage());
        }
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }
}
