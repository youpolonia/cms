<?php
require_once __DIR__ . '/../../core/csrf.php';

csrf_boot('admin');

function theme_builder_acquire_lock(string $themeName, &$lockHandle): bool {
    static $lockFiles = [];
    require_once __DIR__ . '/../../core/tmp_sandbox.php';

    if ($lockHandle !== null) {
        return false; // Already locked
    }

    if (!isset($lockFiles[$themeName])) {
        $lockFiles[$themeName] = cms_tmp_path('theme_builder_' . $themeName . '.lock');
    }
    
    $lockHandle = fopen($lockFiles[$themeName], 'w+');
    if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
        if ($lockHandle) {
            fclose($lockHandle);
            $lockHandle = null;
        }
        return false;
    }
    return true;
}

function theme_builder_release_lock(&$lockHandle, string $themeName): void {
    static $lockFiles = [];
    
    if ($lockHandle && is_resource($lockHandle)) {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
        @unlink($lockFiles[$themeName]);
    }
    $lockHandle = null;
}

function theme_builder_export(string $themeName, string $outputPath): bool {
    csrf_validate_or_403();
    $lockHandle = null;
    if (!theme_builder_acquire_lock($themeName, $lockHandle)) {
        return false;
    }

    $success = false;
    $themeDir = "themes/{$themeName}";
    
    $zip = new ZipArchive();
    if ($zip->open($outputPath, ZipArchive::CREATE) !== true) {
        theme_builder_release_lock($lockHandle, $themeName);
        return false;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($themeDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($themeDir) + 1);
            if (!$zip->addFile($filePath, $relativePath)) {
                theme_builder_release_lock($lockHandle, $themeName);
                return false;
            }
        }
    }

    $success = $zip->close();
    theme_builder_release_lock($lockHandle, $themeName);
    return $success;
}

// CSRF protection helper
function theme_builder_verify_csrf(string $token): bool {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Tenant isolation check
function theme_builder_verify_tenant_access(string $themeName): bool {
    if (!isset($_SESSION['tenant_id'])) {
        return false; // No tenant session
    }
    
    // Check if theme belongs to current tenant
    $themePath = "themes/{$themeName}/tenant.json";
    if (file_exists($themePath)) {
        $tenantData = json_decode(file_get_contents($themePath), true);
        return $tenantData['tenant_id'] === $_SESSION['tenant_id'];
    }
    
    return false;
}

function theme_builder_import(string $themeName, string $zipPath): bool {
    csrf_validate_or_403();
    $lockHandle = null;
    if (!theme_builder_acquire_lock($themeName, $lockHandle)) {
        return false;
    }

    try {
        if (!file_exists($zipPath) || !is_readable($zipPath)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        if ($zip->locateName('theme.json') === false) {
            return false;
        }
        
        $themeJson = $zip->getFromName('theme.json');
        if ($themeJson === false) {
            return false;
        }
        
        $themeData = json_decode($themeJson, true);
        if (isset($themeData['widgets'])) {
            $widgetClasses = theme_builder_get_widget_classes();
            foreach ($themeData['widgets'] as $widgetClass => $settings) {
                if (!in_array($widgetClass, $widgetClasses)) {
                    return false;
                }
            }

        $targetDir = "themes/{$themeName}";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (!is_writable($targetDir)) {
                theme_builder_release_lock($lockHandle, $themeName);
                return false;
            }

            $zip->extractTo($targetDir);
            $success = $zip->close();
            
            // Process widget settings after import
            if ($success) {
                $themeJsonPath = "$targetDir/theme.json";
                if (file_exists($themeJsonPath)) {
                    $themeData = json_decode(file_get_contents($themeJsonPath), true);
                    if (is_array($themeData)) {
                        $widgetClasses = theme_builder_get_widget_classes();
                        $themeData['widgets'] = [];
                        
                        foreach ($widgetClasses as $widgetClass) {
                            try {
                                if (method_exists($widgetClass, 'getDefaultSettings')) {
                                    $settings = $widgetClass::getDefaultSettings();
                                    if (is_array($settings)) {
                                        $themeData['widgets'][$widgetClass] = $settings;
                                    }
                                }
                            } catch (Exception $e) {
                                continue;
                            }
                        }
                        
                        // Add tenant info if needed
                        if (isset($_SESSION['tenant_id'])) {
                            $themeData['tenant_id'] = $_SESSION['tenant_id'];
                            $tenantId = filter_var($_SESSION['tenant_id'], FILTER_SANITIZE_NUMBER_INT);
                            $tenantFile = $targetDir . '/tenant.json';
                            file_put_contents($tenantFile,
                                json_encode(
                                    ['tenant_id' => $tenantId],
                                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                                ));
                        }
                        
                        file_put_contents($themeJsonPath,
                            json_encode($themeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    }
                }
            }
            
            theme_builder_release_lock($lockHandle, $themeName);
            return $success;
        }
    } catch (Exception $e) {
        if ($lockHandle !== null) {
            theme_builder_release_lock($lockHandle, $themeName);
        }
        return false;
    }
}

function theme_builder_create(string $themeName, string $csrfToken = null): bool {
    csrf_validate_or_403();
    // Validate CSRF if token provided
    if ($csrfToken !== null && !theme_builder_verify_csrf($csrfToken)) {
        return false;
    }
    
    // Verify tenant access
    if (!theme_builder_verify_tenant_access($themeName)) {
        return false;
    }

    $lockHandle = null;
    if (!theme_builder_acquire_lock($themeName, $lockHandle)) {
        return false;
    }

    $success = true;
    $themeDir = "themes/{$themeName}";
    
    if (!file_exists($themeDir)) {
        if (!mkdir($themeDir, 0755, true)) {
            $success = false;
        }
    }

    if ($success) {
        $templatePath = __DIR__ . '/theme.template.json';
        if (!file_exists($templatePath)) {
            $success = false;
        } else {
            $template = json_decode(file_get_contents($templatePath), true);
            $template['name'] = $themeName;
            
            // Enhanced widget settings handling
            $widgetClasses = theme_builder_get_widget_classes();
            $template['widgets'] = [];
            
            foreach ($widgetClasses as $widgetClass) {
                try {
                    if (method_exists($widgetClass, 'getDefaultSettings')) {
                        $settings = $widgetClass::getDefaultSettings();
                        if (is_array($settings)) {
                            $template['widgets'][$widgetClass] = $settings;
                        }
                    }
                } catch (Exception $e) {
                    // Skip invalid widget classes
                    continue;
                }
            }
            
            // Add tenant info to theme
            if (isset($_SESSION['tenant_id'])) {
                $template['tenant_id'] = $_SESSION['tenant_id'];
            }
            
            $templateJson = json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $success = $success && (file_put_contents("$themeDir/theme.json", $templateJson) !== false);
            
            // Create tenant.json for isolation
            if ($success && isset($_SESSION['tenant_id'])) {
                $tenantData = ['tenant_id' => $_SESSION['tenant_id']];
                file_put_contents("$themeDir/tenant.json", json_encode($tenantData));
            }
        }
    }

    if ($success) {
        $dirs = [
            "$themeDir/assets/css",
            "$themeDir/assets/js",
            "$themeDir/templates"
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir) && !mkdir($dir, 0755, true)) {
                $success = false;
                break;
            }
        }
    }

    if ($success) {
        $defaultFiles = [
            "$themeDir/assets/css/main.css" => "/* Main theme styles */",
            "$themeDir/assets/js/main.js" => "// Theme JavaScript",
            "$themeDir/templates/default.php" => "<?php\n// Default template\n?>"
        ];

        foreach ($defaultFiles as $path => $content) {
            if (file_put_contents($path, $content) === false) {
                $success = false;
                break;
            }
        }
    }

    theme_builder_release_lock($lockHandle, $themeName);
    return $success;
}

function theme_builder_get_widget_classes(): array {
    $widgetDir = 'includes/Widgets/';
    $widgetClasses = [];
    
    if (file_exists($widgetDir)) {
        $files = scandir($widgetDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $className = pathinfo($file, PATHINFO_FILENAME);
                if (class_exists($className) && method_exists($className, 'render')) {
                    $widgetClasses[] = $className;
                }
            }
        }
    }
    
    return $widgetClasses;
}
