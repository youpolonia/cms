<?php

namespace Includes\Database;

use PDO;

/**
 * Base Model class providing CRUD operations and relationships
 */
abstract class Model
{
    /**
     * @var string The table associated with the model
     */
    protected static string $table;

    /**
     * @var string The primary key for the model (default 'id')
     */
    protected static string $primaryKey = 'id';

    /**
     * @var array The model's attributes
     */
    protected array $attributes = [];

    /**
     * @var array Validation rules
     */
    protected static array $rules = [];

    /**
     * @var array Validation error messages
     */
    protected array $errors = [];

    /**
     * Create new model instance
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Fill the model with attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Set an attribute on the model
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get an attribute from the model
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Get the table name for the model
     *
     * @return string
     */
    public static function getTable(): string
    {
        return static::$table;
    }

    /**
     * Start a new query
     *
     * @return QueryBuilder
     */
    public static function query(): QueryBuilder
    {
        return (new QueryBuilder())->table(static::getTable());
    }

    /**
     * Find a model by primary key
     *
     * @param int|string $id
     * @return static|null
     */
    public static function find($id): ?self
    {
        $result = static::query()
            ->where(static::$primaryKey, '=', $id)
            ->limit(1)
            ->get();

        return $result ? new static($result[0]) : null;
    }

    /**
     * Save the model to database
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $primaryKey = static::$primaryKey;
        $id = $this->getAttribute($primaryKey);

        if ($id) {
            $affected = static::query()
                ->where($primaryKey, '=', $id)
                ->update($this->attributes);
            return $affected > 0;
        }

        $id = static::query()->insert($this->attributes);
        $this->setAttribute($primaryKey, $id);
        return $id !== null;
    }

    /**
     * Delete the model from database
     *
     * @return bool
     */
    public function delete(): bool
    {
        $primaryKey = static::$primaryKey;
        $id = $this->getAttribute($primaryKey);

        if (!$id) {
            return false;
        }

        $affected = static::query()
            ->where($primaryKey, '=', $id)
            ->delete();

        return $affected > 0;
    }

    /**
     * Validate model attributes
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->errors = [];
        $rules = static::$rules;

        foreach ($rules as $field => $rule) {
            $value = $this->getAttribute($field);
            $this->validateField($field, $value, $rule);
        }

        return empty($this->errors);
    }

    /**
     * Validate a single field
     *
     * @param string $field
     * @param mixed $value
     * @param string $rule
     */
    protected function validateField(string $field, $value, string $rule): void
    {
        $valid = true;

        switch ($rule) {
            case 'required':
                $valid = !empty($value);
                break;
            case 'email':
                $valid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                break;
            case 'numeric':
                $valid = is_numeric($value);
                break;
            // Add more validation rules as needed
        }

        if (!$valid) {
            $this->errors[$field] = "Validation failed for {$field} with rule {$rule}";
        }
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Define a one-to-one relationship
     *
     * @param string $related
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return mixed
     */
    public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? static::$primaryKey;

        return $related::query()
            ->where($foreignKey, '=', $this->getAttribute($localKey))
            ->limit(1)
            ->get()[0] ?? null;
    }

    /**
     * Define a one-to-many relationship
     *
     * @param string $related
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return array
     */
    public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): array
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? static::$primaryKey;

        return $related::query()
            ->where($foreignKey, '=', $this->getAttribute($localKey))
            ->get();
    }

    /**
     * Get the foreign key for the model
     *
     * @return string
     */
    protected function getForeignKey(): string
    {
        $className = basename(str_replace('\\', '/', static::class));
        return strtolower($className) . '_id';
    }
}
