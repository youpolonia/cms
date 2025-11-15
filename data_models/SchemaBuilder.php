<?php
namespace Database;

use PDO;
use PDOException;

class SchemaBuilder {
    protected static $connection;
    
    public static function init(PDO $connection) {
        self::$connection = $connection;
    }

    public static function createTable(string $name, callable $callback) {
        $table = new TableBuilder($name);
        $callback($table);
        
        $sql = "CREATE TABLE $name (";
        $columns = [];
        
        foreach ($table->getColumns() as $column) {
            $columns[] = self::buildColumnDefinition($column);
        }
        
        $sql .= implode(', ', $columns) . ')';
        
        try {
            self::$connection->exec($sql);
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to create table $name: " . $e->getMessage());
        }
    }

    public static function dropTable(string $name) {
        $sql = "DROP TABLE IF EXISTS $name";
        self::$connection->exec($sql);
    }

    public static function addColumn(string $table, string $name, string $type, array $options = []) {
        $columnDef = self::buildColumnDefinition([
            'name' => $name,
            'type' => $type,
            'options' => $options
        ]);
        
        $sql = "ALTER TABLE $table ADD COLUMN $columnDef";
        self::$connection->exec($sql);
    }

    protected static function buildColumnDefinition(array $column): string {
        $definition = $column['name'] . ' ' . $column['type'];
        
        if (!empty($column['options']['length'])) {
            $definition .= '(' . $column['options']['length'] . ')';
        }
        
        if (!empty($column['options']['not_null'])) {
            $definition .= ' NOT NULL';
        }
        
        if (!empty($column['options']['default'])) {
            $definition .= ' DEFAULT ' . self::$connection->quote($column['options']['default']);
        }
        
        if (!empty($column['options']['auto_increment'])) {
            $definition .= ' AUTO_INCREMENT';
        }
        
        if (!empty($column['options']['primary'])) {
            $definition .= ' PRIMARY KEY';
        }
        
        return $definition;
    }
}

class TableBuilder {
    protected $name;
    protected $columns = [];
    
    public function __construct(string $name) {
        $this->name = $name;
    }
    
    public function addColumn(string $name, string $type, array $options = []) {
        $this->columns[] = [
            'name' => $name,
            'type' => $type,
            'options' => $options
        ];
    }
    
    public function getColumns(): array {
        return $this->columns;
    }
}
