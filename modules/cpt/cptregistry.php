<?php
/**
 * CPT Registry - Manages custom post type definitions
 */
class CPTRegistry {
    private static $instance;
    private $post_types = [];

    private function __construct() {}

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a new post type
     * @param array $config Post type configuration
     */
    public function register(array $config) {
        $name = $config['name'] ?? null;
        if (!$name) {
            throw new InvalidArgumentException('Post type name is required');
        }

        $this->post_types[$name] = [
            'label' => $config['label'] ?? ucfirst($name),
            'description' => $config['description'] ?? '',
            'fields' => $config['fields'] ?? [],
            'supports' => $config['supports'] ?? ['title'],
            'taxonomies' => $config['taxonomies'] ?? []
        ];
    }

    /**
     * Get post type definition
     */
    public function get(string $name): ?array {
        return $this->post_types[$name] ?? null;
    }

    /**
     * Get all registered post types
     */
    public function getAll(): array {
        return $this->post_types;
    }
}
