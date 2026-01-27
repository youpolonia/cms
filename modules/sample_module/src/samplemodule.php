<?php
namespace SampleModule;

/**
 * Sample Module Main Class
 */
class SampleModule {
    public function __construct() {
        // Register hooks
        add_action('init', [$this, 'init']);
        add_action('cms_init', [$this, 'registerRoutes']);
    }

    public function init() {
        // Module initialization logic
        $this->registerHooks();
    }

    public function registerRoutes() {
        // Route registration
        add_route('GET', '/sample', [$this, 'handleSampleRequest']);
    }

    public function registerHooks() {
        // Hook registrations
        add_filter('content_filter', [$this, 'filterContent']);
    }

    public function handleSampleRequest() {
        return [
            'status' => 'success',
            'message' => 'Sample module endpoint',
            'data' => [
                'version' => '1.0.0',
                'timestamp' => time()
            ]
        ];
    }

    public function filterContent(string $content): string {
        return str_replace('{sample}', 'Sample Module Replacement', $content);
    }
}
