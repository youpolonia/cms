<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../backup_manager.php';
header('Content-Type: text/plain; charset=UTF-8');
if (!defined('DEV_MODE') || DEV_MODE === false) { http_response_code(403); exit; }
echo handle_backup_action(['action' => 'status']);
