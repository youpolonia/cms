<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../includes/services/remotefetcher.php';
require_once __DIR__ . '/../../includes/services/plugincompatibilitychecker.php';

class PluginMarketplaceController {
    private $remoteFetcher;
    private $compatibilityChecker;
    private $indexUrl = 'https://plugins.cms.example.com/index.json';

    public function __construct() {
        $this->remoteFetcher = new RemoteFetcher();
        $this->compatibilityChecker = new PluginCompatibilityChecker();
    }

    public function index() {
        $plugins = $this->remoteFetcher->fetch($this->indexUrl);
        
        if (!$plugins) {
            return $this->renderError('Could not fetch plugin index');
        }

        // Check compatibility for each plugin
        foreach ($plugins['plugins'] as &$plugin) {
            $plugin['compatibility_errors'] = $this->compatibilityChecker->check($plugin);
        }

        return $this->render('marketplace', [
            'plugins' => $plugins['plugins'],
            'categories' => $plugins['categories'] ?? []
        ]);
    }

    private function render($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/plugins/{$view}.php";
    }

    private function renderError($message) {
        require_once __DIR__ . '/../views/plugins/error.php';
    }
}
