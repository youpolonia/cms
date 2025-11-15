<?php
namespace Core;

class PluginSandbox {
    private $pluginPath;
    private $bufferLevel = 0;

    public function __construct(string $pluginPath) {
        $this->pluginPath = $pluginPath;
    }

    public function execute(): void {
        $this->bufferLevel = ob_get_level();
        ob_start();

        try {
            $this->runIsolated();
        } catch (\Throwable $e) {
            $this->cleanBuffers();
            error_log("PluginSandbox error: " . $e->getMessage());
            throw $e;
        }

        $output = ob_get_clean();
        if (!empty($output)) {
            echo $this->filterOutput($output);
        }
    }

    private function runIsolated(): void {
        $pluginFile = $this->pluginPath . '/plugin.php';
        if (!file_exists($pluginFile)) {
            throw new \RuntimeException("Plugin file not found");
        }

        // Isolated scope execution
        $execute = function() use ($pluginFile) {
            $allowedFunctions = ['strlen', 'str_replace', 'preg_match'];
            $this->overrideDangerousFunctions($allowedFunctions);
            require_once $pluginFile;
        };

        $execute();
    }

    private function overrideDangerousFunctions(array $allowed): void {
        $dangerous = [
            'exec', 'system', 'shell_exec', 'passthru',
            'eval', 'create_function', 'require_once', 'require',
            'exit', 'die'
        ];

        foreach ($dangerous as $func) {
            if (!in_array($func, $allowed)) {
                override_function($func, '', 'return false;');
            }
        }
    }

    private function filterOutput(string $output): string {
        // Basic XSS protection
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }

    private function cleanBuffers(): void {
        while (ob_get_level() > $this->bufferLevel) {
            ob_end_clean();
        }
    }
}
