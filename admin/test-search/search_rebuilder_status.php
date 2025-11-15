<?php
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('Access denied. DEV_MODE required.');
}

header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'implemented' => false,
    'note' => 'Placeholder only'
]);
