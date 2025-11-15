<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
function debugErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    switch ($errno) {
        case E_USER_ERROR:
            echo "ERROR [$errno] $errstr\n";
            echo "Fatal error on line $errline in file $errfile\n";
            exit(1);
            break;
            
        case E_USER_WARNING:
            echo "WARNING [$errno] $errstr\n";
            break;
            
        case E_USER_NOTICE:
            echo "NOTICE [$errno] $errstr\n";
            break;
            
        default:
            echo "Unknown error type: [$errno] $errstr\n";
            break;
    }
    
    return true;
}

// Error handler registration now handled by ErrorHandler class
