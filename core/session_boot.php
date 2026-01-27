<?php
declare(strict_types=1);

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }

if (!function_exists('cms_session_start')) {
    function cms_session_start(string $area = 'admin'): void {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
              || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        $name = ($area === 'admin') ? 'CMSSESSID_ADMIN' : 'CMSSESSID';
        if (session_name() !== $name) { session_name($name); }

        $cur = session_get_cookie_params();
        $opts = [
            'lifetime' => 0,
            'path'     => $cur['path'] ?? '/',
            'domain'   => $cur['domain'] ?? '',
            'secure'   => $https ? true : false,
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        session_set_cookie_params($opts);
        session_start();

        if ($area === 'admin' && empty($_SESSION['_init'])) {
            session_regenerate_id(true);
            $_SESSION['_init'] = true;
        }
    }
}

if (!function_exists('cms_session_destroy')) {
    function cms_session_destroy(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
