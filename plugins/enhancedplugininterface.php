<?php
/**
 * Enhanced Plugin Interface with:
 * - Dependency management
 * - Lifecycle hooks
 * - Version compatibility
 * - Standardized hook points
 */
interface EnhancedPluginInterface extends PluginInterface {
    /**
     * Get plugin dependencies
     * @return array Array of required plugins [plugin_name => min_version]
     */
    public function getDependencies(): array;

    /**
     * Check CMS version compatibility
     * @return array [min_version, max_version] or empty array if no restrictions
     */
    public function getVersionCompatibility(): array;

    /**
     * Plugin installation routine
     */
    public function install(): void;

    /**
     * Plugin activation routine
     */
    public function activate(): void;

    /**
     * Plugin deactivation routine
     */
    public function deactivate(): void;

    /**
     * Plugin uninstallation routine
     */
    public function uninstall(): void;

    /**
     * Get available hook points with priorities
     * @return array [hook_name => priority]
     */
    public function getHookPoints(): array;
}
