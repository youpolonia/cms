<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;

class ThemeStudioController
{
    /**
     * Resolve theme slug — supports ?theme=slug override for previewing non-active themes
     */
    private function resolveTheme(): string
    {
        if (!empty($_GET['theme'])) {
            $slug = preg_replace('/[^a-z0-9\-_]/', '', strtolower($_GET['theme']));
            if ($slug && is_dir(\CMS_ROOT . '/themes/' . $slug)) {
                // Override get_active_theme() for this request
                $GLOBALS['_active_theme_override'] = $slug;
                return $slug;
            }
        }
        return get_active_theme();
    }

    /**
     * Full-screen Theme Studio UI
     */
    public function index(Request $request): void
    {
        $themeSlug = $this->resolveTheme();
        $themeConfig = get_theme_config($themeSlug);
        $schema = theme_get_schema($themeSlug);
        $values = theme_get_all($themeSlug);
        $history = theme_get_history($themeSlug, 10);
        
        // Check if AI is available
        $aiAvailable = false;
        $pexelsAvailable = false;
        try {
            $aiDir = \CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai';
            $corePath = $aiDir . '/class-jtb-ai-core.php';
            $pexelsPath = $aiDir . '/class-jtb-ai-pexels.php';
            if (file_exists($corePath)) {
                require_once $corePath;
                $ai = \JessieThemeBuilder\JTB_AI_Core::getInstance();
                $aiAvailable = $ai->isConfigured();
            }
            if (file_exists($pexelsPath)) {
                require_once $pexelsPath;
                $pexelsAvailable = \JessieThemeBuilder\JTB_AI_Pexels::isConfigured();
            }
        } catch (\Throwable $e) {
            // AI not available — that's OK
        }
        
        // Full-screen view (own layout, no admin topbar)
        $data = [
            'themeSlug' => $themeSlug,
            'themeName' => $themeConfig['name'] ?? ucfirst($themeSlug),
            'schema' => $schema,
            'values' => $values,
            'history' => $history,
            'aiAvailable' => $aiAvailable,
            'pexelsAvailable' => $pexelsAvailable,
            'csrfToken' => csrf_token(),
        ];
        
        extract($data);
        require \CMS_APP . '/views/admin/theme-studio/index.php';
        exit;
    }
    
    /**
     * Preview endpoint — renders frontend with studio data attributes
     */
    public function preview(Request $request): void
    {
        // Allow previewing non-active themes via ?theme=slug
        $this->resolveTheme();

        // Set flag so templates can add data-studio-field attributes
        define('THEME_STUDIO_PREVIEW', true);
        
        // Render the homepage through normal front-end flow
        $_SERVER['REQUEST_URI'] = '/';
        $controller = new \App\Controllers\Front\HomeController();
        $controller->index($request);
    }
    
    // ─── API Endpoints ────────────────────────────────────────
    
    /**
     * GET /api/theme-studio/schema
     */
    public function apiSchema(Request $request): void
    {
        $themeSlug = $this->resolveTheme();
        $config = get_theme_config($themeSlug);
        
        Response::json([
            'ok' => true,
            'theme' => $themeSlug,
            'name' => $config['name'] ?? $themeSlug,
            'schema' => theme_get_schema($themeSlug),
        ]);
    }
    
    /**
     * GET /api/theme-studio/values
     */
    public function apiValues(Request $request): void
    {
        $themeSlug = $this->resolveTheme();
        
        Response::json([
            'ok' => true,
            'theme' => $themeSlug,
            'values' => theme_get_all($themeSlug),
        ]);
    }
    
    /**
     * POST /api/theme-studio/save
     * Body: JSON { "data": { "section": { "key": "value" } }, "label": "optional" }
     */
    public function apiSave(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        if (empty($body['data']) || !is_array($body['data'])) {
            Response::json(['ok' => false, 'error' => 'No data provided']);
            return;
        }
        
        $themeSlug = $this->resolveTheme();
        $label = $body['label'] ?? 'Manual save';
        
        // Save snapshot before changes
        theme_save_snapshot($themeSlug, 'Before: ' . $label);
        
        // Apply changes
        $count = theme_set_bulk($themeSlug, $body['data']);
        
        Response::json([
            'ok' => true,
            'saved' => $count,
            'values' => theme_get_all($themeSlug),
        ]);
    }
    
    /**
     * POST /api/theme-studio/reset
     * Body: JSON { "section": "hero" } or {} for full reset
     */
    public function apiReset(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $themeSlug = $this->resolveTheme();
        $section = $body['section'] ?? null;
        
        // Save snapshot before reset
        theme_save_snapshot($themeSlug, 'Before reset' . ($section ? ": {$section}" : ': all'));
        
        theme_reset($themeSlug, $section);
        
        Response::json([
            'ok' => true,
            'values' => theme_get_all($themeSlug),
        ]);
    }
    
    /**
     * GET /api/theme-studio/history
     */
    public function apiHistory(Request $request): void
    {
        $themeSlug = $this->resolveTheme();
        
        Response::json([
            'ok' => true,
            'history' => theme_get_history($themeSlug, 30),
        ]);
    }
    
    /**
     * POST /api/theme-studio/restore
     * Body: JSON { "id": 123 }
     */
    public function apiRestore(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $id = (int)($body['id'] ?? 0);
        
        if ($id <= 0) {
            Response::json(['ok' => false, 'error' => 'Invalid snapshot ID']);
            return;
        }
        
        $themeSlug = $this->resolveTheme();
        $result = theme_restore_snapshot($themeSlug, $id);
        
        Response::json([
            'ok' => $result,
            'values' => theme_get_all($themeSlug),
            'error' => $result ? null : 'Snapshot not found or restore failed',
        ]);
    }
    
    /**
     * POST /api/theme-studio/upload
     * Multipart form: file + section + field
     */
    public function apiUpload(Request $request): void
    {
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Response::json(['ok' => false, 'error' => 'No file uploaded']);
            return;
        }
        
        $file = $_FILES['file'];
        $section = $_POST['section'] ?? 'brand';
        $field = $_POST['field'] ?? 'logo';
        
        // Validate image
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
        
        if (!isset($allowed[$mime])) {
            Response::json(['ok' => false, 'error' => 'Invalid image type: ' . $mime]);
            return;
        }
        
        // Max 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            Response::json(['ok' => false, 'error' => 'File too large (max 5MB)']);
            return;
        }
        
        // Save file
        $ext = $allowed[$mime];
        $filename = 'studio_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = \CMS_ROOT . '/uploads/media';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filepath = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            Response::json(['ok' => false, 'error' => 'Failed to save file']);
            return;
        }
        
        $url = '/uploads/media/' . $filename;
        
        // Save to customizations
        $themeSlug = $this->resolveTheme();
        theme_set($themeSlug, $section, $field, $url, 'image');
        
        Response::json([
            'ok' => true,
            'url' => $url,
            'filename' => $filename,
        ]);
    }
    
    // ─── Section Manager Endpoints ──────────────────────────────
    
    /**
     * GET /api/theme-studio/sections
     * Returns section definitions, current order, and enabled state for the active theme.
     */
    public function apiSections(Request $request): void
    {
        $themeSlug = $this->resolveTheme();
        $config = get_theme_config($themeSlug);
        $sections = $config['homepage_sections'] ?? [];
        $currentOrder = theme_get_section_order($themeSlug);

        // Build sections with enabled state
        $sectionMap = [];
        foreach ($sections as $sec) {
            $sectionMap[$sec['id']] = $sec;
        }

        $orderedSections = [];
        foreach ($currentOrder as $id) {
            if (isset($sectionMap[$id])) {
                $def = $sectionMap[$id];
                $def['enabled'] = theme_section_enabled($id, $themeSlug);
                $orderedSections[] = $def;
                unset($sectionMap[$id]);
            }
        }
        // Append any sections not in the current order (new sections added to theme.json)
        foreach ($sectionMap as $id => $def) {
            $def['enabled'] = theme_section_enabled($id, $themeSlug);
            $orderedSections[] = $def;
        }

        Response::json([
            'ok' => true,
            'theme' => $themeSlug,
            'sections' => $orderedSections,
            'order' => $currentOrder,
        ]);
    }

    /**
     * POST /api/theme-studio/sections/save
     * Saves new order and enabled states.
     * Body: { "order": ["hero","about",...], "enabled": {"hero":true,"about":false,...} }
     */
    public function apiSectionsSave(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $themeSlug = $this->resolveTheme();

        $order = $body['order'] ?? null;
        $enabled = $body['enabled'] ?? null;

        if (!is_array($order) || empty($order)) {
            Response::json(['ok' => false, 'error' => 'Invalid order data']);
            return;
        }

        // Validate section IDs against theme config
        $config = get_theme_config($themeSlug);
        $validIds = array_column($config['homepage_sections'] ?? [], 'id');
        $order = array_values(array_filter($order, fn($id) => in_array($id, $validIds)));

        if (empty($order)) {
            Response::json(['ok' => false, 'error' => 'No valid section IDs']);
            return;
        }

        // Save order as JSON
        theme_set($themeSlug, 'sections', 'order', json_encode($order), 'json');

        // Save enabled states
        if (is_array($enabled)) {
            foreach ($enabled as $id => $state) {
                if (in_array($id, $validIds)) {
                    theme_set($themeSlug, 'sections', $id . '_enabled', $state ? '1' : '0', 'toggle');
                }
            }
        }

        Response::json([
            'ok' => true,
            'order' => $order,
        ]);
    }

    // ─── AI Endpoints ─────────────────────────────────────────
    
    /**
     * POST /api/theme-studio/ai/customize
     * Body: { "prompt": "I'm a dentist in Warsaw", "mode": "full"|"targeted" }
     */
    public function aiCustomize(Request $request): void
    {
        $aiCorePath = \CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-core.php';
        if (!file_exists($aiCorePath)) {
            Response::json(['ok' => false, 'error' => 'AI plugin not installed']);
            return;
        }
        
        require_once $aiCorePath;
        $ai = \JessieThemeBuilder\JTB_AI_Core::getInstance();
        
        if (!$ai->isConfigured()) {
            Response::json(['ok' => false, 'error' => 'No AI provider configured. Add your API key in Settings → AI Configuration.']);
            return;
        }
        
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $userPrompt = trim($body['prompt'] ?? '');
        
        if (empty($userPrompt)) {
            Response::json(['ok' => false, 'error' => 'Please describe what you want to change']);
            return;
        }
        
        // Provider/model override from frontend
        $requestProvider = $body['provider'] ?? null;
        $requestModel = $body['model'] ?? null;
        
        if ($requestProvider && in_array($requestProvider, $ai->getAvailableProviders())) {
            $ai->setProvider($requestProvider);
        }
        
        $themeSlug = $this->resolveTheme();
        $schema = theme_get_schema($themeSlug);
        $currentValues = theme_get_all($themeSlug);
        
        $systemPrompt = $this->buildAIPrompt($schema, $currentValues, $themeSlug);
        
        $queryOptions = [
            'system_prompt' => $systemPrompt,
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'json_mode' => true,
        ];
        
        if ($requestModel) {
            $queryOptions['model'] = $requestModel;
        }
        
        $result = $ai->query($userPrompt, $queryOptions);
        
        if ($result['ok'] && !empty($result['json'])) {
            Response::json([
                'ok' => true,
                'changes' => $result['json'],
                'tokens' => $result['tokens_used'] ?? 0,
            ]);
        } elseif ($result['ok'] && !empty($result['text'])) {
            // Try to extract JSON from text
            $text = $result['text'];
            if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
                if ($parsed) {
                    Response::json(['ok' => true, 'changes' => $parsed, 'tokens' => $result['tokens_used'] ?? 0]);
                    return;
                }
            }
            Response::json(['ok' => false, 'error' => 'AI response was not valid JSON', 'raw' => substr($text, 0, 500)]);
        } else {
            Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI request failed']);
        }
    }
    
    /**
     * POST /api/theme-studio/ai/generate-content
     * Body: { "section": "hero", "context": "dental clinic in Warsaw" }
     */
    public function aiGenerateContent(Request $request): void
    {
        $aiCorePath = \CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-core.php';
        if (!file_exists($aiCorePath)) {
            Response::json(['ok' => false, 'error' => 'AI plugin not installed']);
            return;
        }
        
        require_once $aiCorePath;
        $ai = \JessieThemeBuilder\JTB_AI_Core::getInstance();
        
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $section = $body['section'] ?? '';
        $context = $body['context'] ?? '';
        
        // Provider/model override from frontend
        $requestProvider = $body['provider'] ?? null;
        $requestModel = $body['model'] ?? null;
        
        if ($requestProvider && in_array($requestProvider, $ai->getAvailableProviders())) {
            $ai->setProvider($requestProvider);
        }
        
        $schema = theme_get_schema();
        $sectionSchema = $schema[$section] ?? null;
        
        if (!$sectionSchema) {
            Response::json(['ok' => false, 'error' => "Unknown section: {$section}"]);
            return;
        }
        
        $fields = [];
        foreach ($sectionSchema['fields'] as $key => $def) {
            if (in_array($def['type'], ['text', 'textarea'])) {
                $fields[$key] = $def['label'];
            }
        }
        
        $prompt = "Generate content for the \"{$sectionSchema['label']}\" section of a website.\n"
                . "Business context: {$context}\n\n"
                . "Generate values for these fields:\n"
                . json_encode($fields, JSON_PRETTY_PRINT) . "\n\n"
                . "Return JSON with the field keys and generated text values. Be specific and professional, not generic.";
        
        $queryOptions = [
            'system_prompt' => 'You are a professional copywriter. Generate website content based on the business description. Return only valid JSON.',
            'max_tokens' => 2000,
            'temperature' => 0.8,
            'json_mode' => true,
        ];
        
        if ($requestModel) {
            $queryOptions['model'] = $requestModel;
        }
        
        $result = $ai->query($prompt, $queryOptions);
        
        if ($result['ok'] && !empty($result['json'])) {
            Response::json(['ok' => true, 'content' => $result['json']]);
        } else {
            Response::json(['ok' => false, 'error' => $result['error'] ?? 'Failed to generate content']);
        }
    }
    
    /**
     * POST /api/theme-studio/ai/suggest-images
     * Body: { "query": "modern dental office", "count": 8 }
     */
    public function aiSuggestImages(Request $request): void
    {
        $pexelsPath = \CMS_ROOT . '/plugins/jessie-theme-builder/includes/ai/class-jtb-ai-pexels.php';
        if (!file_exists($pexelsPath)) {
            Response::json(['ok' => false, 'error' => 'Pexels integration not available']);
            return;
        }
        
        require_once $pexelsPath;
        
        if (!\JessieThemeBuilder\JTB_AI_Pexels::isConfigured()) {
            Response::json(['ok' => false, 'error' => 'Pexels API key not configured']);
            return;
        }
        
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $query = trim($body['query'] ?? '');
        $count = min(20, max(1, (int)($body['count'] ?? 8)));
        
        if (empty($query)) {
            Response::json(['ok' => false, 'error' => 'Search query required']);
            return;
        }
        
        $results = \JessieThemeBuilder\JTB_AI_Pexels::searchPhotos($query, ['per_page' => $count, 'orientation' => 'landscape']);
        
        Response::json([
            'ok' => true,
            'images' => $results,
        ]);
    }
    
    /**
     * POST /api/theme-studio/ai/color-palette
     * Body: { "seed": "#2d6a4f", "style": "modern"|"warm"|"corporate" }
     */
    public function aiColorPalette(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        $seed = $body['seed'] ?? '#3b82f6';
        $style = $body['style'] ?? 'modern';
        
        // Generate harmonious palette from seed color
        $palette = $this->generateColorPalette($seed, $style);
        
        Response::json([
            'ok' => true,
            'palette' => $palette,
        ]);
    }
    
    // ─── Private Helpers ──────────────────────────────────────
    
    private function buildAIPrompt(array $schema, array $currentValues, string $themeSlug): string
    {
        $schemaJson = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $valuesJson = json_encode($currentValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return <<<PROMPT
You are an expert brand designer and web developer. You customize website themes based on business descriptions.

THEME: {$themeSlug}

CUSTOMIZABLE SCHEMA (sections and their fields):
{$schemaJson}

CURRENT VALUES:
{$valuesJson}

RULES:
- Return a JSON object with the same structure as CURRENT VALUES: {"section": {"key": "value"}}
- Only include fields you want to CHANGE (partial update)
- Colors: use hex codes (#rrggbb), create harmonious palettes
- Content: write professional, specific copy (not lorem ipsum)
- Match tone to the industry (corporate=formal, cafe=warm, tech=modern, medical=trustworthy)
- Headlines: concise, impactful (max ~8 words)
- Subtitles: 1-2 sentences explaining the value proposition
- Toggle fields: use true/false
- For images: describe what image would be ideal (user will search Pexels separately)

Return ONLY valid JSON, no markdown, no explanation.
PROMPT;
    }
    
    private function generateColorPalette(string $seedHex, string $style): array
    {
        // Parse seed color to HSL
        $rgb = $this->hexToRgb($seedHex);
        $hsl = $this->rgbToHsl($rgb[0], $rgb[1], $rgb[2]);
        
        $h = $hsl[0];
        $s = $hsl[1];
        $l = $hsl[2];
        
        switch ($style) {
            case 'warm':
                return [
                    'primary' => $seedHex,
                    'secondary' => $this->hslToHex(($h + 30) % 360, min($s + 5, 100), $l),
                    'accent' => $this->hslToHex(($h + 60) % 360, min($s + 10, 100), min($l + 10, 85)),
                    'background' => '#fffbf5',
                    'surface' => '#1a1410',
                    'text' => '#1a1410',
                ];
            case 'corporate':
                return [
                    'primary' => $seedHex,
                    'secondary' => $this->hslToHex($h, max($s - 20, 10), max($l - 15, 15)),
                    'accent' => $this->hslToHex(($h + 180) % 360, min($s, 70), min($l + 15, 80)),
                    'background' => '#ffffff',
                    'surface' => '#0f172a',
                    'text' => '#0f172a',
                ];
            case 'dark':
                return [
                    'primary' => $seedHex,
                    'secondary' => $this->hslToHex(($h + 30) % 360, $s, min($l + 10, 70)),
                    'accent' => $this->hslToHex(($h + 180) % 360, min($s + 15, 100), min($l + 20, 80)),
                    'background' => '#0f172a',
                    'surface' => '#1e293b',
                    'text' => '#f1f5f9',
                ];
            default: // modern
                return [
                    'primary' => $seedHex,
                    'secondary' => $this->hslToHex(($h + 210) % 360, min($s + 10, 100), $l),
                    'accent' => $this->hslToHex(($h + 45) % 360, min($s + 15, 100), min($l + 15, 85)),
                    'background' => '#ffffff',
                    'surface' => '#0f172a',
                    'text' => '#0f172a',
                ];
        }
    }
    
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
    
    private function rgbToHsl(int $r, int $g, int $b): array
    {
        $r /= 255; $g /= 255; $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        
        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            
            if ($max === $r) $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6;
            elseif ($max === $g) $h = (($b - $r) / $d + 2) / 6;
            else $h = (($r - $g) / $d + 4) / 6;
        }
        
        return [round($h * 360), round($s * 100), round($l * 100)];
    }
    
    private function hslToHex(float $h, float $s, float $l): string
    {
        $h /= 360; $s /= 100; $l /= 100;
        
        if ($s === 0.0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hueToRgb($p, $q, $h + 1/3);
            $g = $this->hueToRgb($p, $q, $h);
            $b = $this->hueToRgb($p, $q, $h - 1/3);
        }
        
        return sprintf('#%02x%02x%02x', round($r * 255), round($g * 255), round($b * 255));
    }
    
    private function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }
    
    // ─── AI Models & Provider Discovery ───────────────────────
    
    /**
     * GET /api/theme-studio/ai/models
     * Returns available AI providers and their models — read dynamically from ai_settings.json.
     * User defines models in config (BYOK), we just expose them here.
     */
    public function aiModels(Request $request): void
    {
        $providers = [];
        $defaultProvider = null;
        $defaultModel = null;
        
        // Load AI config
        $settingsPath = \CMS_ROOT . '/config/ai_settings.json';
        $settings = [];
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true) ?: [];
        }
        
        $defaultProvider = $settings['default_provider'] ?? 'openai';
        $recommended = $settings['recommended_models'] ?? [];
        
        // Provider display names and icons
        $providerMeta = [
            'openai'     => ['label' => 'OpenAI',    'icon' => '🟢'],
            'anthropic'  => ['label' => 'Anthropic',  'icon' => '🟠'],
            'google'     => ['label' => 'Google',     'icon' => '🔵'],
            'deepseek'   => ['label' => 'DeepSeek',   'icon' => '🟣'],
            'huggingface' => ['label' => 'HuggingFace', 'icon' => '🟡'],
            'ollama'     => ['label' => 'Ollama',     'icon' => '⚪'],
        ];
        
        // Build providers list from config — read actual models from ai_settings.json
        foreach ($settings['providers'] ?? [] as $provKey => $provConfig) {
            // Skip disabled or unconfigured providers
            if (empty($provConfig['enabled'])) continue;
            if (empty($provConfig['api_key']) && $provKey !== 'ollama') continue;
            
            // Skip HuggingFace if it only has text/image/vision (not chat models)
            if ($provKey === 'huggingface') {
                $hfModels = $provConfig['models'] ?? [];
                if (!is_array($hfModels) || isset($hfModels['text'])) continue; // Old format, not chat models
            }
            
            $meta = $providerMeta[$provKey] ?? ['label' => ucfirst($provKey), 'icon' => '⚙️'];
            $configuredModel = $provConfig['default_model'] ?? null;
            $configModels = $provConfig['models'] ?? [];
            
            // Build model list from config
            $models = [];
            foreach ($configModels as $modelId => $modelDef) {
                if (!is_array($modelDef)) continue; // Skip non-array entries
                
                $name = $modelDef['name'] ?? $modelId;
                $isLegacy = !empty($modelDef['legacy']);
                $isReasoning = !empty($modelDef['reasoning']) || !empty($modelDef['extended_thinking']);
                $isRecommended = !empty($modelDef['recommended']);
                $maxTokens = $modelDef['max_tokens'] ?? 0;
                $costInput = $modelDef['cost_per_1k_input'] ?? 0;
                
                // Determine tier from model properties
                $tier = 'standard';
                if ($isReasoning) {
                    $tier = 'reasoning';
                } elseif ($isRecommended) {
                    $tier = 'recommended';
                } elseif ($isLegacy) {
                    $tier = 'legacy';
                } elseif ($costInput >= 0.01) {
                    $tier = 'premium';
                } elseif ($costInput >= 0.002) {
                    $tier = 'pro';
                } elseif ($costInput > 0) {
                    $tier = 'fast';
                }
                
                // Build description from properties
                $desc = '';
                if ($maxTokens >= 1000000) {
                    $desc .= (int)($maxTokens / 1000000) . 'M ctx';
                } elseif ($maxTokens >= 1000) {
                    $desc .= (int)($maxTokens / 1000) . 'K ctx';
                }
                if ($costInput > 0) {
                    $desc .= ($desc ? ' · ' : '') . '$' . $costInput . '/1K in';
                }
                if ($isLegacy) $desc .= ($desc ? ' · ' : '') . 'Legacy';
                
                $models[] = [
                    'id' => $modelId,
                    'name' => $name,
                    'tier' => $tier,
                    'desc' => $desc,
                    'legacy' => $isLegacy,
                    'reasoning' => $isReasoning,
                    'recommended' => $isRecommended,
                ];
            }
            
            // Sort: recommended first, then non-legacy, then legacy
            usort($models, function ($a, $b) {
                if ($a['recommended'] !== $b['recommended']) return $b['recommended'] ? 1 : -1;
                if ($a['legacy'] !== $b['legacy']) return $a['legacy'] ? 1 : -1;
                return 0;
            });
            
            if (empty($models)) continue;
            
            $providers[$provKey] = [
                'label' => $meta['label'],
                'icon' => $meta['icon'],
                'models' => $models,
                'configured_model' => $configuredModel,
            ];
            
            if ($provKey === $defaultProvider) {
                $defaultModel = $configuredModel;
            }
        }
        
        Response::json([
            'ok' => true,
            'providers' => $providers,
            'default_provider' => $defaultProvider,
            'default_model' => $defaultModel,
            'recommended' => $recommended,
        ]);
    }
}
