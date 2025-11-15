<?php
/**
 * CMS Plugin Interface
 * Defines required methods for all plugins
 */
interface PluginInterface {
    /**
     * Get plugin metadata
     * @return array Contains keys: name, version, author, description
     */
    public function getMetadata(): array;

    /**
     * Initialize plugin
     * @return void
     */
    public function init(): void;

    /**
     * Register plugin hooks
     * @return void
     */
    public function registerHooks(): void;
}
