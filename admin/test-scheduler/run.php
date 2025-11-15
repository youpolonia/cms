<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE === false) {
    http_response_code(403);
    exit;
}
echo "DEV endpoint active\n";
