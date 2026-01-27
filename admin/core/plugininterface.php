<?php
/**
 * Plugin Interface
 * Defines the standard structure for all plugins
 */
interface PluginInterface
{
    /**
     * Activate the plugin
     */
    public function activate(): void;

    /**
     * Deactivate the plugin
     */
    public function deactivate(): void;

    /**
     * Get plugin settings
     * @return array Key-value pairs of settings
     */
    public function getSettings(): array;

    /**
     * Register plugin hooks
     * @param HookManager $hookManager
     */
    public function registerHooks(HookManager $hookManager): void;

    /**
     * Get plugin metadata
     * @return array {
     *     @type string $name
     *     @type string $version
     *     @type string $author
     *     @type string $description
     * }
     */
    public function getMetadata(): array;

    /**
     * Check if plugin meets requirements
     * @throws PluginRequirementException If requirements not met
     */
    public function checkRequirements(): void;
}
