<?php
namespace Includes\Services;

use Includes\Interfaces\TenantConfigStorage;

class TenantConfig {
    private TenantConfigStorage $storage;

    public function __construct(TenantConfigStorage $storage) {
        $this->storage = $storage;
    }

    public function get(string $key, $default = null) {
        return $this->storage->get($key, $default);
    }

    public function set(string $key, $value): void {
        $this->storage->set($key, $value);
    }

    public function has(string $key): bool {
        return $this->storage->has($key);
    }

    public function delete(string $key): void {
        $this->storage->delete($key);
    }

    public function all(): array {
        return $this->storage->all();
    }
}
