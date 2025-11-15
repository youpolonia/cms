<?php
namespace CPT;

use Core\ModuleRegistry;
use Exception;

/**
 * CPT BuilderCore - Handles block registration and rendering for CPT module
 */
class BuilderCore {
    /**
     * @var array Registered blocks
     */
    private static $blocks = [];

    /**
     * Register blocks with the builder
     * @param array $blocks Array of block configurations
     * @throws Exception If block config validation fails
     */
    public static function registerBlocks(array $blocks): void {
        foreach ($blocks as $block) {
            if (!self::validateBlockConfig($block)) {
                throw new Exception("Invalid block configuration");
            }
            self::$blocks[$block['name']] = $block;
        }
    }

    /**
     * Render a registered block
     * @param string $blockName Name of block to render
     * @param array $attributes Block attributes
     * @return string Rendered HTML
     * @throws Exception If block not found
     */
    public static function renderBlock(string $blockName, array $attributes = []): string {
        if (!isset(self::$blocks[$blockName])) {
            throw new Exception("Block '$blockName' not registered");
        }

        $block = self::$blocks[$blockName];
        ob_start();
        require_once $block['template'];
        return ob_get_clean();
    }

    /**
     * Validate block configuration
     * @param array $config Block configuration
     * @return bool True if valid
     */
    public static function validateBlockConfig(array $config): bool {
        $required = ['name', 'label', 'template'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                return false;
            }
        }
        return file_exists($config['template']);
    }

    /**
     * Check if required dependencies are loaded
     * @throws Exception If dependencies missing
     */
    public static function checkDependencies(): void {
        if (!function_exists('ob_start')) {
            throw new Exception("Output buffering not available");
        }
    }
}
