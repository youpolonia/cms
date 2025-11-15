<?php
class Phase11_Package_Creator {
    const DEPLOYMENT_FILES = [
        'database/migrations/0001_tenant_isolation.php',
        'database/migrations/Migration_0002_AddTenantColumns.php',
        'database/migrations/Migration_0003_CompleteTenantScope.php',
        'database/migrations/Migration_0004_CrossSiteRelations.php',
        'database/migrations/Migration_0005_TenantAwareQueryBuilder.php',
        'database/migrations/Migration_0006_AnalyticsTestEndpoints.php',
        'public/api/test/tenant-scoping.php',
        'public/api/test/cross-site-relations.php',
        'deployment/phase11/checklist.md'
    ];

    public static function createPackage() {
        $errors = [];
        
        // Validate files exist
        foreach (self::DEPLOYMENT_FILES as $file) {
            if (!file_exists($file)) {
                $errors[] = "Missing file: $file";
            }
        }

        if (!empty($errors)) {
            echo "Validation errors:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
            return false;
        }

        // Create zip package
        $zip = new ZipArchive();
        $zipFile = 'deployment/phase11/phase11_package_'.date('Ymd').'.zip';
        
        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
            echo "Failed to create zip file\n";
            return false;
        }

        foreach (self::DEPLOYMENT_FILES as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();
        echo "Package created: $zipFile\n";
        return true;
    }
}

// Execute
Phase11_Package_Creator::createPackage();
