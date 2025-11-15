<?php

namespace Includes\Database;

use Includes\Database\Connection;

class QueryBuilder
{
    protected $connection;
    protected $table;
    protected $columns = ['*'];
    protected $wheres = [];
    protected $bindings = [];
    protected $orders = [];
    protected $limit = null;
    protected $offset = null;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = "$column $direction";
        return $this;
    }

    public function limit(int $count, int $offset = null): self
    {
        $this->limit = $count;
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $query = $this->buildSelectQuery();
        $stmt = $this->connection->getPdo()->prepare($query);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function buildSelectQuery(): string
    {
        $query = "SELECT " . implode(', ', $this->columns) . " FROM $this->table";

        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orders)) {
            $query .= " ORDER BY " . implode(', ', $this->orders);
        }

        if ($this->limit !== null) {
            $query .= " LIMIT " . $this->limit;
            if ($this->offset !== null) {
                $query .= " OFFSET " . $this->offset;
            }
        }

        return $query;
    }

    public function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->connection->getPdo()->prepare($query);
        $stmt->execute(array_values($data));
        
        return (int)$this->connection->getPdo()->lastInsertId();
    }

    public function update(array $data): int
    {
        $sets = [];
        $values = [];
        foreach ($data as $column => $value) {
            $sets[] = "$column = ?";
            $values[] = $value;
        }
        
        $query = "UPDATE $this->table SET " . implode(', ', $sets);
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
            $values = array_merge($values, $this->bindings);
        }
        
        $stmt = $this->connection->getPdo()->prepare($query);
        $stmt->execute($values);
        
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $query = "DELETE FROM $this->table";
        if (!empty($this->wheres)) {
            $query .= " WHERE " . implode(' AND ', $this->wheres);
        }
        
        $stmt = $this->connection->getPdo()->prepare($query);
        $stmt->execute($this->bindings);
        
        return $stmt->rowCount();
    }
}
