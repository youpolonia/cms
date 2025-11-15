<?php
namespace Includes\Interfaces;

interface TenantConfigStorage {
    public function get(string $key, $default = null);
    public function set(string $key, $value): void;
    public function has(string $key): bool;
    public function delete(string $key): void;
    public function all(): array;
}
