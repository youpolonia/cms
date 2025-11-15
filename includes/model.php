<?php
/**
 * Base Model Class
 */
require_once __DIR__ . '/../core/database.php';
abstract class Model {
    protected static $table;
    protected static $primaryKey = 'id';
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function getAttribute($key) {
        return $this->attributes[$key] ?? null;
    }

    public function save() {
        $db = \core\Database::connection();
        
        if ($this->exists) {
            return $this->performUpdate($db);
        }
        return $this->performInsert($db);
    }

    protected function performUpdate(Database $db) {
        $attributes = $this->getDirty();
        if (empty($attributes)) {
            return true;
        }

        $columns = [];
        $values = [];
        foreach ($attributes as $key => $value) {
            $columns[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $this->getKey();

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = ?',
            static::$table,
            implode(', ', $columns),
            static::$primaryKey
        );

        $stmt = $db->query($sql, $values);
        return $stmt->rowCount() > 0;
    }

    protected function performInsert(Database $db) {
        $attributes = $this->getAttributes();
        unset($attributes[static::$primaryKey]);

        $columns = array_keys($attributes);
        $placeholders = array_fill(0, count($columns), '?');
        $values = array_values($attributes);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $db->query($sql, $values);
        $this->setAttribute(static::$primaryKey, $db->getLastInsertId());
        $this->exists = true;
        return true;
    }

    public function delete() {
        if (!$this->exists) {
            return false;
        }

        $db = \core\Database::connection();
        $sql = sprintf(
            'DELETE FROM %s WHERE %s = ?',
            static::$table,
            static::$primaryKey
        );

        $stmt = $db->query($sql, [$this->getKey()]);
        $this->exists = false;
        return $stmt->rowCount() > 0;
    }

    public static function find($id) {
        $db = \core\Database::connection();
        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = ? LIMIT 1',
            static::$table,
            static::$primaryKey
        );

        $stmt = $db->query($sql, [$id]);
        $result = $stmt->fetch();

        if ($result) {
            $model = new static($result);
            $model->exists = true;
            $model->original = $result;
            return $model;
        }

        return null;
    }

    public static function all() {
        $db = \core\Database::connection();
        $sql = sprintf('SELECT * FROM %s', static::$table);
        $stmt = $db->query($sql);
        return array_map(function($row) {
            $model = new static($row);
            $model->exists = true;
            $model->original = $row;
            return $model;
        }, $stmt->fetchAll());
    }

    public function getKey() {
        return $this->attributes[static::$primaryKey] ?? null;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function getDirty() {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    public function __get($key) {
        return $this->getAttribute($key);
    }

    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }
}
