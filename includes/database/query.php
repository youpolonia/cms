<?php

namespace Includes\Database;

use PDO;
use PDOStatement;

/**
 * Base query class providing core functionality
 */
abstract class Query
{
    /**
     * @var string The SQL query string
     */
    protected $sql = '';

    /**
     * @var array Bound parameters
     */
    protected $bindings = [];

    /**
     * @var string Connection name
     */
    protected $connectionName = 'default';

    /**
     * @var array Query execution logs
     */
    protected static $queryLog = [];

    /**
     * Set the connection name
     *
     * @param string $name
     * @return $this
     */
    public function connection(string $name): self
    {
        $this->connectionName = $name;
        return $this;
    }

    /**
     * Bind a parameter value
     *
     * @param string $key
     * @param mixed $value
     * @param int $type PDO param type
     * @return $this
     */
    public function bind(string $key, $value, int $type = PDO::PARAM_STR): self
    {
        $this->bindings[$key] = [
            'value' => $value,
            'type' => $type
        ];
        return $this;
    }

    /**
     * Execute the query
     *
     * @return PDOStatement
     * @throws DatabaseException
     */
    public function execute(): PDOStatement
    {
        $connection = DatabaseConnection::getConnection($this->connectionName);
        $statement = $connection->prepare($this->sql);

        foreach ($this->bindings as $key => $binding) {
            $statement->bindValue($key, $binding['value'], $binding['type']);
        }

        $start = microtime(true);
        $statement->execute();
        $time = microtime(true) - $start;

        self::logQuery($this->sql, $this->bindings, $time);

        DatabaseConnection::releaseConnection($connection, $this->connectionName);
        return $statement;
    }

    /**
     * Log a query execution
     *
     * @param string $sql
     * @param array $bindings
     * @param float $time
     */
    protected static function logQuery(string $sql, array $bindings, float $time): void
    {
        self::$queryLog[] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
            'timestamp' => microtime(true)
        ];
    }

    /**
     * Get the query log
     *
     * @return array
     */
    public static function getQueryLog(): array
    {
        return self::$queryLog;
    }

    /**
     * Clear the query log
     */
    public static function clearQueryLog(): void
    {
        self::$queryLog = [];
    }

    /**
     * Get the last executed query
     *
     * @return array|null
     */
    public static function getLastQuery(): ?array
    {
        return end(self::$queryLog) ?: null;
    }
}
