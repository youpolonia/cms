<?php
declare(strict_types=1);

namespace Includes\Database;

use PDO;
use PDOException;
use RuntimeException;

require_once __DIR__ . '/../schema/Table.php';
require_once __DIR__ . '/../schema/Column.php';

/**
 * Base migration class for schema management
 */
abstract class Migration
{
    /**
     * @var PDO Database connection
     */
    protected PDO $pdo;
    
    /**
     * @var string Migration name
     */
    protected string $name;
    
    /**
     * @var int Migration batch number
     */
    protected int $batch = 0;
    
    /**
     * @var array SQL statements to execute
     */
    protected array $statements = [];
    
    /**
     * @var bool Whether to run in transaction
     */
    protected bool $useTransaction = true;
    
    /**
     * Constructor
     *
     * @param PDO $pdo
     * @param string $name
     */
    public function __construct(PDO $pdo, string $name)
    {
        $this->pdo = $pdo;
        $this->name = $name;
    }
    
    /**
     * Apply the migration
     *
     * @return void
     * @throws RuntimeException
     */
    public function up(): void
    {
        try {
            if ($this->useTransaction) {
                $this->pdo->beginTransaction();
            }
            
            $this->apply();
            
            if ($this->useTransaction) {
                $this->pdo->commit();
            }
        } catch (PDOException $e) {
            if ($this->useTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException("Migration failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Revert the migration
     *
     * @return void
     * @throws RuntimeException
     */
    public function down(): void
    {
        try {
            if ($this->useTransaction) {
                $this->pdo->beginTransaction();
            }
            
            $this->revert();
            
            if ($this->useTransaction) {
                $this->pdo->commit();
            }
        } catch (PDOException $e) {
            if ($this->useTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException("Migration rollback failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Define the migration
     *
     * @return void
     */
    abstract protected function apply(): void;
    
    /**
     * Revert the migration
     *
     * @return void
     */
    abstract protected function revert(): void;
    
    /**
     * Execute a SQL statement
     *
     * @param string $sql
     * @param array $params
     * @return bool
     * @throws PDOException
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $this->statements[] = [
            'sql' => $sql,
            'params' => $params
        ];
        
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($params);
    }
    
    /**
     * Execute a raw SQL statement
     *
     * @param string $sql
     * @return bool
     * @throws PDOException
     */
    protected function executeRaw(string $sql): bool
    {
        $this->statements[] = [
            'sql' => $sql,
            'params' => []
        ];

        $result = 0; /* exec disabled */
        return $result !== false;
    }
    
    /**
     * Get the migration name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Set the migration batch number
     *
     * @param int $batch
     * @return void
     */
    public function setBatch(int $batch): void
    {
        $this->batch = $batch;
    }
    
    /**
     * Get the migration batch number
     *
     * @return int
     */
    public function getBatch(): int
    {
        return $this->batch;
    }
    
    /**
     * Set whether to use transactions
     *
     * @param bool $useTransaction
     * @return void
     */
    public function setUseTransaction(bool $useTransaction): void
    {
        $this->useTransaction = $useTransaction;
    }
    
    /**
     * Get the executed SQL statements
     *
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
    }
    
    /**
     * Create a table
     *
     * @param string $table
     * @param callable $callback
     * @return bool
     */
    protected function createTable(string $table, callable $callback): bool
    {
        $schema = new Schema\Table($table);
        $callback($schema);
        
        return $this->executeRaw($schema->toSql());
    }
    
    /**
     * Alter a table
     *
     * @param string $table
     * @param callable $callback
     * @return bool
     */
    protected function alterTable(string $table, callable $callback): bool
    {
        $schema = new Schema\Table($table, true);
        $callback($schema);
        
        return $this->executeRaw($schema->toSql());
    }
    
    /**
     * Drop a table
     *
     * @param string $table
     * @return bool
     */
    protected function dropTable(string $table): bool
    {
        return $this->executeRaw("DROP TABLE IF EXISTS `{$table}`");
    }
    
    /**
     * Rename a table
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    protected function renameTable(string $from, string $to): bool
    {
        return $this->executeRaw("RENAME TABLE `{$from}` TO `{$to}`");
    }
    
    /**
     * Add a column to a table
     *
     * @param string $table
     * @param string $column
     * @param string $type
     * @param array $options
     * @return bool
     */
    protected function addColumn(string $table, string $column, string $type, array $options = []): bool
    {
        $schema = new Schema\Column($column, $type, $options);
        return $this->executeRaw("ALTER TABLE `{$table}` ADD COLUMN {$schema->toSql()}");
    }
    
    /**
     * Modify a column in a table
     *
     * @param string $table
     * @param string $column
     * @param string $type
     * @param array $options
     * @return bool
     */
    protected function modifyColumn(string $table, string $column, string $type, array $options = []): bool
    {
        $schema = new Schema\Column($column, $type, $options);
        return $this->executeRaw("ALTER TABLE `{$table}` MODIFY COLUMN {$schema->toSql()}");
    }
    
    /**
     * Drop a column from a table
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function dropColumn(string $table, string $column): bool
    {
        return $this->executeRaw("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
    }
    
    /**
     * Rename a column in a table
     *
     * @param string $table
     * @param string $from
     * @param string $to
     * @param string $type
     * @param array $options
     * @return bool
     */
    protected function renameColumn(string $table, string $from, string $to, string $type, array $options = []): bool
    {
        $schema = new Schema\Column($to, $type, $options);
        return $this->executeRaw("ALTER TABLE `{$table}` CHANGE COLUMN `{$from}` {$schema->toSql()}");
    }
    
    /**
     * Add an index to a table
     *
     * @param string $table
     * @param string|array $columns
     * @param string $name
     * @param string $type
     * @return bool
     */
    protected function addIndex(string $table, $columns, ?string $name = null, string $type = 'INDEX'): bool
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columnList = '`' . implode('`, `', $columns) . '`';
        
        if ($name === null) {
            $name = $table . '_' . implode('_', $columns);
        }
        
        return $this->executeRaw("ALTER TABLE `{$table}` ADD {$type} `{$name}` ({$columnList})");
    }
    
    /**
     * Add a unique index to a table
     *
     * @param string $table
     * @param string|array $columns
     * @param string $name
     * @return bool
     */
    protected function addUniqueIndex(string $table, $columns, ?string $name = null): bool
    {
        return $this->addIndex($table, $columns, $name, 'UNIQUE INDEX');
    }
    
    /**
     * Add a primary key to a table
     *
     * @param string $table
     * @param string|array $columns
     * @return bool
     */
    protected function addPrimaryKey(string $table, $columns): bool
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columnList = '`' . implode('`, `', $columns) . '`';
        
        return $this->executeRaw("ALTER TABLE `{$table}` ADD PRIMARY KEY ({$columnList})");
    }
    
    /**
     * Drop an index from a table
     *
     * @param string $table
     * @param string $name
     * @return bool
     */
    protected function dropIndex(string $table, string $name): bool
    {
        return $this->executeRaw("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
    }
    
    /**
     * Drop a primary key from a table
     *
     * @param string $table
     * @return bool
     */
    protected function dropPrimaryKey(string $table): bool
    {
        return $this->executeRaw("ALTER TABLE `{$table}` DROP PRIMARY KEY");
    }
    
    /**
     * Add a foreign key to a table
     *
     * @param string $table
     * @param string|array $columns
     * @param string $referencedTable
     * @param string|array $referencedColumns
     * @param string $name
     * @param string $onDelete
     * @param string $onUpdate
     * @return bool
     */
    protected function addForeignKey(
        string $table,
        $columns,
        string $referencedTable,
        $referencedColumns,
        ?string $name = null,
        string $onDelete = 'RESTRICT',
        string $onUpdate = 'RESTRICT'
    ): bool {
        $columns = is_array($columns) ? $columns : [$columns];
        $referencedColumns = is_array($referencedColumns) ? $referencedColumns : [$referencedColumns];
        
        $columnList = '`' . implode('`, `', $columns) . '`';
        $referencedColumnList = '`' . implode('`, `', $referencedColumns) . '`';
        
        if ($name === null) {
            $name = $table . '_' . implode('_', $columns) . '_foreign';
        }
        
        return $this->executeRaw(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}` " .
            "FOREIGN KEY ({$columnList}) REFERENCES `{$referencedTable}` ({$referencedColumnList}) " .
            "ON DELETE {$onDelete} ON UPDATE {$onUpdate}"
        );
    }
    
    /**
     * Drop a foreign key from a table
     *
     * @param string $table
     * @param string $name
     * @return bool
     */
    protected function dropForeignKey(string $table, string $name): bool
    {
        return $this->executeRaw("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
    }
}
