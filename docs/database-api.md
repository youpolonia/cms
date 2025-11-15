# Database Layer API Reference

## DatabaseConnection

```php
/**
 * Creates database connection instance
 * @param array $config Connection configuration
 */
public function __construct(array $config)

/**
 * Gets a connection from the pool
 * @return PDO
 */
public function getConnection()

/**
 * Releases connection back to pool
 * @param PDO $connection
 */
public function releaseConnection(PDO $connection)
```

## QueryBuilder

```php
/**
 * Creates new query builder instance
 * @param DatabaseConnection $connection
 */
public function __construct(DatabaseConnection $connection)

/**
 * Executes SELECT query
 * @return array
 */
public function get()

/**
 * Executes INSERT query
 * @return int Last insert ID
 */
public function insert(array $data)

/**
 * Executes UPDATE query
 * @return int Affected rows
 */
public function update(array $data)

/**
 * Executes DELETE query
 * @return int Affected rows
 */
public function delete()
```

## Model

```php
/**
 * Finds record by ID
 * @return Model|null
 */
public static function find(int $id)

/**
 * Saves model changes
 * @return bool
 */
public function save()

/**
 * Deletes model
 * @return bool
 */
public function delete()

/**
 * Defines hasOne relationship
 * @return Model|null
 */
public function hasOne(string $modelClass, string $foreignKey)
```

## Migrations

```php
/**
 * Runs migration up
 */
abstract public function up()

/**
 * Runs migration down
 */
abstract public function down()