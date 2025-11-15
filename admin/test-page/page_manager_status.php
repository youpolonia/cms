<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'implemented' => false,
    'note' => 'Placeholder only'
]);
