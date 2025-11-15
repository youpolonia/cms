<?php
namespace Includes\Debug;

class ErrorHandler {
    public static function register() {
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline) {
        require_once __DIR__ . '/debug_error_handler.php';
        return debugErrorHandler($errno, $errstr, $errfile, $errline);
    }
}
