<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../config.php'; }
http_response_code(403);
exit;
