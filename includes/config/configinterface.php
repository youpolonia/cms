<?php

declare(strict_types=1);

namespace Includes\Config;

interface ConfigInterface
{
    /**
     * Get configuration value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Check if configuration key exists
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get all configuration values
     * @return array
     */
    public function all(): array;

    /**
     * Set configuration value
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl Time to live in seconds
     * @return void
     */
    public function set(string $key, $value, ?int $ttl = null): void;

    /**
     * Clear configuration value
     * @param string $key
     * @return void
     */
    public function clear(string $key): void;
}
