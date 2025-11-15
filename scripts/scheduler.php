<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/scheduler.php';

header('Content-Type: application/json');
$out = \core\Scheduler::runDue(10);
echo json_encode($out, JSON_UNESCAPED_SLASHES), "\n";

// NOTE: Run by pinging this PHP over HTTP (FTP-only environment). No CLI, no exec/system.
