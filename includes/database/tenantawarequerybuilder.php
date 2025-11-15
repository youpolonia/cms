<?php

declare(strict_types=1);

namespace App\Includes\Database;

use PDO;
use App\Includes\MultiSite;

class TenantAwareQueryBuilder
{
    private \PDO $connection;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private array $columns = ['*'];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $orders = [];
    private array $joins = [];
    private ?int $tenantId = null;

    public function __construct(\PDO $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
        
        if (MultiSite::isInitialized()) {
            $this->tenantId = MultiSite::currentTenantId();
        }
    }

    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;
        return $this;
    }

    private function logQueryPerformance(string $query, float $duration): void
    {
        if ($this->tenantId && $duration > 100) { // Log slow queries >100ms
            DatabaseConnection::logPerformanceMetric(
                'query_time',
                $duration,
                [
                    'query' => $query,
                    'table' => $this->table,
                    'bindings' => $this->bindings
                ]
            );
        }
    }

    public function where(string $column, string $operator, $value): self
    {
        if (!in_array($operator, ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE'])) {
            throw new \InvalidArgumentException("Invalid operator: $operator");
        }
        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function execute(): array
    {
        $start = microtime(true);
        $query = $this->buildQuery();
        $stmt = $this->connection->prepare($query);
        $stmt->execute($this->bindings);
        $duration = (microtime(true) - $start) * 1000;
        
        $this->logQueryPerformance($query, $duration);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildQuery(): string
    {
        $query = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;
        
        if (!empty($this->joins)) {
            $query .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->wheres)) {
            $query .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        
        if (!empty($this->orders)) {
            $query .= ' ORDER BY ' . implode(', ', $this->orders);
        }
        
        if ($this->limit !== null) {
            $query .= ' LIMIT ' . $this->limit;
            if ($this->offset !== null) {
                $query .= ' OFFSET ' . $this->offset;
            }
        }
        
        return $query;
    }

    public function orWhere(string $column, string $operator, $value): self
    {
        if (!in_array($operator, ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE'])) {
            throw new \InvalidArgumentException("Invalid operator: $operator");
        }
        $this->wheres[] = "OR $column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second): self
    {
        $prefixedTable = MultiSite::getPrefixedTableName($table);
        $this->joins[] = "JOIN $prefixedTable ON $first $operator $second";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = "$column $direction";
        return $this;
    }

    public function get(): array
    {
        $query = $this->buildSelectQuery();
        $stmt = $this->connection->prepare($query);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";
        $stmt = $this->connection->prepare($query);
        return $stmt->execute(array_values($data));
    }

    public function update(array $data): bool
    {
        $setClause = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $query = "UPDATE $this->table SET $setClause";
        
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        $bindings = array_merge(array_values($data), $this->bindings);
        $stmt = $this->connection->prepare($query);
        return $stmt->execute($bindings);
    }

    public function delete(): bool
    {
        $query = "DELETE FROM $this->table";
        
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        $stmt = $this->connection->prepare($query);
        return $stmt->execute($this->bindings);
    }

    private function buildSelectQuery(): string
    {
        $query = "SELECT " . implode(', ', $this->columns) . " FROM $this->table";
        
        if (!empty($this->joins)) {
            $query .= " " . implode(' ', $this->joins);
        }
        
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        if (!empty($this->orders)) {
            $query .= " ORDER BY " . implode(', ', $this->orders);
        }
        
        if ($this->limit !== null) {
            $query .= " LIMIT " . $this->limit;
        }
        
        if ($this->offset !== null) {
            $query .= " OFFSET " . $this->offset;
        }
        
        return $query;
    }

    public static function getPrefixedTable(string $table): string
    {
        return MultiSite::getPrefixedTableName($table);
    }
}
