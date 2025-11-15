<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
/**
 * Plugin Scaffold Generator
 * Creates plugin directory structure and files from templates
 */
class PluginScaffold {
    private $security;
    private $pluginDir;
    private $templateDir;
    
    public function __construct() {
        $this->security = new DeveloperToolsSecurity();
        $this->templateDir = __DIR__ . '/templates/';
        $this->ensureTemplateDir();
    }
    
    private function ensureTemplateDir(): void {
        if (!is_dir($this->templateDir)) {
            mkdir($this->templateDir, 0755, true);
        }
    }
    
    public function generate(array $config): bool {
        if (!$this->security->checkAccess()) {
            throw new RuntimeException('Access denied');
        }
        
        $this->validateConfig($config);
        $this->pluginDir = $this->getPluginDir($config['name']);
        
        try {
            $this->createPluginDir();
            $this->processTemplates($config);
            return true;
        } catch (Exception $e) {
            $this->cleanupOnFailure();
            throw $e;
        }
    }
    
    private function validateConfig(array $config): void {
        $required = ['name', 'version', 'author'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }
        
        if (!preg_match('/^[a-z0-9\-]+$/', $config['name'])) {
            throw new InvalidArgumentException('Plugin name must be lowercase alphanumeric with hyphens');
        }
    }
    
    private function getPluginDir(string $name): string {
        return dirname(__DIR__, 2) . "/plugins/$name/";
    }
    
    private function createPluginDir(): void {
        if (is_dir($this->pluginDir)) {
            throw new RuntimeException('Plugin directory already exists');
        }
        
        if (!mkdir($this->pluginDir, 0755, true)) {
            throw new RuntimeException('Failed to create plugin directory');
        }
    }
    
    private function processTemplates(array $config): void {
        $config['class_name'] = $this->generateClassName($config['name']);
        $config['date'] = date('Y-m-d');
        
        $files = [
            'config.json' => $this->processConfigTemplate($config),
            'src/' . $config['class_name'] . '.php' => $this->processTemplate('PluginClass.php.tpl', $config),
            'README.md' => $this->processTemplate('README.md.tpl', $config)
        ];
        
        foreach ($files as $filename => $content) {
            $dir = dirname($this->pluginDir . $filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($this->pluginDir . $filename, $content);
        }
    }
    
    private function generateClassName(string $name): string {
        $parts = explode('-', $name);
        $parts = array_map('ucfirst', $parts);
        return implode('', $parts);
    }
    
    private function processTemplate(string $template, array $config): string {
        $content = file_get_contents($this->templateDir . $template);
        $placeholders = [];
        
        foreach ($config as $key => $value) {
            $placeholders['{{' . $key . '}}'] = $this->security->sanitizeOutput($value);
        }
        
        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $content
        );
    }
    
    private function processConfigTemplate(array $config): string {
        $template = file_get_contents($this->templateDir . 'config.json.tpl');
        $placeholders = [
            '{{name}}' => $this->security->sanitizeOutput($config['name']),
            '{{version}}' => $this->security->sanitizeOutput($config['version']),
            '{{author}}' => $this->security->sanitizeOutput($config['author']),
            '{{description}}' => $this->security->sanitizeOutput($config['description'] ?? ''),
        ];
        
        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template
        );
    }
    
    private function cleanupOnFailure(): void {
        if (is_dir($this->pluginDir)) {
            array_map('unlink', glob("$this->pluginDir/*"));
            rmdir($this->pluginDir);
        }
    }
}
