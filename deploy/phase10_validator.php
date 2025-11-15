<?php
/**
 * Phase 10 Deployment Bundle Validator
 */
class Phase10Validator {
    const REQUIRED_FILES = [
        'database/migrations/Migration_0001_AddTenantAwareness.php',
        'database/migrations/0005_test_endpoints.php',
        'database/migrations/001_create_tenant_aware_tables.php',
        'api/federation.php',
        'config/status_rules.php',
        'docs/phase10_deployment.md'
    ];

    public static function validateFiles(): array {
        $results = [];
        foreach (self::REQUIRED_FILES as $file) {
            $results[$file] = [
                'exists' => file_exists($file),
                'size' => file_exists($file) ? filesize($file) : 0,
                'checksum' => file_exists($file) ? md5_file($file) : ''
            ];
        }
        return $results;
    }

    public static function createBundle(string $outputPath): bool {
        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach (self::REQUIRED_FILES as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, $file);
            }
        }

        return $zip->close();
    }

    public static function validateBundle(string $bundlePath): array {
        $results = [];
        $zip = new ZipArchive();
        if ($zip->open($bundlePath) !== true) {
            return ['error' => 'Failed to open bundle'];
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $results[$filename] = [
                'size' => $zip->statIndex($i)['size'],
                'compressed_size' => $zip->statIndex($i)['comp_size']
            ];
        }

        $zip->close();
        return $results;
    }
}

// Execute validation
$validationResults = Phase10Validator::validateFiles();
$newBundlePath = 'deploy/phase10_bundle_new.zip';

if (Phase10Validator::createBundle($newBundlePath)) {
    $bundleValidation = Phase10Validator::validateBundle($newBundlePath);
    file_put_contents('deploy/phase10_validation.log', 
        json_encode([
            'file_validation' => $validationResults,
            'bundle_validation' => $bundleValidation
        ], JSON_PRETTY_PRINT)
    );
    echo "Phase 10 bundle recreation completed successfully\n";
} else {
    echo "Failed to create new bundle\n";
}
