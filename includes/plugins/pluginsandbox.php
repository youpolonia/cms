<?php

namespace Includes\Plugins;

require_once __DIR__ . '/../errorhandler.php';
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../database/tenantcontext.php';
use Includes\ErrorHandler;
use Includes\Config;
use Includes\Database\TenantContext;

class PluginSandbox {
    private $restrictedFunctions;
    private $allowedFunctions = [
        'strlen', 'strpos', 'substr', 'trim',
        'array_merge', 'array_keys', 'in_array',
        'date', 'time', 'strtotime'
    ];

    private $tenantId;
    private $pluginName;
    private $memoryLimit;
    private $timeLimit;
    private $allowedPaths = [];

    public function __construct(string $pluginName, ?string $tenantId = null) {
        $this->restrictedFunctions = [];
        $this->memoryLimit = '128M';
        $this->timeLimit = 30;
        $this->pluginName = $pluginName;
        $this->tenantId = $tenantId ?? TenantContext::getCurrentTenantId();
        
        // Set allowed paths for this plugin
        $this->allowedPaths = [
            CMS_ROOT . "/plugins/$pluginName",
            CMS_ROOT . "/public/plugins/$pluginName"
        ];
    }

    public function execute(callable $callback, array $args = []) {
        $this->setupEnvironment();
        try {
            return call_user_func_array($callback, $args);
        } finally {
            $this->restoreEnvironment();
        }
    }

    public function evaluate(string $code) {
        $this->setupEnvironment();
        
        try {
            error_log('PluginSandbox eval disabled');
            return false;
        } finally {
            $this->restoreEnvironment();
        }
    }

    private function setupEnvironment(): void {
        // Set resource limits
        ini_set('memory_limit', $this->memoryLimit);
        set_time_limit($this->timeLimit);

        // Register shutdown function to catch fatal errors
        register_shutdown_function([$this, 'handleShutdown']);

        // Set tenant context if available
        if ($this->tenantId) {
            TenantContext::setCurrentTenantId($this->tenantId);
        }

        // Override dangerous functions
        $this->overrideFunctions();
    }

    private function restoreEnvironment(): void {
        // Restore original memory limit
        ini_restore('memory_limit');
        set_time_limit(0);
    }

    private function overrideFunctions(): void {
        // Override filesystem functions to restrict access
        $overrideMap = [
            'fopen' => [$this, 'safeFopen'],
            'file_get_contents' => [$this, 'safeFileGetContents'],
            'file_put_contents' => [$this, 'safeFilePutContents'],
            'unlink' => [$this, 'safeUnlink'],
            'mkdir' => [$this, 'safeMkdir'],
            'rmdir' => [$this, 'safeRmdir']
        ];

        foreach ($overrideMap as $func => $handler) {
            if (function_exists($func)) {
                override_function($func, '$path, ...$args', 'return call_user_func($handler, $path, ...$args);');
            }
        }
    }

    private function isPathAllowed(string $path): bool {
        $realPath = realpath($path);
        if (!$realPath) {
            return false;
        }

        foreach ($this->allowedPaths as $allowed) {
            if (strpos($realPath, realpath($allowed)) === 0) {
                return true;
            }
        }
        return false;
    }

    public function safeFopen(string $path, string $mode) {
        if (!$this->isPathAllowed($path)) {
            throw new \RuntimeException("Filesystem access denied to $path");
        }
        return fopen($path, $mode);
    }

    public function handleShutdown(): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            ErrorHandler::log("Plugin sandbox fatal error in {$this->pluginName}: " . $error['message']);
        }
    }

    public function isFunctionAllowed(string $function): bool {
        return in_array($function, $this->allowedFunctions) && 
               !in_array($function, $this->restrictedFunctions);
    }
}
