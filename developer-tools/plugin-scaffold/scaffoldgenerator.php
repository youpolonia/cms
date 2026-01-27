<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
/**
 * Plugin Scaffold Generator
 * Creates standardized plugin structure
 */
class PluginScaffoldGenerator {
    private $templateDir;
    private $outputDir;
    private $security;

    public function __construct() {
        $this->templateDir = __DIR__ . '/templates/';
        $this->outputDir = __DIR__ . '/../../plugins/';
        require_once __DIR__ . '/../securitymiddleware.php';
        $this->security = new DeveloperToolsSecurity();
    }

    public function generate(string $pluginName, array $options = []): bool {
        if (!$this->security->checkAccess()) {
            return false;
        }

        $pluginDir = $this->outputDir . $pluginName . '/';
        if (file_exists($pluginDir)) {
            return false;
        }

        mkdir($pluginDir, 0755, true);

        // Process template files
        $templates = [
            'plugin.php' => [
                'PLUGIN_NAME' => $pluginName,
                'AUTHOR' => $options['author'] ?? 'Your Name',
                'DESCRIPTION' => $options['description'] ?? 'A custom plugin'
            ],
            'config.json' => [
                'VERSION' => $options['version'] ?? '1.0.0'
            ]
        ];

        foreach ($templates as $file => $replacements) {
            $template = file_get_contents($this->templateDir . $file . '.tpl');
            foreach ($replacements as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }
            file_put_contents($pluginDir . $file, $template);
        }

        return true;
    }
}
