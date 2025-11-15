<?php
// core/error_handler.php — global error/exception handling (no frameworks)
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }

if (!function_exists('cms_register_error_handlers')) {
    function cms_register_error_handlers(): void {
        $isDev = defined('DEV_MODE') && DEV_MODE === true;

        // Error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', $isDev ? '1' : '0');
        ini_set('log_errors', '1');
        $logDir = CMS_ROOT . '/logs';
        if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
        ini_set('error_log', $logDir . '/php_errors.log');

        // JSONL app log for exceptions
        $appLog = $logDir . '/app_errors.log';

        set_error_handler(function (int $severity, string $message, string $file = '', int $line = 0) use ($isDev, $appLog) {
            // Convert warnings/notices to ErrorException to unify flow
            if (!(error_reporting() & $severity)) return false;
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $e) use ($isDev, $appLog) {
            $errId = bin2hex(random_bytes(8));
            $payload = [
                'ts'   => gmdate('c'),
                'id'   => $errId,
                'type' => 'exception',
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace'=> $isDev ? $e->getTrace() : [],
                'uri'  => $_SERVER['REQUEST_URI'] ?? '',
            ];
            @file_put_contents($appLog, json_encode($payload) . PHP_EOL, FILE_APPEND | LOCK_EX);

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/plain; charset=UTF-8');
                header('Cache-Control: no-store');
            }
            if ($isDev) {
                echo "ERROR_ID:$errId\n", $e->getMessage(), "\n";
            } else {
                echo "ERROR_ID:$errId\nService temporarily unavailable.\n";
            }
        });

        register_shutdown_function(function () use ($isDev, $appLog) {
            $e = error_get_last();
            if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $errId = bin2hex(random_bytes(8));
                $payload = [
                    'ts'   => gmdate('c'),
                    'id'   => $errId,
                    'type' => 'fatal',
                    'msg'  => $e['message'] ?? '',
                    'file' => $e['file'] ?? '',
                    'line' => $e['line'] ?? 0,
                    'uri'  => $_SERVER['REQUEST_URI'] ?? '',
                ];
                @file_put_contents($appLog, json_encode($payload) . PHP_EOL, FILE_APPEND | LOCK_EX);
                if (!headers_sent()) {
                    http_response_code(500);
                    header('Content-Type: text/plain; charset=UTF-8');
                    header('Cache-Control: no-store');
                }
                echo $isDev ? "ERROR_ID:$errId\n{$e['message']}\n" : "ERROR_ID:$errId\nService temporarily unavailable.\n";
            }
        });
    }
}
