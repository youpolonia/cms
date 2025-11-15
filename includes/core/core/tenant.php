<?php

namespace CMS\Core;

class Tenant
{
    private $id;
    private $name;
    private $domain;
    private $config;
    private $isActive;
    private $storagePath;

    public function __construct($id, $name, $domain, array $config = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->domain = $domain;
        $this->config = $config;
        $this->isActive = true;
        $this->storagePath = "storage/tenants/{$id}";
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDomain() { return $this->domain; }
    public function getConfig() { return $this->config; }
    public function isActive() { return $this->isActive; }
    public function getStoragePath() { return $this->storagePath; }

    // Setters
    public function setName($name) { $this->name = $name; }
    public function setDomain($domain) { $this->domain = $domain; }
    public function setConfig(array $config) { $this->config = $config; }
    public function setActive(bool $active) { $this->isActive = $active; }

    // Configuration methods
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function mergeConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    // Isolation methods
    public function initializeStorage()
    {
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function validate()
    {
        return !empty($this->id) && !empty($this->domain);
    }
}
