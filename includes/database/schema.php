<?php
declare(strict_types=1);

namespace Includes\Database;

/**
 * Schema builder for database table definitions
 */
class Schema
{
    /**
     * Create a new table
     *
     * @param string $table
     * @param callable $callback
     * @return Schema\Table
     */
    public static function create(string $table, callable $callback): Schema\Table
    {
        $schema = new Schema\Table($table);
        $callback($schema);
        
        return $schema;
    }
    
    /**
     * Alter an existing table
     *
     * @param string $table
     * @param callable $callback
     * @return Schema\Table
     */
    public static function alter(string $table, callable $callback): Schema\Table
    {
        $schema = new Schema\Table($table, true);
        $callback($schema);
        
        return $schema;
    }
    
    /**
     * Drop a table
     *
     * @param string $table
     * @return string
     */
    public static function drop(string $table): string
    {
        return "DROP TABLE IF EXISTS `{$table}`";
    }
    
    /**
     * Rename a table
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public static function rename(string $from, string $to): string
    {
        return "RENAME TABLE `{$from}` TO `{$to}`";
    }
    
    /**
     * Execute a raw SQL statement
     *
     * @param string $sql
     * @return string
     */
    public static function raw(string $sql): string
    {
        return $sql;
    }
}

namespace Includes\Database\Schema;

/**
 * Table schema builder
 */
class Table
{
    /**
     * @var string Table name
     */
    protected string $table;
    
    /**
     * @var bool Whether this is an alter operation
     */
    protected bool $alter;
    
    /**
     * @var array Columns
     */
    protected array $columns = [];
    
    /**
     * @var array Indexes
     */
    protected array $indexes = [];
    
    /**
     * @var array Foreign keys
     */
    protected array $foreignKeys = [];
    
    /**
     * @var string|null Primary key
     */
    protected ?string $primaryKey = null;
    
    /**
     * @var array Table options
     */
    protected array $options = [
        'engine' => 'InnoDB',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci'
    ];
    
    /**
     * Constructor
     *
     * @param string $table
     * @param bool $alter
     */
    public function __construct(string $table, bool $alter = false)
    {
        $this->table = $table;
        $this->alter = $alter;
    }
    
    /**
     * Add an auto-incrementing ID column
     *
     * @param string $column
     * @return $this
     */
    public function id(string $column = 'id'): self
    {
        $this->columns[] = new Column($column, 'INT', [
            'unsigned' => true,
            'autoIncrement' => true,
            'primary' => true
        ]);
        
        $this->primaryKey = $column;
        
        return $this;
    }
    
    /**
     * Add a big integer ID column
     *
     * @param string $column
     * @return $this
     */
    public function bigId(string $column = 'id'): self
    {
        $this->columns[] = new Column($column, 'BIGINT', [
            'unsigned' => true,
            'autoIncrement' => true,
            'primary' => true
        ]);
        
        $this->primaryKey = $column;
        
        return $this;
    }
    
    /**
     * Add a UUID column
     *
     * @param string $column
     * @return $this
     */
    public function uuid(string $column = 'uuid'): self
    {
        $this->columns[] = new Column($column, 'CHAR(36)', [
            'primary' => true
        ]);
        
        $this->primaryKey = $column;
        
        return $this;
    }
    
    /**
     * Add a string column
     *
     * @param string $column
     * @param int $length
     * @param array $options
     * @return $this
     */
    public function string(string $column, int $length = 255, array $options = []): self
    {
        $this->columns[] = new Column($column, "VARCHAR({$length})", $options);
        return $this;
    }
    
    /**
     * Add a text column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function text(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'TEXT', $options);
        return $this;
    }
    
    /**
     * Add a medium text column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function mediumText(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'MEDIUMTEXT', $options);
        return $this;
    }
    
    /**
     * Add a long text column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function longText(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'LONGTEXT', $options);
        return $this;
    }
    
    /**
     * Add an integer column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function integer(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'INT', $options);
        return $this;
    }
    
    /**
     * Add a big integer column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function bigInteger(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'BIGINT', $options);
        return $this;
    }
    
    /**
     * Add a small integer column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function smallInteger(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'SMALLINT', $options);
        return $this;
    }
    
    /**
     * Add a tiny integer column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function tinyInteger(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'TINYINT', $options);
        return $this;
    }
    
    /**
     * Add a float column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function float(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'FLOAT', $options);
        return $this;
    }
    
    /**
     * Add a double column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function double(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'DOUBLE', $options);
        return $this;
    }
    
    /**
     * Add a decimal column
     *
     * @param string $column
     * @param int $precision
     * @param int $scale
     * @param array $options
     * @return $this
     */
    public function decimal(string $column, int $precision = 8, int $scale = 2, array $options = []): self
    {
        $this->columns[] = new Column($column, "DECIMAL({$precision},{$scale})", $options);
        return $this;
    }
    
    /**
     * Add a boolean column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function boolean(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'TINYINT(1)', $options);
        return $this;
    }
    
    /**
     * Add a date column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function date(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'DATE', $options);
        return $this;
    }
    
    /**
     * Add a datetime column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function dateTime(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'DATETIME', $options);
        return $this;
    }
    
    /**
     * Add a timestamp column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function timestamp(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'TIMESTAMP', $options);
        return $this;
    }
    
    /**
     * Add created_at and updated_at timestamp columns
     *
     * @return $this
     */
    public function timestamps(): self
    {
        $this->timestamp('created_at', ['nullable' => true]);
        $this->timestamp('updated_at', ['nullable' => true]);
        return $this;
    }
    
    /**
     * Add a JSON column
     *
     * @param string $column
     * @param array $options
     * @return $this
     */
    public function json(string $column, array $options = []): self
    {
        $this->columns[] = new Column($column, 'JSON', $options);
        return $this;
    }
    
    /**
     * Add an enum column
     *
     * @param string $column
     * @param array $values
     * @param array $options
     * @return $this
     */
    public function enum(string $column, array $values, array $options = []): self
    {
        $valuesStr = "'" . implode("', '", $values) . "'";
        $this->columns[] = new Column($column, "ENUM({$valuesStr})", $options);
        return $this;
    }
    
    /**
     * Add a foreign key column
     *
     * @param string $column
     * @param string $referencedTable
     * @param string $referencedColumn
     * @param array $options
     * @return $this
     */
    public function foreignId(string $column, string $referencedTable, string $referencedColumn = 'id', array $options = []): self
    {
        $this->integer($column, array_merge(['unsigned' => true], $options));
        
        $this->foreignKeys[] = [
            'column' => $column,
            'referencedTable' => $referencedTable,
            'referencedColumn' => $referencedColumn,
            'onDelete' => $options['onDelete'] ?? 'RESTRICT',
            'onUpdate' => $options['onUpdate'] ?? 'RESTRICT'
        ];
        
        return $this;
    }
    
    /**
     * Add an index
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function index($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
        if ($name === null) {
            $name = $this->table . '_' . implode('_', $columns) . '_index';
        }
        
        $this->indexes[] = [
            'type' => 'INDEX',
            'name' => $name,
            'columns' => $columns
        ];
        
        return $this;
    }
    
    /**
     * Add a unique index
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function unique($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
        if ($name === null) {
            $name = $this->table . '_' . implode('_', $columns) . '_unique';
        }
        
        $this->indexes[] = [
            'type' => 'UNIQUE INDEX',
            'name' => $name,
            'columns' => $columns
        ];
        
        return $this;
    }
    
    /**
     * Set the primary key
     *
     * @param string|array $columns
     * @return $this
     */
    public function primary($columns): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
        $this->indexes[] = [
            'type' => 'PRIMARY KEY',
            'columns' => $columns
        ];
        
        return $this;
    }
    
    /**
     * Set the table engine
     *
     * @param string $engine
     * @return $this
     */
    public function engine(string $engine): self
    {
        $this->options['engine'] = $engine;
        return $this;
    }
    
    /**
     * Set the table charset
     *
     * @param string $charset
     * @return $this
     */
    public function charset(string $charset): self
    {
        $this->options['charset'] = $charset;
        return $this;
    }
    
    /**
     * Set the table collation
     *
     * @param string $collation
     * @return $this
     */
    public function collation(string $collation): self
    {
        $this->options['collation'] = $collation;
        return $this;
    }
    
    /**
     * Generate the SQL statement
     *
     * @return string
     */
    public function toSql(): string
    {
        if ($this->alter) {
            return $this->toAlterSql();
        }
        
        return $this->toCreateSql();
    }
    
    /**
     * Generate the CREATE TABLE SQL statement
     *
     * @return string
     */
    protected function toCreateSql(): string
    {
        $columnDefinitions = [];
        
        foreach ($this->columns as $column) {
            $columnDefinitions[] = "  " . $column->toSql();
        }
        
        foreach ($this->indexes as $index) {
            $columnList = '`' . implode('`, `', $index['columns']) . '`';
            
            if ($index['type'] === 'PRIMARY KEY') {
                $columnDefinitions[] = "  PRIMARY KEY ({$columnList})";
            } else {
                $columnDefinitions[] = "  {$index['type']} `{$index['name']}` ({$columnList})";
            }
        }
        
        foreach ($this->foreignKeys as $foreignKey) {
            $name = $this->table . '_' . $foreignKey['column'] . '_foreign';
            
            $columnDefinitions[] = sprintf(
                "  CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s ON UPDATE %s",
                $name,
                $foreignKey['column'],
                $foreignKey['referencedTable'],
                $foreignKey['referencedColumn'],
                $foreignKey['onDelete'],
                $foreignKey['onUpdate']
            );
        }
        
        $sql = "CREATE TABLE `{$this->table}` (\n";
        $sql .= implode(",\n", $columnDefinitions);
        $sql .= "\n) ENGINE={$this->options['engine']} DEFAULT CHARSET={$this->options['charset']} COLLATE={$this->options['collation']}";
        
        return $sql;
    }
    
    /**
     * Generate the ALTER TABLE SQL statement
     *
     * @return string
     */
    protected function toAlterSql(): string
    {
        $alterStatements = [];
        
        foreach ($this->columns as $column) {
            $alterStatements[] = "ADD COLUMN " . $column->toSql();
        }
        
        foreach ($this->indexes as $index) {
            $columnList = '`' . implode('`, `', $index['columns']) . '`';
            
            if ($index['type'] === 'PRIMARY KEY') {
                $alterStatements[] = "ADD PRIMARY KEY ({$columnList})";
            } else {
                $alterStatements[] = "ADD {$index['type']} `{$index['name']}` ({$columnList})";
            }
        }
        
        foreach ($this->foreignKeys as $foreignKey) {
            $name = $this->table . '_' . $foreignKey['column'] . '_foreign';
            
            $alterStatements[] = sprintf(
                "ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`) ON DELETE %s ON UPDATE %s",
                $name,
                $foreignKey['column'],
                $foreignKey['referencedTable'],
                $foreignKey['referencedColumn'],
                $foreignKey['onDelete'],
                $foreignKey['onUpdate']
            );
        }
        
        if (empty($alterStatements)) {
            return '';
        }
        
        return "ALTER TABLE `{$this->table}` " . implode(", ", $alterStatements);
    }
}

/**
 * Column schema builder
 */
class Column
{
    /**
     * @var string Column name
     */
    protected string $name;
    
    /**
     * @var string Column type
     */
    protected string $type;
    
    /**
     * @var array Column options
     */
    protected array $options;
    
    /**
     * Constructor
     *
     * @param string $name
     * @param string $type
     * @param array $options
     */
    public function __construct(string $name, string $type, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = array_merge([
            'nullable' => false,
            'default' => null,
            'unsigned' => false,
            'autoIncrement' => false,
            'primary' => false,
            'comment' => null
        ], $options);
    }
    
    /**
     * Generate the SQL definition
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = "`{$this->name}` {$this->type}";
        
        if ($this->options['unsigned'] && strpos($this->type, 'INT') !== false) {
            $sql .= ' UNSIGNED';
        }
        
        if ($this->options['nullable']) {
            $sql .= ' NULL';
        } else {
            $sql .= ' NOT NULL';
        }
        
        if ($this->options['default'] !== null) {
            if (strtoupper((string) $this->options['default']) === 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } elseif (is_string($this->options['default'])) {
                $sql .= " DEFAULT '" . addslashes($this->options['default']) . "'"; // Added addslashes for safety
            } else {
                $sql .= " DEFAULT {$this->options['default']}";
            }
        } elseif ($this->options['nullable']) {
            $sql .= " DEFAULT NULL";
        }
        
        if ($this->options['autoIncrement']) {
            $sql .= ' AUTO_INCREMENT';
        }

        // If this column is marked as primary in its options, add PRIMARY KEY here
        // This is important for AUTO_INCREMENT columns which must be a key.
        if ($this->options['primary']) {
            $sql .= ' PRIMARY KEY';
        }
        
        if ($this->options['comment'] !== null) {
            $sql .= " COMMENT '{$this->options['comment']}'";
        }
        
        return $sql;
    }
}
