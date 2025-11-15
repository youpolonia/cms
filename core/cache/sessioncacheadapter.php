<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../session_boot.php';
namespace Core\Cache;

class SessionCacheAdapter implements CacheInterface {
    private $cache;
    private $tenantId;
    private $sessionStarted = false;
    private $flashMessages = [];

    public function __construct(CacheInterface $cache, string $tenantId) {
        $this->cache = $cache;
        $this->tenantId = $tenantId;
    }

    public function set(string $tenantId, string $key, $value, ?int $ttl = null): bool {
        $this->ensureSessionStarted();
        return $this->cache->set($tenantId, $key, $value, $ttl);
    }

    public function get(string $tenantId, string $key) {
        $this->ensureSessionStarted();
        return $this->cache->get($tenantId, $key);
    }

    public function delete(string $tenantId, string $key): bool {
        $this->ensureSessionStarted();
        return $this->cache->delete($tenantId, $key);
    }

    public function clear(string $tenantId): bool {
        $this->ensureSessionStarted();
        return $this->cache->clear($tenantId);
    }

    public function has(string $tenantId, string $key): bool {
        $this->ensureSessionStarted();
        return $this->cache->has($tenantId, $key);
    }

    private function ensureSessionStarted(): void {
        if (!$this->sessionStarted && session_status() === PHP_SESSION_NONE) {
            $this->sessionStarted = cms_session_start('public');
        }
    }

    // Session-specific functionality
    public function getSessionId(): ?string {
        return session_id();
    }

    public function regenerateId(bool $deleteOldSession = true): bool {
        return session_regenerate_id($deleteOldSession);
    }

    public function setFlash(string $key, $value): void {
        $this->flashMessages[$key] = $value;
        $this->set($this->tenantId, '_flash_'.$key, $value);
    }

    public function getFlash(string $key) {
        $value = $this->get($this->tenantId, '_flash_'.$key);
        $this->delete($this->tenantId, '_flash_'.$key);
        return $value ?? $this->flashMessages[$key] ?? null;
    }

    public function setCsrfToken(): string {
        $token = bin2hex(random_bytes(32));
        $this->set($this->tenantId, '_csrf_token', $token);
        return $token;
    }

    public function validateCsrfToken(string $token): bool {
        $storedToken = $this->get($this->tenantId, '_csrf_token');
        return hash_equals($storedToken ?? '', $token);
    }

    // Backward compatibility with direct $_SESSION access
    public function __get(string $key) {
        return $this->get($this->tenantId, $key);
    }

    public function __set(string $key, $value) {
        $this->set($this->tenantId, $key, $value);
    }

    public function __isset(string $key): bool {
        return $this->has($this->tenantId, $key);
    }

    public function __unset(string $key) {
        $this->delete($this->tenantId, $key);
    }
}
