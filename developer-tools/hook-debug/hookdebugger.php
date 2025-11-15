<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
/**
 * Hook Debugger - Real-time monitoring tool for CMS hooks and API calls
 */
class HookDebugger {
    private static $instance;
    private $hooks = [];
    private $apiCalls = [];
    private $filters = [
        'hook_type' => null,
        'priority' => null,
        'search_term' => null
    ];

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->registerSecurityMiddleware();
    }

    public function registerHookListener(string $hookName, callable $callback, int $priority = 10) {
        $this->hooks[$hookName][$priority][] = [
            'callback' => $callback,
            'timestamp' => microtime(true)
        ];
    }

    public function logApiCall(string $endpoint, array $data) {
        $this->apiCalls[] = [
            'endpoint' => $endpoint,
            'data' => $data,
            'timestamp' => microtime(true)
        ];
    }

    public function setFilter(string $type, $value) {
        if (array_key_exists($type, $this->filters)) {
            $this->filters[$type] = $value;
        }
    }

    public function getFilteredHooks() {
        return array_filter($this->hooks, function($hook) {
            // Apply filters here
            return true;
        });
    }

    public function getFilteredApiCalls() {
        return array_filter($this->apiCalls, function($call) {
            // Apply filters here
            return true;
        });
    }

    private function registerSecurityMiddleware() {
        require_once __DIR__.'/../securitymiddleware.php';
        SecurityMiddleware::registerDebugTool($this);
    }

    public function renderConsole() {
        $hooks = $this->getFilteredHooks();
        $apiCalls = $this->getFilteredApiCalls();
        
        ob_start();
        require_once __DIR__.'/debug-console.php';
        return ob_get_clean();
    }
}
