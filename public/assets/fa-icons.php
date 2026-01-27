<?php
header("Content-Type: application/json");
header("Cache-Control: public, max-age=86400");
$file = __DIR__ . "/fonts/fontawesome/fa-icons.json";
if (file_exists($file)) { readfile($file); } else { http_response_code(404); echo json_encode(["error" => $file]); }