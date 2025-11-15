<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
http_response_code(403);
echo "Disabled: process_analytics.php not permitted (framework bootstrap).";
