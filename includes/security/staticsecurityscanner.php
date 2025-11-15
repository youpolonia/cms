<?php
/**
 * Static Security Scanner
 * Read-only security analysis for PHP codebase
 */
class StaticSecurityScanner {
    private $basePath;
    private $scanDirs = ['core', 'includes', 'admin', 'public', 'api', 'models', 'views', 'plugins', 'extensions', 'templates', 'controllers'];
    private $excludeDirs = ['memory-bank', 'logs', 'node_modules', 'vendor', '.git', 'uploads', 'cache', 'tmp', '.husky', '.vscode', '.idea'];
    private $results = [];

    public function __construct($basePath) {
        $this->basePath = rtrim($basePath, '/');
    }

    public function scan() {
        $this->results = [
            'forbidden_calls' => [],
            'autoloaders' => [],
            'dynamic_includes' => [],
            'csrf_missing' => [],
            'public_test_endpoints' => [],
            'trailing_php_tag' => []
        ];

        foreach ($this->scanDirs as $dir) {
            $fullPath = $this->basePath . '/' . $dir;
            if (is_dir($fullPath)) {
                $this->scanDirectory($fullPath);
            }
        }

        return $this->results;
    }

    private function scanDirectory($dir) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $dir . '/' . $item;
            $relativePath = str_replace($this->basePath . '/', '', $path);

            if ($this->isExcluded($relativePath)) continue;

            if (is_dir($path)) {
                $this->scanDirectory($path);
            } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $this->scanFile($path, $relativePath);
            }
        }
    }

    private function isExcluded($path) {
        foreach ($this->excludeDirs as $exclude) {
            if (strpos($path, $exclude . '/') === 0 || strpos($path, '/' . $exclude . '/') !== false) {
                return true;
            }
            if (preg_match('#(^|/)debug[^/]*/#', $path)) {
                return true;
            }
        }
        return false;
    }

    private function scanFile($fullPath, $relativePath) {
        $content = file_get_contents($fullPath);
        $lines = explode("\n", $content);

        $this->checkForbiddenCalls($lines, $relativePath);
        $this->checkAutoloaders($lines, $relativePath);
        $this->checkDynamicIncludes($lines, $relativePath);
        $this->checkCsrf($lines, $relativePath, $content);
        $this->checkPublicTestEndpoints($lines, $relativePath);
        $this->checkTrailingPhpTag($content, $relativePath);
    }

    private function checkForbiddenCalls($lines, $path) {
        $forbidden = ['system', 'exec', 'shell_exec', 'passthru', 'popen', 'proc_open'];
        foreach ($lines as $num => $line) {
            foreach ($forbidden as $func) {
                if (preg_match('/\b' . preg_quote($func, '/') . '\s*\(/', $line)) {
                    $this->results['forbidden_calls'][] = [
                        'path' => $path,
                        'line' => $num + 1,
                        'kind' => $func,
                        'snippet' => trim($line)
                    ];
                }
            }
        }
    }

    private function checkAutoloaders($lines, $path) {
        foreach ($lines as $num => $line) {
            if (preg_match('/\bspl_autoload_register\s*\(/', $line)) {
                $this->results['autoloaders'][] = [
                    'path' => $path,
                    'line' => $num + 1,
                    'kind' => 'spl_autoload_register',
                    'snippet' => trim($line)
                ];
            }
            if (preg_match('/\bfunction\s+__autoload\s*\(/', $line)) {
                $this->results['autoloaders'][] = [
                    'path' => $path,
                    'line' => $num + 1,
                    'kind' => '__autoload',
                    'snippet' => trim($line)
                ];
            }
            if (strpos($line, 'Composer\\Autoload\\') !== false) {
                $this->results['autoloaders'][] = [
                    'path' => $path,
                    'line' => $num + 1,
                    'kind' => 'Composer\\Autoload\\',
                    'snippet' => trim($line)
                ];
            }
            if (preg_match('/require(?:_once)?\s*[(\s]*[\'"].*vendor\/autoload\.php/', $line)) {
                $this->results['autoloaders'][] = [
                    'path' => $path,
                    'line' => $num + 1,
                    'kind' => 'vendor/autoload.php',
                    'snippet' => trim($line)
                ];
            }
        }
    }

    private function checkDynamicIncludes($lines, $path) {
        foreach ($lines as $num => $line) {
            if (preg_match('/\b(require|require_once|include|include_once)\s*[\(\s]/', $line)) {
                if (preg_match('/\b(require|require_once|include|include_once)\s*[\(\s]*(\$|[a-zA-Z_][a-zA-Z0-9_]*\s*\(|\((?!\s*[\'\"]))/', $line)) {
                    if (!preg_match('/\b(require|require_once|include|include_once)\s*[\(\s]*__DIR__\s*\./', $line)) {
                        if (!preg_match('/\b(require|require_once|include|include_once)\s*[\(\s]*[\'\"\/]/', $line)) {
                            $this->results['dynamic_includes'][] = [
                                'path' => $path,
                                'line' => $num + 1,
                                'kind' => 'dynamic_include',
                                'snippet' => trim($line)
                            ];
                        }
                    }
                }
            }
        }
    }

    private function checkCsrf($lines, $path, $content) {
        $formMatches = [];
        preg_match_all('/<form[^>]*method\s*=\s*["\']post["\'][^>]*>/i', $content, $formMatches, PREG_OFFSET_CAPTURE);

        foreach ($formMatches[0] as $match) {
            $lineNum = substr_count(substr($content, 0, $match[1]), "\n") + 1;
            $hasCsrf = false;
            $startLine = $lineNum - 1;
            for ($i = $startLine; $i < min($startLine + 20, count($lines)); $i++) {
                if (strpos($lines[$i], 'csrf_field()') !== false) {
                    $hasCsrf = true;
                    break;
                }
            }
            if (!$hasCsrf) {
                $this->results['csrf_missing'][] = [
                    'path' => $path,
                    'line' => $lineNum,
                    'kind' => 'form_missing_csrf',
                    'snippet' => trim($match[0])
                ];
            }
        }

        if (preg_match('/\$_SERVER\s*\[\s*[\'"]REQUEST_METHOD[\'"]\s*\]\s*===?\s*[\'"]POST[\'"]/i', $content)) {
            if (strpos($content, 'csrf_validate_or_403()') === false) {
                foreach ($lines as $num => $line) {
                    if (preg_match('/\$_SERVER\s*\[\s*[\'"]REQUEST_METHOD[\'"]\s*\]\s*===?\s*[\'"]POST[\'"]/i', $line)) {
                        $this->results['csrf_missing'][] = [
                            'path' => $path,
                            'line' => $num + 1,
                            'kind' => 'handler_missing_csrf',
                            'snippet' => trim($line)
                        ];
                        break;
                    }
                }
            }
        }
    }

    private function checkPublicTestEndpoints($lines, $path) {
        if (strpos($path, 'public/') !== 0) return;

        if (preg_match('#/(test|debug)#i', $path)) {
            $content = implode("\n", $lines);
            $hasDEVGate = preg_match('/if\s*\(\s*!.*DEV_MODE.*\).*\b(exit|die)\b/s', $content) ||
                          preg_match('/if\s*\(\s*!defined\s*\(\s*[\'"]DEV_MODE[\'"]\s*\)/s', $content);

            if (!$hasDEVGate) {
                $this->results['public_test_endpoints'][] = [
                    'path' => $path,
                    'line' => 1,
                    'kind' => 'unguarded_test_endpoint',
                    'snippet' => 'Test/debug file in public/ without DEV_MODE gate'
                ];
            }
        }
    }

    private function checkTrailingPhpTag($content, $path) {
        $trimmed = rtrim($content);
        if (substr($trimmed, -2) === '?>') {
            $lineCount = substr_count($content, "\n") + 1;
            $this->results['trailing_php_tag'][] = [
                'path' => $path,
                'line' => $lineCount,
                'kind' => 'trailing_php_closing_tag',
                'snippet' => '?>'
            ];
        }
    }

    public function getResults() {
        return $this->results;
    }

    public function getCounts() {
        return [
            'forbidden_calls' => count($this->results['forbidden_calls']),
            'autoloaders' => count($this->results['autoloaders']),
            'dynamic_includes' => count($this->results['dynamic_includes']),
            'csrf_missing' => count($this->results['csrf_missing']),
            'public_test_endpoints' => count($this->results['public_test_endpoints']),
            'trailing_php_tag' => count($this->results['trailing_php_tag'])
        ];
    }
}
