<?php
/**
 * Abstract Plugin
 * Provides common functionality for all plugins
 */
abstract class AbstractPlugin implements PluginInterface
{
    protected string $pluginPath;
    protected array $settings = [];
    protected array $metadata = [];

    public function __construct(string $pluginPath)
    {
        $this->pluginPath = $pluginPath;
        $this->loadMetadata();
        $this->loadSettings();
    }

    protected function loadMetadata(): void
    {
        $metadataFile = $this->pluginPath . '/plugin.json';
        if (file_exists($metadataFile)) {
            $this->metadata = json_decode(file_get_contents($metadataFile), true);
        }
    }

    protected function loadSettings(): void
    {
        $settingsFile = $this->pluginPath . '/settings.json';
        if (file_exists($settingsFile)) {
            $this->settings = json_decode(file_get_contents($settingsFile), true);
        }
    }

    public function activate(): void
    {
        // Default activation logic
    }

    public function deactivate(): void
    {
        // Default deactivation logic
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function registerHooks(HookManager $hookManager): void
    {
        // Default hook registration
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function checkRequirements(): void
    {
        // Default requirement checks
    }

    protected function saveSettings(): bool
    {
        return file_put_contents(
            $this->pluginPath . '/settings.json',
            json_encode($this->settings, JSON_PRETTY_PRINT)
        ) !== false;
    }
}
