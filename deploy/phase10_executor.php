<?php
require_once __DIR__ . '/phase10_validator.php';

header('Content-Type: text/plain');

try {
    $results = Phase10Validator::validateFiles();
    $newBundlePath = 'deploy/phase10_bundle_new.zip';

    if (Phase10Validator::createBundle($newBundlePath)) {
        $bundleValidation = Phase10Validator::validateBundle($newBundlePath);
        
        // Log to memory-bank
        $logEntry = "## [".date('Y-m-d')."] Agent: Code\n";
        $logEntry .= "- Implemented: Phase 10 bundle recreation\n";
        $logEntry .= "- Output file: $newBundlePath\n";
        $logEntry .= "- Notes: Validated ".count($results)." files\n";
        
        file_put_contents('memory-bank/progress.md', $logEntry, FILE_APPEND);
        
        echo "Phase 10 bundle recreation completed successfully\n";
        echo "Validation results saved to deploy/phase10_validation.log\n";
    } else {
        throw new Exception("Failed to create new bundle");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
