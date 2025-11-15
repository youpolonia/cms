<?php

namespace Includes\Core;

class ServiceContainer
{
    protected $services = [];
    protected $factories = [];
    protected $shared = [];

    public function set(string $name, $service, bool $shared = true): void
    {
        $this->services[$name] = $service;
        $this->shared[$name] = $shared;
    }

    public function factory(string $name, callable $factory, bool $shared = true): void
    {
        $this->factories[$name] = $factory;
        $this->shared[$name] = $shared;
    }

    public function get(string $name)
    {
        if (isset($this->services[$name]) && $this->shared[$name]) {
            return $this->services[$name];
        }

        if (isset($this->factories[$name])) {
            $service = call_user_func($this->factories[$name], $this);
            
            if ($this->shared[$name]) {
                $this->services[$name] = $service;
            }
            
            return $service;
        }

        throw new \RuntimeException("Service {$name} not found");
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]) || isset($this->factories[$name]);
    }
}
