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
            'samesite' => 'Lax',
        ];

        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params($opts);
        } else {
            session_set_cookie_params(
                (int)$opts['lifetime'],
                $opts['path'].'; samesite='.$opts['samesite'],
                $opts['domain'],
                (bool)$opts['secure'],
                (bool)$opts['httponly']
            );
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        if ($https) { ini_set('session.cookie_secure', '1'); }

        session_start();
    }
}
