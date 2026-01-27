<?php
/**
 * AI Configuration Diagnostic Tool
 * Checks if AI settings are properly loaded
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/admin/includes/auth.php';
cms_require_admin_role();

header('Content-Type: text/plain; charset=utf-8');

echo "=== AI Configuration Diagnostic ===\n\n";

// Check CMS_ROOT
echo "1. CMS_ROOT: " . CMS_ROOT . "\n";

// Check config file path
$configPath = CMS_ROOT . '/config/ai_settings.json';
echo "2. Config path: " . $configPath . "\n";
echo "3. File exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "\n";

if (file_exists($configPath)) {
    $json = file_get_contents($configPath);
    echo "4. File readable: " . ($json !== false ? 'YES' : 'NO') . "\n";
    
    if ($json !== false) {
        $settings = json_decode($json, true);
        echo "5. JSON valid: " . (is_array($settings) ? 'YES' : 'NO - ' . json_last_error_msg()) . "\n";
        
        if (is_array($settings)) {
            echo "\n=== OpenAI Settings ===\n";
            $openai = $settings['providers']['openai'] ?? [];
            echo "6. OpenAI section exists: " . (isset($settings['providers']['openai']) ? 'YES' : 'NO') . "\n";
            echo "7. OpenAI enabled: " . (!empty($openai['enabled']) ? 'YES' : 'NO') . "\n";
            echo "8. OpenAI API key exists: " . (!empty($openai['api_key']) ? 'YES (length: ' . strlen($openai['api_key']) . ')' : 'NO') . "\n";
            echo "9. API key starts with: " . (isset($openai['api_key']) ? substr($openai['api_key'], 0, 10) . '...' : 'N/A') . "\n";
        }
    }
}

// Check ai_images module
echo "\n=== AI Images Module ===\n";
$aiImagesPath = CMS_ROOT . '/core/ai_images.php';
echo "10. ai_images.php exists: " . (file_exists($aiImagesPath) ? 'YES' : 'NO') . "\n";

if (file_exists($aiImagesPath)) {
    require_once $aiImagesPath;
    
    echo "11. ai_images_is_configured() exists: " . (function_exists('ai_images_is_configured') ? 'YES' : 'NO') . "\n";
    
    if (function_exists('ai_images_is_configured')) {
        $result = ai_images_is_configured();
        echo "12. ai_images_is_configured() returns: " . ($result ? 'TRUE' : 'FALSE') . "\n";
    }
    
    if (function_exists('ai_images_load_openai_settings')) {
        $loadedSettings = ai_images_load_openai_settings();
        echo "13. Loaded settings:\n";
        echo "    - enabled: " . ($loadedSettings['enabled'] ? 'true' : 'false') . "\n";
        echo "    - api_key: " . (!empty($loadedSettings['api_key']) ? 'present (' . strlen($loadedSettings['api_key']) . ' chars)' : 'empty') . "\n";
    }
}

echo "\n=== Summary ===\n";
if (function_exists('ai_images_is_configured') && ai_images_is_configured()) {
    echo "✅ AI Images should be working!\n";
} else {
    echo "❌ AI Images is NOT configured properly\n";
}
