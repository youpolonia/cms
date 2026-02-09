<?php
/**
 * Module Registry
 * Manages registration and retrieval of JTB modules
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Registry
{
    private static array $modules = [];
    private static bool $initialized = false;

    /**
     * Initialize the registry
     */
    public static function init(): void
    {
        self::$initialized = true;
    }

    /**
     * Register a module
     */
    public static function register(string $slug, string $className): void
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("Module class '{$className}' does not exist");
        }

        self::$modules[$slug] = $className;
    }

    /**
     * Get a module instance by slug
     * Normalizes slug: converts hyphens to underscores for compatibility
     * (AI may generate 'site-logo' but module is registered as 'site_logo')
     */
    public static function get(string $slug): ?JTB_Element
    {
        // Normalize slug: convert hyphens to underscores
        $normalizedSlug = str_replace('-', '_', $slug);

        // Try normalized slug first, then original
        $lookupSlug = isset(self::$modules[$normalizedSlug]) ? $normalizedSlug : $slug;

        if (!isset(self::$modules[$lookupSlug])) {
            return null;
        }

        $className = self::$modules[$lookupSlug];
        return new $className();
    }

    /**
     * Get all registered modules
     */
    public static function all(): array
    {
        return self::$modules;
    }

    /**
     * Get all module instances
     */
    public static function getInstances(): array
    {
        $instances = [];

        foreach (self::$modules as $slug => $className) {
            $instances[$slug] = new $className();
        }

        return $instances;
    }

    /**
     * Get modules by category
     */
    public static function getByCategory(string $category): array
    {
        $filtered = [];

        foreach (self::$modules as $slug => $className) {
            $instance = new $className();
            if ($instance->category === $category) {
                $filtered[$slug] = $instance;
            }
        }

        return $filtered;
    }

    /**
     * Get category definitions
     */
    public static function getCategories(): array
    {
        return [
            'structure' => [
                'label' => 'Structure',
                'icon' => 'layout'
            ],
            'content' => [
                'label' => 'Content',
                'icon' => 'text'
            ],
            'media' => [
                'label' => 'Media',
                'icon' => 'image'
            ],
            'interactive' => [
                'label' => 'Interactive',
                'icon' => 'toggle'
            ],
            'forms' => [
                'label' => 'Forms',
                'icon' => 'form'
            ],
            'blog' => [
                'label' => 'Blog',
                'icon' => 'blog'
            ],
            'fullwidth' => [
                'label' => 'Fullwidth',
                'icon' => 'fullwidth'
            ]
        ];
    }

    /**
     * Check if module exists
     * Normalizes slug: converts hyphens to underscores for compatibility
     */
    public static function exists(string $slug): bool
    {
        // Normalize slug: convert hyphens to underscores
        $normalizedSlug = str_replace('-', '_', $slug);

        return isset(self::$modules[$slug]) || isset(self::$modules[$normalizedSlug]);
    }

    /**
     * Get module count
     */
    public static function count(): int
    {
        return count(self::$modules);
    }
}
