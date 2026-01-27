<?php
/**
 * Plugin Interface - Defines required plugin methods
 */
interface PluginInterface {
    /**
     * Get plugin metadata
     * @return array [name, version, author, description]
     */
    public static function getInfo(): array;

    /**
     * Plugin initialization
     * @param PluginManager $manager
     */
    public function init(PluginManager $manager): void;

    /**
     * Plugin activation
     */
    public function activate(): void;

    /**
     * Plugin deactivation 
     */
    public function deactivate(): void;

    /**
     * Plugin uninstall (cleanup)
     */
    public function uninstall(): void;
}
