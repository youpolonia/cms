<?php

namespace Includes\Plugins;

require_once __DIR__ . '/../errorhandler.php';
require_once __DIR__ . '/../FileCache.php';

use Includes\ErrorHandler;
use Includes\FileCache;

class PluginSandboxV2 {
    private $pluginName;
    private $tenantId;
    private $violationLogger;
    private $ruleSet;
    private $defaultDenylist = [
        // File operations
        'file_', 'fopen', 'fwrite', 'fread', 'unlink', 'rename',
        // Network
        'curl_', 'fsockopen', 'socket_',
        // Execution
        'exec', 'passthru', 'system', 'shell_exec', 'proc_',
        // Dynamic code
        'eval', 'create_function', 'preg_replace_e', 'assert'
    ];

    public function __construct(string $pluginName, int $tenantId) {
        $this->pluginName = $pluginName;
        $this->tenantId = $tenantId;
        $this->violationLogger = new PluginViolationLogger();
        $this->ruleSet = new SandboxRuleSet($this->defaultDenylist);
    }

    public function execute(callable $code) {
        try {
            $result = $code();
            restore_error_handler();
            return $result;
        } catch (\Throwable $e) {
            $this->violationLogger->log(
                $this->pluginName,
                "Exception: " . $e->getMessage()
            );
            if ($this->ruleSet->fallbackMode) {
                return $this->executeInFallbackMode($code);
            }
            throw $e;
        }
    }

    public function setRules(array $rules): void {
        $this->ruleSet->update($rules);
    }

    public function getViolations(): array {
        return $this->violationLogger->getRecentViolations(10);
    }

    private function overrideFunctions(): void { /* no-op: function overrides are not supported in pure PHP */ }

    private function executeInFallbackMode(callable $code) {
        // Limited functionality execution
        return null;
    }
}

class PluginViolationLogger {
    private $logFile;

    public function __construct() {
        $this->logFile = config('plugins.log_path').'/violations.log';
    }

    public function log(string $plugin, string $violation): void {
        $entry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $plugin,
            $violation
        );
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }

    public function getRecentViolations(int $count = 10): array {
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        return array_slice($lines, -$count);
    }
}

class SandboxRuleSet {
    public $denylist;
    public $allowlist = [];
    public $fallbackMode = false;

    public function __construct(array $defaultDenylist) {
        $this->denylist = $defaultDenylist;
    }

    public function update(array $rules): void {
        if (isset($rules['denylist'])) {
            $this->denylist = array_merge($this->denylist, $rules['denylist']);
        }
        if (isset($rules['allowlist'])) {
            $this->allowlist = $rules['allowlist'];
        }
        if (isset($rules['fallbackMode'])) {
            $this->fallbackMode = (bool)$rules['fallbackMode'];
        }
    }
}
