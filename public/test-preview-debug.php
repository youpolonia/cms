<?php
session_start();
header("Content-Type: application/json");

$keys = [];
foreach ($_SESSION as $k => $v) {
    if (strpos($k, "tb_") !== false) {
        $keys[$k] = $v;
    }
}
echo json_encode($keys, JSON_PRETTY_PRINT | JSON_UNCODED_UNICODE);