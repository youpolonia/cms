<?php

namespace Includes\Services;

class EventDispatcher
{
    protected array $listeners = [];
    protected ?int $tenantId = null;

    public function __construct(?int $tenantId = null)
    {
        $this->tenantId = $tenantId;
    }

    public function addListener(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(string $eventName, array $payload = []): void
    {
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                $listener(array_merge($payload, ['tenant_id' => $this->tenantId]));
            }
        }
    }

    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }
}
