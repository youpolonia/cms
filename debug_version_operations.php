<?php
// Debug script for version operations testing
require_once __DIR__ . '/includes/version_operations.php';

$versionOps = new VersionOperations();
$result = $versionOps->testOperations();
var_dump($result);
