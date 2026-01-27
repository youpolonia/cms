<?php

interface ConfigStorageInterface
{
    public function load(): array;
    public function save(array $config): bool;
}

class FileConfigStorage implements ConfigStorageInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function load(): array
    {
        if (!file_exists($this->path)) {
            throw new RuntimeException("Config file not found: {$this->path}");
        }

        $config = require_once $this->path;
        return is_array($config) ? $config : [];
    }

    public function save(array $config): bool
    {
        $content = "<?php\nreturn " . var_export($config, true) . ";\n";
        return file_put_contents($this->path, $content) !== false;
    }
}

class DatabaseConfigStorage implements ConfigStorageInterface
{
    private \PDO $db;
    private string $table = 'system_config';
    private string $keyColumn = 'config_key';
    private string $valueColumn = 'config_value';

    public function __construct(\PDO $connection, ?string $table = null)
    {
        $this->db = $connection;
        if ($table !== null) {
            $this->table = $table;
        }
    }

    public function load(): array
    {
        $stmt = $this->db->prepare("SELECT {$this->keyColumn}, {$this->valueColumn} FROM {$this->table}");
        $stmt->execute();
        
        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row[$this->keyColumn]] = json_decode($row[$this->valueColumn], true);
        }
        
        return $config;
    }

    public function save(array $config): bool
    {
        $this->db->beginTransaction();
        
        try {
            // Clear existing config
            $this->db->exec("DELETE FROM {$this->table}");
            
            // Insert new config
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} ({$this->keyColumn}, {$this->valueColumn}) VALUES (?, ?)"
            );
            
            foreach ($config as $key => $value) {
                $stmt->execute([$key, json_encode($value)]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to save config: " . $e->getMessage());
        }
    }
}

/**
 * Configuration Service - Centralized configuration management
 *
 * Handles all system configuration operations including:
 * - Loading configuration from various sources
 * - Merging configurations
 * - Providing typed access to config values
 * - Managing configuration overrides
 */
class ConfigurationService
{
    /**
     * @var array $config Store for loaded configurations
     */
    private array $config = [];
    private ?ConfigStorageInterface $storage = null;
    private ?array $cache = null;

    /**
     * Load configuration from file
     * 
     * @param string $path Path to config file
     * @throws RuntimeException If file cannot be read
     */
    public function __construct(?ConfigStorageInterface $storage = null)
    {
        $this->storage = $storage;
    }

    public function loadFromFile(string $path): void
    {
        $this->storage = new FileConfigStorage($path);
        $this->config = $this->storage->load();
    }

    /**
     * Get configuration value by dot notation key
     * 
     * @param string $key Configuration key (e.g. 'worker.heartbeat')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get(string $key, mixed $default = null, ?string $type = null): mixed
    {
        if ($this->cache !== null && array_key_exists($key, $this->cache)) {
            return $this->castValue($this->cache[$key], $type);
        }

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $this->castValue($default, $type);
            }
            $value = $value[$k];
        }

        return $this->castValue($value, $type);
    }

    public function getAsInt(string $key, ?int $default = null): ?int
    {
        return $this->get($key, $default, 'int');
    }

    public function getAsBool(string $key, ?bool $default = null): ?bool
    {
        return $this->get($key, $default, 'bool');
    }

    public function getAsString(string $key, ?string $default = null): ?string
    {
        return $this->get($key, $default, 'string');
    }

    public function getAsFloat(string $key, ?float $default = null): ?float
    {
        return $this->get($key, $default, 'float');
    }

    public function getAsArray(string $key, ?array $default = null): ?array
    {
        return $this->get($key, $default, 'array');
    }

    private function castValue(mixed $value, ?string $type): mixed
    {
        if ($type === null) {
            return $value;
        }

        return match ($type) {
            'bool' => (bool)$value,
            'int' => (int)$value,
            'float' => (float)$value,
            'string' => (string)$value,
            'array' => (array)$value,
            default => $value
        };
    }

    /**
     * Set configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Value to set
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$this->config;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
        
        if ($this->storage) {
            $this->storage->save($this->config);
        }

        if ($this->cache !== null) {
            $this->cache[$key] = $value;
        }
    }

    public function enableCache(bool $enabled = true): void
    {
        $this->cache = $enabled ? [] : null;
    }

    /**
     * Merge additional configuration
     * 
     * @param array $config Configuration array to merge
     * @param bool $overwrite Whether to overwrite existing values
     */
    public function merge(array $config, bool $overwrite = true): void
    {
        if ($overwrite) {
            $this->config = array_replace_recursive($this->config, $config);
        } else {
            $this->config = array_replace_recursive($config, $this->config);
        }
    }
}
