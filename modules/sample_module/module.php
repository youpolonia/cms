<?php
/**
 * Sample Module Implementation
 */

class SampleModule {
    public static function init() {
        // Register module initialization
        add_action('cms_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes() {
        // Register module routes
        add_route('GET', '/sample', [self::class, 'index']);
    }

    public static function index() {
        return [
            'status' => 'success',
            'message' => 'Sample module is working!',
            'data' => [
                'version' => '1.0.0',
                'author' => 'CMS Developer'
            ]
        ];
    }
}

// Register module initialization hook
add_action('init', [SampleModule::class, 'init']);
