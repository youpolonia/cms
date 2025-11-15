<?php
/**
 * Secure session management for FTP-only CMS
 */
function cms_session_start() {
    // Set secure session options before starting
    $sessionName = 'CMS_SESSID';
    $secure = true;
    $httponly = true;
    $samesite = 'Strict';

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 86400, // 1 day
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    } else {
        session_set_cookie_params(
            86400,
            '/; samesite='.$samesite,
            $_SERVER['HTTP_HOST'],
            $secure,
            $httponly
        );
    }

    session_name($sessionName);
    
    // Prevent session fixation
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate ID periodically
    if (!isset($_SESSION['__CMS_LAST_REGEN'])) {
        session_regenerate_id(true);
        $_SESSION['__CMS_LAST_REGEN'] = time();
    } elseif (time() - $_SESSION['__CMS_LAST_REGEN'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['__CMS_LAST_REGEN'] = time();
    }
}

function cms_session_destroy() {
    // Unset all session variables
    $_SESSION = array();

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();
}
