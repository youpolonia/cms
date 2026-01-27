<?php
/**
 * AI Plugin Builder
 * Generate complete plugin structures using AI
 */

declare(strict_types=1);

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_models.php';

class AIPluginBuilder
{
    private const PLUGIN_TEMPLATES = [
        'basic' => [
            'name' => 'Basic Plugin',
            'description' => 'Simple plugin with admin page',
            'files' => ['plugin.json', 'index.php', 'admin.php']
        ],
        'widget' => [
            'name' => 'Widget Plugin',
            'description' => 'Frontend widget with admin settings',
            'files' => ['plugin.json', 'index.php', 'widget.php', 'admin.php', 'install.php']
        ],
        'api' => [
            'name' => 'API Plugin',
            'description' => 'REST API endpoints',
            'files' => ['plugin.json', 'index.php', 'api.php', 'admin.php']
        ],
        'integration' => [
            'name' => 'Integration Plugin',
            'description' => 'Third-party service integration',
            'files' => ['plugin.json', 'index.php', 'client.php', 'admin.php', 'install.php']
        ],
        'content' => [
            'name' => 'Content Plugin',
            'description' => 'Custom content type or block',
            'files' => ['plugin.json', 'index.php', 'block.php', 'renderer.php', 'admin.php', 'install.php']
        ]
    ];

    private const MENU_SECTIONS = [
        'dashboard' => 'Dashboard',
        'content' => 'Content',
        'seo' => 'SEO',
        'ai-tools' => 'AI Tools',
        'marketing' => 'Marketing',
        'appearance' => 'Appearance',
        'system' => 'System',
        'integrations' => 'Integrations',
        'plugins' => 'Plugins'
    ];

    /**
     * Get available templates
     */
    public static function getTemplates(): array
    {
        return self::PLUGIN_TEMPLATES;
    }

    /**
     * Get menu sections
     */
    public static function getMenuSections(): array
    {
        return self::MENU_SECTIONS;
    }

    /**
     * Generate plugin using AI
     */
    public static function generate(array $params): array
    {
        $name = trim($params['name'] ?? '');
        $description = trim($params['description'] ?? '');
        $features = trim($params['features'] ?? '');
        $template = $params['template'] ?? 'basic';
        $menuSection = $params['menu_section'] ?? 'plugins';
        $hasAdminPage = (bool)($params['has_admin_page'] ?? true);
        $hasDatabase = (bool)($params['has_database'] ?? false);
        $hasApi = (bool)($params['has_api'] ?? false);

        if (empty($name)) {
            return ['success' => false, 'error' => 'Plugin name is required'];
        }

        // Generate slug
        $slug = self::generateSlug($name);

        // Check if plugin already exists
        $pluginDir = CMS_ROOT . '/plugins/' . $slug;
        if (is_dir($pluginDir)) {
            return ['success' => false, 'error' => 'Plugin already exists: ' . $slug];
        }

        // Build prompt for AI
        $prompt = self::buildPrompt($name, $description, $features, $template, $menuSection, $hasAdminPage, $hasDatabase, $hasApi);

        // Get model from params if provided
        $model = $params['model'] ?? null;

        // Call AI API
        $aiResult = self::callAI($prompt, $model);
        
        if (!$aiResult['success']) {
            return $aiResult;
        }

        // Parse AI response and create files
        $files = self::parseAIResponse($aiResult['content'], $slug, $name, $description, $menuSection);

        if (empty($files)) {
            return ['success' => false, 'error' => 'Failed to parse AI response'];
        }

        // Create plugin directory and files
        $createResult = self::createPluginFiles($pluginDir, $files);

        if (!$createResult['success']) {
            return $createResult;
        }

        return [
            'success' => true,
            'slug' => $slug,
            'path' => $pluginDir,
            'files' => array_keys($files),
            'message' => "Plugin '$name' created successfully!"
        ];
    }

    /**
     * Generate slug from name
     */
    private static function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    /**
     * Build AI prompt
     */
    private static function buildPrompt(
        string $name,
        string $description,
        string $features,
        string $template,
        string $menuSection,
        bool $hasAdminPage,
        bool $hasDatabase,
        bool $hasApi
    ): string {
        $slug = self::generateSlug($name);
        
        $prompt = "Generate a complete CMS plugin with the following specifications:

PLUGIN NAME: {$name}
SLUG: {$slug}
DESCRIPTION: {$description}
FEATURES: {$features}
TEMPLATE TYPE: {$template}
MENU SECTION: {$menuSection}
HAS ADMIN PAGE: " . ($hasAdminPage ? 'Yes' : 'No') . "
HAS DATABASE: " . ($hasDatabase ? 'Yes' : 'No') . "
HAS API: " . ($hasApi ? 'Yes' : 'No') . "

CRITICAL RULES FOR CODE GENERATION:
1. Pure PHP 8.1+ only - NO frameworks
2. NO closing ?> tags at end of files
3. Use require_once only (not include)
4. NO system(), exec(), shell_exec() or CLI functions
5. All database access via \\core\\Database::connection()
6. All filenames must be lowercase
7. CSRF protection for all forms using csrf_field() and csrf_validate_or_403()
8. Escape all output with htmlspecialchars()

REQUIRED FILES TO GENERATE:

1. plugin.json - Must include:
```json
{
  \"name\": \"{$name}\",
  \"version\": \"1.0.0\",
  \"description\": \"{$description}\",
  \"author\": \"CMS Team\",
  \"requires\": {\"core\": \">=2.0.0\", \"php\": \">=8.1\"},
  \"menu\": {
    \"section\": \"{$menuSection}\",
    \"items\": [{\"title\": \"{$name}\", \"icon\": \"🧩\", \"url\": \"/admin/{$slug}.php\"}]
  }
}
```

2. index.php - Main plugin file with initialization

3. admin/{$slug}.php - Admin panel page (if has_admin_page=true) with:
   - Proper header/footer includes
   - CSRF protection
   - Clean UI matching admin theme
   - Form handling

4. install.php - Installation script (if has_database=true) with:
   - Database table creation
   - Default settings

5. uninstall.php - Cleanup script

Generate each file with clear markers:
===FILE: filename.php===
(content)
===END FILE===

Make the code production-ready, secure, and well-documented.";

        return $prompt;
    }

    /**
     * Call AI API
     * @param string $prompt The prompt to send
     * @param string|null $model Optional model override
     */
    private static function callAI(string $prompt, ?string $model = null): array
    {
        // Load AI settings
        $settingsFile = CMS_ROOT . '/config/ai_settings.json';
        if (!file_exists($settingsFile)) {
            return ['success' => false, 'error' => 'AI settings not configured'];
        }

        $settings = json_decode(file_get_contents($settingsFile), true);
        $provider = $settings['provider'] ?? 'openai';
        $apiKey = $settings['api_key'] ?? $settings['openai_api_key'] ?? '';

        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'AI API key not configured'];
        }

        if ($provider === 'openai' || $provider === 'default') {
            return self::callOpenAI($apiKey, $prompt, $settings, $model);
        } elseif ($provider === 'anthropic') {
            return self::callAnthropic($apiKey, $prompt, $settings);
        }

        return ['success' => false, 'error' => 'Unknown AI provider: ' . $provider];
    }

    /**
     * Call OpenAI API
     * @param string $apiKey API key
     * @param string $prompt The prompt
     * @param array $settings Settings array
     * @param string|null $modelOverride Optional model override
     */
    private static function callOpenAI(string $apiKey, string $prompt, array $settings, ?string $modelOverride = null): array
    {
        // Model priority: override > settings > default
        $model = $modelOverride ?? $settings['openai_model'] ?? $settings['model'] ?? 'gpt-4.1-mini';

        // Validate model if ai_models.php functions are available
        if (function_exists('ai_is_valid_model') && !ai_is_valid_model($model)) {
            $model = function_exists('ai_get_default_model') ? ai_get_default_model() : 'gpt-4.1-mini';
        }
        
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert PHP developer specializing in CMS plugin development. Generate clean, secure, production-ready code.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 4000,
            'temperature' => 0.3
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 120
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'API request failed: ' . $error];
        }

        if ($httpCode !== 200) {
            $body = json_decode($response, true);
            return ['success' => false, 'error' => 'API error: ' . ($body['error']['message'] ?? $response)];
        }

        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? '';

        return ['success' => true, 'content' => $content];
    }

    /**
     * Call Anthropic API
     */
    private static function callAnthropic(string $apiKey, string $prompt, array $settings): array
    {
        $model = $settings['anthropic_model'] ?? 'claude-sonnet-4-5-20250929';
        
        $data = [
            'model' => $model,
            'max_tokens' => 4000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 120
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'API request failed: ' . $error];
        }

        if ($httpCode !== 200) {
            $body = json_decode($response, true);
            return ['success' => false, 'error' => 'API error: ' . ($body['error']['message'] ?? $response)];
        }

        $result = json_decode($response, true);
        $content = $result['content'][0]['text'] ?? '';

        return ['success' => true, 'content' => $content];
    }

    /**
     * Parse AI response into files
     */
    private static function parseAIResponse(string $content, string $slug, string $name, string $description, string $menuSection): array
    {
        $files = [];

        // Try to parse ===FILE: filename=== format
        if (preg_match_all('/===FILE:\s*([^=]+)===\s*(.*?)===END FILE===/s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $filename = trim($match[1]);
                $fileContent = trim($match[2]);
                
                // Clean up code blocks
                $fileContent = preg_replace('/^```(?:php|json)?\s*/m', '', $fileContent);
                $fileContent = preg_replace('/```\s*$/m', '', $fileContent);
                
                $files[$filename] = trim($fileContent);
            }
        }

        // If parsing failed, create basic structure
        if (empty($files)) {
            $files = self::createBasicStructure($slug, $name, $description, $menuSection);
        }

        // Ensure plugin.json exists
        if (!isset($files['plugin.json'])) {
            $files['plugin.json'] = json_encode([
                'name' => $name,
                'version' => '1.0.0',
                'description' => $description,
                'author' => 'CMS Team',
                'requires' => ['core' => '>=2.0.0', 'php' => '>=8.1'],
                'menu' => [
                    'section' => $menuSection,
                    'items' => [
                        ['title' => $name, 'icon' => '🧩', 'url' => '/admin/' . $slug . '.php']
                    ]
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return $files;
    }

    /**
     * Create basic plugin structure
     */
    private static function createBasicStructure(string $slug, string $name, string $description, string $menuSection): array
    {
        $files = [];

        // plugin.json
        $files['plugin.json'] = json_encode([
            'name' => $name,
            'version' => '1.0.0',
            'description' => $description,
            'author' => 'CMS Team',
            'requires' => ['core' => '>=2.0.0', 'php' => '>=8.1'],
            'menu' => [
                'section' => $menuSection,
                'items' => [
                    ['title' => $name, 'icon' => '🧩', 'url' => '/admin/' . $slug . '.php']
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // index.php
        $files['index.php'] = '<?php
/**
 * ' . $name . ' Plugin
 * ' . $description . '
 */

declare(strict_types=1);

if (!defined(\'CMS_ROOT\')) {
    define(\'CMS_ROOT\', dirname(dirname(__DIR__)));
}

class ' . str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Plugin
{
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        
        // Plugin initialization code here
    }

    public static function getVersion(): string
    {
        return \'1.0.0\';
    }
}

// Auto-initialize
' . str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))) . 'Plugin::init();
';

        // Admin page
        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));
        $files['admin/' . $slug . '.php'] = '<?php
/**
 * ' . $name . ' Admin Panel
 */

declare(strict_types=1);

define(\'CMS_ROOT\', realpath(__DIR__ . \'/../..\'));

require_once CMS_ROOT . \'/config.php\';
require_once CMS_ROOT . \'/core/session_boot.php\';
cms_session_start(\'admin\');

require_once CMS_ROOT . \'/core/csrf.php\';
csrf_boot(\'admin\');

require_once CMS_ROOT . \'/admin/includes/permissions.php\';
cms_require_admin_role();

$pageTitle = \'' . $name . '\';
$message = \'\';
$messageType = \'\';

// Handle form submission
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {
    csrf_validate_or_403();
    
    // Process form data here
    $message = \'Settings saved successfully!\';
    $messageType = \'success\';
}

require_once CMS_ROOT . \'/admin/includes/header.php\';
?>

<div class="container">
    <h1><?= htmlspecialchars($pageTitle) ?></h1>
    
    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>Settings</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label>Option 1</label>
                    <input type="text" name="option1" class="form-control" value="">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once CMS_ROOT . \'/admin/includes/footer.php\';
';

        // install.php
        $files['install.php'] = '<?php
/**
 * ' . $name . ' Installation Script
 */

declare(strict_types=1);

if (!defined(\'CMS_ROOT\')) {
    define(\'CMS_ROOT\', dirname(dirname(__DIR__)));
}

// Installation code here
// Example: Create database tables, set default options

return true;
';

        // uninstall.php
        $files['uninstall.php'] = '<?php
/**
 * ' . $name . ' Uninstallation Script
 */

declare(strict_types=1);

if (!defined(\'CMS_ROOT\')) {
    define(\'CMS_ROOT\', dirname(dirname(__DIR__)));
}

// Cleanup code here
// Example: Remove database tables, delete options

return true;
';

        return $files;
    }

    /**
     * Create plugin files on disk
     */
    private static function createPluginFiles(string $pluginDir, array $files): array
    {
        // Create main plugin directory
        if (!@mkdir($pluginDir, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create plugin directory'];
        }

        foreach ($files as $filename => $content) {
            $filePath = $pluginDir . '/' . $filename;
            $fileDir = dirname($filePath);

            // Create subdirectories if needed
            if (!is_dir($fileDir)) {
                if (!@mkdir($fileDir, 0755, true)) {
                    return ['success' => false, 'error' => 'Failed to create directory: ' . $fileDir];
                }
            }

            // Write file
            if (@file_put_contents($filePath, $content) === false) {
                return ['success' => false, 'error' => 'Failed to write file: ' . $filename];
            }
        }

        return ['success' => true];
    }

    /**
     * Preview plugin structure without creating
     */
    public static function preview(array $params): array
    {
        $name = trim($params['name'] ?? '');
        $description = trim($params['description'] ?? '');
        $menuSection = $params['menu_section'] ?? 'plugins';

        if (empty($name)) {
            return ['success' => false, 'error' => 'Plugin name is required'];
        }

        $slug = self::generateSlug($name);
        $files = self::createBasicStructure($slug, $name, $description, $menuSection);

        return [
            'success' => true,
            'slug' => $slug,
            'files' => $files
        ];
    }
}
