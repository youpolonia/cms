<?php
require_once __DIR__ . '/core/bootstrap.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
header('Content-Type: text/plain; charset=utf-8');
echo "Disabled legacy script: assign_role.php (Composer/Laravel not allowed).";
exit;
