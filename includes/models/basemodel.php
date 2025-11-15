<?php

namespace Includes\Models;

use Includes\Database\Connection;
use Includes\Database\QueryBuilder;
use Includes\Database\RelationshipBuilder;
use Includes\Database\TenantScope;

abstract class BaseModel
{
    protected static $connection;
    protected static $table;
    protected static $primaryKey = 'id';
    protected static $columnWhitelist = [];
    protected $attributes = [];

    // Instance-based operations
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function save(): bool
    {
        if (isset($this->attributes[static::$primaryKey])) {
            return $this->update();
        }
        return $this->insert();
    }

    protected function insert(): bool
    {
        $id = static::query()->insert($this->attributes);
        if ($id) {
            $this->attributes[static::$primaryKey] = $id;
            return true;
        }
        return false;
    }

    protected function update(): bool
    {
        return static::query()
            ->where(static::$primaryKey, $this->attributes[static::$primaryKey])
            ->update($this->attributes);
    }

    public function delete(): bool
    {
        return static::query()
            ->where(static::$primaryKey, $this->attributes[static::$primaryKey])
            ->delete();
    }

    // Static query operations
    protected static function query(): QueryBuilder
    {
        $builder = new QueryBuilder(static::$table);
        if (self::$connection) {
            $builder->setConnection(self::$connection);
        }
        return $builder;
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find($id): ?static
    {
        return static::query()->where(static::$primaryKey, $id)->first();
    }

    // Relationships
    protected static function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): RelationshipBuilder
    {
        $relatedModel = new $related();
        return new RelationshipBuilder(
            'hasOne',
            static::getTable(),
            $relatedModel::getTable(),
            $foreignKey,
            $localKey
        );
    }

    protected static function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): RelationshipBuilder
    {
        $relatedModel = new $related();
        return new RelationshipBuilder(
            'hasMany',
            static::getTable(),
            $relatedModel::getTable(),
            $foreignKey,
            $localKey
        );
    }

    protected static function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): RelationshipBuilder
    {
        $relatedModel = new $related();
        return new RelationshipBuilder(
            'belongsTo',
            static::getTable(),
            $relatedModel::getTable(),
            $foreignKey,
            $ownerKey
        );
    }

    // Existing preserved methods
    public static function setConnection(Connection $connection)
    {
        self::$connection = $connection;
    }

    public static function getTable(): string
    {
        if (!static::$table) {
            throw new \RuntimeException('Model table name not defined');
        }
        return static::$table;
    }

    protected static function validateColumn(string $column): bool
    {
        if (!empty(static::$columnWhitelist)) {
            return in_array($column, static::$columnWhitelist);
        }
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column);
    }

    protected static function validateInOperator(array $values): void
    {
        if (count($values) > 100) {
            throw new \InvalidArgumentException('IN operator cannot accept more than 100 values');
        }
        
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                throw new \InvalidArgumentException('IN operator values must be scalar');
            }
        }
    }

    protected static function addTenantScope()
    {
        // Implementation depends on TenantScope class
    }
}
