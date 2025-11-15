<?php
/**
 * Component Registry for Page Builder
 * 
 * Manages available components and their configurations
 */
namespace CMS\Core\Builder;

class ComponentRegistry {
    /**
     * @var array Registered components
     */
    private static $components = [];

    /**
     * Register a new component type
     * @param string $type Component type identifier
     * @param array $config Component configuration
     * @throws \InvalidArgumentException
     */
    public static function register(string $type, array $config) {
        if (isset(self::$components[$type])) {
            throw new \InvalidArgumentException("Component type '$type' already registered");
        }

        // Validate required fields
        $required = ['name', 'category', 'fields'];
        foreach ($required as $field) {
            if (!isset($config[$field])) {
                throw new \InvalidArgumentException("Missing required field '$field'");
            }
        }

        self::$components[$type] = $config;
    }

    /**
     * Get all registered components
     * @return array
     */
    public static function all(): array {
        return self::$components;
    }

    /**
     * Get a specific component configuration
     * @param string $type Component type
     * @return array|null
     */
    public static function get(string $type): ?array {
        return self::$components[$type] ?? null;
    }

    /**
     * Check if a component type exists
     * @param string $type Component type
     * @return bool
     */
    public static function has(string $type): bool {
        return isset(self::$components[$type]);
    }

    /**
     * Get components by category
     * @param string $category
     * @return array
     */
    public static function byCategory(string $category): array {
        return array_filter(self::$components, function($comp) use ($category) {
            return $comp['category'] === $category;
        });
    }
}
