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
        
        // Frontend can pass current field values (required for sub-page sections not in schema)
        $frontendFields = $body['fields'] ?? null;
        
        $fields = [];
        $sectionLabel = $section;
        
        if ($sectionSchema) {
            // Theme section — use schema
            $sectionLabel = $sectionSchema['label'] ?? $section;
            foreach ($sectionSchema['fields'] as $key => $def) {
                if (in_array($def['type'], ['text', 'textarea'])) {
                    $fields[$key] = $def['label'];
                }
            }
        } elseif ($frontendFields && is_array($frontendFields)) {
            // Sub-page section — use field keys/values from frontend
            foreach ($frontendFields as $key => $currentValue) {
                $fields[$key] = $key;  // Use key name as label
            }
            // Try to extract a section heading from field values
            foreach ($frontendFields as $key => $val) {
                if (stripos($key, 'h2') !== false || stripos($key, 'h1') !== false) {
                    $sectionLabel = $val;
                    break;
                }
            }
        } else {
            Response::json(['ok' => false, 'error' => "Unknown section: {$section}"]);
            return;
        }
        
        if (empty($fields)) {
            Response::json(['ok' => false, 'error' => 'No text fields found in this section']);
            return;
        }
        
        // Build prompt with current values as context for better generation
        $fieldsContext = $frontendFields ? json_encode($frontendFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : json_encode($fields, JSON_PRETTY_PRINT);
        
        $prompt = "Generate improved content for the \"{$sectionLabel}\" section of a website.\n"
                . "Business context: {$context}\n\n"
                . "Current field values to improve:\n"
                . $fieldsContext . "\n\n"
                . "Return JSON with the exact same field keys and new improved text values.\n"
                . "Make the content more engaging, specific, and professional. Keep the same tone and topic.";
        
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

    /* ══════════════════════════════════════════════════════
     * Visual Editor AI — unified endpoint
     * POST /api/visual-editor/ai
     * Body: { action, text, mode, section, field, prompt, element_tag, context }
     * ══════════════════════════════════════════════════════ */
    /**
     * Visual Editor: Save page/article content inline
     * POST /api/visual-editor/save-page
     * Body: { type: "page"|"article", id: int, field: "title"|"content", value: string }
     */
    public function vePageSave(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            Response::json(['ok' => false, 'error' => 'Invalid JSON body']);
            return;
        }

        $type = $body['type'] ?? '';
        $id = (int)($body['id'] ?? 0);
        $field = $body['field'] ?? '';
        $value = $body['value'] ?? '';

        if (!in_array($type, ['page', 'article'])) {
            Response::json(['ok' => false, 'error' => 'Invalid type']);
            return;
        }
        if ($id <= 0) {
            Response::json(['ok' => false, 'error' => 'Invalid ID']);
            return;
        }
        if (!in_array($field, ['title', 'content'])) {
            Response::json(['ok' => false, 'error' => 'Invalid field']);
            return;
        }

        $pdo = \core\Database::connection();
        $table = ($type === 'article') ? 'articles' : 'pages';
        $safeField = $field; // already validated

        $stmt = $pdo->prepare("UPDATE {$table} SET {$safeField} = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$value, $id]);

        if ($stmt->rowCount() > 0) {
            Response::json(['ok' => true]);
        } else {
            Response::json(['ok' => false, 'error' => 'No rows updated (record not found?)']);
        }
    }

    public function veAi(Request $request): void
    {
        $body = $GLOBALS['_JSON_DATA'] ?? json_decode(file_get_contents('php://input'), true);
        if (!is_array($body)) {
            Response::json(['ok' => false, 'error' => 'Invalid JSON body']);
            return;
        }

        $action = trim($body['action'] ?? '');

        switch ($action) {
            case 'rewrite':
                $this->veAiRewrite($body);
                break;
            case 'generate':
                $this->veAiGenerate($body);
                break;
            case 'variants':
                $this->veAiVariants($body);
                break;
            case 'style':
                $this->veAiStyle($body);
                break;
            case 'seo-check':
                $this->veAiSeoCheck($body);
                break;
            default:
                Response::json(['ok' => false, 'error' => 'Unknown action: ' . $action]);
        }
    }

    /**
     * Rewrite text in various modes (paraphrase, seo, punchy, formal, casual, simplify)
     */
    private function veAiRewrite(array $body): void
    {
        $text = trim($body['text'] ?? '');
        $mode = trim($body['mode'] ?? 'paraphrase');
        $field = trim($body['field'] ?? '');
        $section = trim($body['section'] ?? '');

        if ($text === '') {
            Response::json(['ok' => false, 'error' => 'No text to rewrite']);
            return;
        }

        // Map VE modes to ai_content_rewrite modes
        $modeMap = [
            'paraphrase' => 'paraphrase',
            'seo'        => 'seo_optimize',
            'punchy'     => 'summarize',
            'formal'     => 'formalize',
            'casual'     => 'casual',
            'simplify'   => 'simplify',
            'expand'     => 'expand',
        ];

        $rewriteMode = $modeMap[$mode] ?? 'paraphrase';

        // Use ai_content_rewrite if available
        $rewritePath = \CMS_ROOT . '/core/ai_content_rewrite.php';
        if (file_exists($rewritePath)) {
            require_once $rewritePath;
            $result = ai_rewrite_content($text, $rewriteMode, [
                'context' => $this->buildFieldContext($section, $field),
            ]);
            if (!empty($result['ok']) || !empty($result['success'])) {
                Response::json([
                    'ok' => true,
                    'text' => $result['rewritten'] ?? $result['content'] ?? $text,
                    'mode' => $mode,
                ]);
                return;
            }
        }

        // Fallback: use ai_universal_generate directly
        $this->veAiFallbackRewrite($text, $mode, $section, $field);
    }

    /**
     * Fallback rewrite using ai_universal_generate
     */
    private function veAiFallbackRewrite(string $text, string $mode, string $section, string $field): void
    {
        require_once \CMS_ROOT . '/core/ai_content.php';

        $modeInstructions = [
            'paraphrase' => 'Rewrite this text keeping the same meaning but with different wording.',
            'seo'        => 'Rewrite this text to be SEO-optimized. Use natural keyword placement, improve readability, make it compelling for both search engines and readers.',
            'punchy'     => 'Rewrite this text to be more punchy and engaging. Shorter, more impactful. Make every word count.',
            'formal'     => 'Rewrite this text in a more formal, professional tone.',
            'casual'     => 'Rewrite this text in a friendly, conversational tone.',
            'simplify'   => 'Simplify this text. Use shorter sentences and simpler words.',
            'expand'     => 'Expand this text with more detail, examples, and depth.',
        ];

        $instruction = $modeInstructions[$mode] ?? $modeInstructions['paraphrase'];
        $fieldContext = $this->buildFieldContext($section, $field);

        $settings = ai_config_load_full();
        $provider = $settings['default_provider'] ?? 'openai';
        $provConfig = $settings['providers'][$provider] ?? [];
        $model = $provConfig['default_model'] ?? '';

        $result = ai_universal_generate(
            $provider,
            $model,
            "You are an expert copywriter for websites. You rewrite text for specific page elements. "
            . "Context: this is a {$fieldContext}. "
            . "Return ONLY the rewritten text, no explanations, no quotes, no markdown.",
            $instruction . "\n\nOriginal text:\n" . $text,
            ['max_tokens' => 1000, 'temperature' => 0.7]
        );

        if (!empty($result['ok'])) {
            $output = trim($result['content'] ?? $result['text'] ?? '');
            // Strip surrounding quotes if AI wrapped them
            $output = preg_replace('/^["\'](.*)["\']$/s', '$1', $output);
            Response::json(['ok' => true, 'text' => $output, 'mode' => $mode]);
        } else {
            Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
        }
    }

    /**
     * Generate fresh content for a section field
     */
    private function veAiGenerate(array $body): void
    {
        $section = trim($body['section'] ?? '');
        $field = trim($body['field'] ?? '');
        $context = trim($body['context'] ?? '');
        $elementTag = trim($body['element_tag'] ?? '');

        $themeSlug = $this->resolveTheme();
        $schema = theme_get_schema($themeSlug);
        $sectionSchema = $schema[$section] ?? null;

        // Build field info
        $fieldLabel = $field;
        $fieldType = 'text';
        if ($sectionSchema && isset($sectionSchema['fields'][$field])) {
            $fieldLabel = $sectionSchema['fields'][$field]['label'] ?? $field;
            $fieldType = $sectionSchema['fields'][$field]['type'] ?? 'text';
        }

        $sectionLabel = $sectionSchema['label'] ?? $section;

        // Determine max length from element type
        $maxLen = 500;
        if (in_array($elementTag, ['h1', 'h2', 'h3'])) $maxLen = 80;
        elseif ($elementTag === 'h4' || $elementTag === 'h5') $maxLen = 100;
        elseif ($elementTag === 'a' || $elementTag === 'button' || $elementTag === 'span') $maxLen = 40;
        elseif ($elementTag === 'p') $maxLen = 300;

        require_once \CMS_ROOT . '/core/ai_content.php';
        $settings = ai_config_load_full();
        $provider = $settings['default_provider'] ?? 'openai';
        $provConfig = $settings['providers'][$provider] ?? [];
        $model = $provConfig['default_model'] ?? '';

        $prompt = "Generate website copy for a \"{$sectionLabel}\" section, specifically the \"{$fieldLabel}\" field.\n";
        if ($context) $prompt .= "Business/website context: {$context}\n";
        $prompt .= "Element type: {$elementTag}\n";
        $prompt .= "Maximum length: ~{$maxLen} characters.\n";
        $prompt .= "Make it compelling, professional, and specific (not generic).\n";
        $prompt .= "Return ONLY the text, no explanations, no quotes, no markdown.";

        $result = ai_universal_generate(
            $provider,
            $model,
            'You are a professional website copywriter. Generate copy for specific page elements. Be specific and creative, avoid generic phrases like "Welcome to our website".',
            $prompt,
            ['max_tokens' => 500, 'temperature' => 0.8]
        );

        if (!empty($result['ok'])) {
            $output = trim($result['content'] ?? $result['text'] ?? '');
            $output = preg_replace('/^["\'](.*)["\']$/s', '$1', $output);
            Response::json(['ok' => true, 'text' => $output]);
        } else {
            Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
        }
    }

    /**
     * Generate A/B variants for a text field
     */
    private function veAiVariants(array $body): void
    {
        $text = trim($body['text'] ?? '');
        $field = trim($body['field'] ?? '');
        $section = trim($body['section'] ?? '');
        $count = min(max((int)($body['count'] ?? 3), 2), 5);

        if ($text === '') {
            Response::json(['ok' => false, 'error' => 'No text provided']);
            return;
        }

        // Try AICopywriter first
        $copywriterPath = \CMS_ROOT . '/core/ai_copywriter.php';
        if (file_exists($copywriterPath)) {
            require_once $copywriterPath;
            try {
                $copywriter = new \Core\AICopywriter();
                // Determine copy type from field name
                $copyType = 'custom';
                $fieldLower = strtolower($field);
                if (str_contains($fieldLower, 'headline') || str_contains($fieldLower, 'title')) {
                    $copyType = 'headline';
                } elseif (str_contains($fieldLower, 'subtitle') || str_contains($fieldLower, 'subhead')) {
                    $copyType = 'subheadline';
                } elseif (str_contains($fieldLower, 'cta') || str_contains($fieldLower, 'button')) {
                    $copyType = 'cta';
                } elseif (str_contains($fieldLower, 'description') || str_contains($fieldLower, 'desc')) {
                    $copyType = 'body_copy';
                }

                $result = $copywriter->generate([
                    'copy_type' => $copyType,
                    'topic' => $text,
                    'context' => $this->buildFieldContext($section, $field),
                    'tone' => 'professional',
                    'variants' => $count,
                ]);

                if (!empty($result['success']) && !empty($result['copies'])) {
                    $variants = array_map(fn($c) => $c['text'] ?? $c['content'] ?? $c, $result['copies']);
                    Response::json(['ok' => true, 'variants' => $variants]);
                    return;
                }
            } catch (\Throwable $e) {
                // Fall through to fallback
            }
        }

        // Fallback: ai_universal_generate
        require_once \CMS_ROOT . '/core/ai_content.php';
        $settings = ai_config_load_full();
        $provider = $settings['default_provider'] ?? 'openai';
        $provConfig = $settings['providers'][$provider] ?? [];
        $model = $provConfig['default_model'] ?? '';

        $result = ai_universal_generate(
            $provider,
            $model,
            'You are a copywriter generating A/B test variants. Return a JSON array of strings.',
            "Generate {$count} alternative versions of this website text. Keep similar length and intent.\n\nOriginal: \"{$text}\"\n\nReturn ONLY a JSON array of strings, e.g. [\"variant 1\", \"variant 2\", \"variant 3\"]",
            ['max_tokens' => 1000, 'temperature' => 0.9]
        );

        if (!empty($result['ok'])) {
            $raw = trim($result['content'] ?? $result['text'] ?? '');
            $variants = json_decode($raw, true);
            if (is_array($variants)) {
                Response::json(['ok' => true, 'variants' => $variants]);
            } else {
                // Try to extract from text
                $lines = array_filter(array_map('trim', explode("\n", $raw)));
                $variants = array_values(array_map(fn($l) => preg_replace('/^\d+[\.\)]\s*/', '', trim($l, '"')), $lines));
                Response::json(['ok' => true, 'variants' => array_slice($variants, 0, $count)]);
            }
        } else {
            Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
        }
    }

    /**
     * AI style + layout suggestions — freeform prompt → CSS properties + layout commands
     */
    private function veAiStyle(array $body): void
    {
        $prompt = trim($body['prompt'] ?? '');
        $elementTag = trim($body['element_tag'] ?? 'div');
        $elementCss = $body['element_css'] ?? [];
        $tsKey = trim($body['ts_key'] ?? '');
        $sectionOrder = $body['section_order'] ?? [];
        $pageStructure = $body['page_structure'] ?? [];

        if ($prompt === '') {
            Response::json(['ok' => false, 'error' => 'No prompt provided']);
            return;
        }

        require_once \CMS_ROOT . '/core/ai_content.php';
        $settings = ai_config_load_full();
        $provider = $settings['default_provider'] ?? 'openai';
        $provConfig = $settings['providers'][$provider] ?? [];
        $model = $provConfig['default_model'] ?? '';

        $currentCssStr = '';
        if (!empty($elementCss) && is_array($elementCss)) {
            foreach ($elementCss as $prop => $val) {
                $currentCssStr .= "  {$prop}: {$val};\n";
            }
        }

        // Build page context for layout awareness
        $pageContext = '';
        if (!empty($sectionOrder)) {
            $pageContext .= "Current section order: " . implode(' → ', $sectionOrder) . "\n";
        }
        if (!empty($pageStructure)) {
            $pageContext .= "Page structure:\n";
            foreach ($pageStructure as $sec) {
                $id = $sec['id'] ?? '?';
                $blocks = $sec['blocks'] ?? [];
                $layout = $sec['layout'] ?? '';
                $pageContext .= "  [{$id}] layout:{$layout} blocks: " . implode(', ', $blocks) . "\n";
            }
        }

        $systemPrompt = <<<SYS
You are an expert web designer. Given a user's design/layout request, return a JSON object with TWO optional keys:

1. "css" — CSS properties to apply to the selected element (kebab-case keys, string values)
2. "layout" — layout commands (optional, only when the user asks to rearrange/restructure)

Layout commands can include:
- "section_order": ["hero","features","about","cta"] — reorder page sections
- "parent_css": {"flex-direction":"row","gap":"24px",...} — CSS for the parent container of current element
- "sibling_order": [2,0,1] — reorder blocks within their container (indices)

CSS rules:
- Use standard CSS properties only
- Colors: hex (#rrggbb) or rgba()
- Units: px, em, rem, %
- Gradients: full linear-gradient()/radial-gradient() syntax
- Layout: flex-direction, grid-template-columns, gap, justify-content, align-items, order, etc.
- Be creative but tasteful

Examples:

Style-only request:
{"css": {"background": "linear-gradient(135deg, #667eea, #764ba2)", "padding": "60px 40px"}}

Layout request ("swap the two columns"):
{"css": {}, "layout": {"parent_css": {"flex-direction": "row-reverse"}}}

Section reorder ("move features above about"):
{"css": {}, "layout": {"section_order": ["hero","features","about","cta","parallax"]}}

Combined ("make hero darker and move it after about"):
{"css": {"background": "#0a0a0a"}, "layout": {"section_order": ["about","hero","features","cta"]}}

Grid change ("make it 3 columns"):
{"css": {}, "layout": {"parent_css": {"grid-template-columns": "repeat(3, 1fr)", "gap": "24px"}}}

Return ONLY the JSON object.
SYS;

        $userPrompt = "Selected element: <{$elementTag}> (data-ts=\"{$tsKey}\")\n";
        if ($currentCssStr) {
            $userPrompt .= "Current styles:\n{$currentCssStr}\n";
        }
        if ($pageContext) {
            $userPrompt .= "\n{$pageContext}\n";
        }
        $userPrompt .= "\nUser request: {$prompt}";

        $result = ai_universal_generate(
            $provider,
            $model,
            $systemPrompt,
            $userPrompt,
            ['max_tokens' => 1500, 'temperature' => 0.7]
        );

        if (!empty($result['ok'])) {
            $raw = trim($result['content'] ?? $result['text'] ?? '');
            // Extract JSON — may contain nested objects
            if (preg_match('/\{[\s\S]*\}/s', $raw, $m)) {
                $parsed = json_decode($m[0], true);
                if (is_array($parsed)) {
                    $response = ['ok' => true];

                    // Filter CSS properties
                    $css = $parsed['css'] ?? $parsed;
                    if (is_array($css)) {
                        // If top-level has non-layout keys, treat whole thing as CSS (backward compat)
                        if (!isset($parsed['css']) && !isset($parsed['layout'])) {
                            $css = $parsed;
                        }
                        $allowed = $this->getAllowedCssProperties();
                        $filtered = [];
                        foreach ($css as $prop => $val) {
                            if ($prop === 'layout' || $prop === 'css') continue;
                            $clean = preg_replace('/[^a-z0-9-]/', '', strtolower($prop));
                            if (in_array($clean, $allowed)) {
                                $filtered[$clean] = (string)$val;
                            }
                        }
                        $response['css'] = $filtered;
                    }

                    // Extract layout commands
                    if (!empty($parsed['layout']) && is_array($parsed['layout'])) {
                        $layout = [];

                        // Section reorder
                        if (!empty($parsed['layout']['section_order']) && is_array($parsed['layout']['section_order'])) {
                            $layout['section_order'] = array_values(array_map('strval', $parsed['layout']['section_order']));
                        }

                        // Parent CSS
                        if (!empty($parsed['layout']['parent_css']) && is_array($parsed['layout']['parent_css'])) {
                            $allowed = $this->getAllowedCssProperties();
                            $parentCss = [];
                            foreach ($parsed['layout']['parent_css'] as $prop => $val) {
                                $clean = preg_replace('/[^a-z0-9-]/', '', strtolower($prop));
                                if (in_array($clean, $allowed)) {
                                    $parentCss[$clean] = (string)$val;
                                }
                            }
                            if ($parentCss) $layout['parent_css'] = $parentCss;
                        }

                        // Sibling order
                        if (isset($parsed['layout']['sibling_order']) && is_array($parsed['layout']['sibling_order'])) {
                            $layout['sibling_order'] = array_map('intval', $parsed['layout']['sibling_order']);
                        }

                        if (!empty($layout)) $response['layout'] = $layout;
                    }

                    Response::json($response);
                    return;
                }
            }
            Response::json(['ok' => false, 'error' => 'AI did not return valid JSON', 'raw' => substr($raw, 0, 500)]);
        } else {
            Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
        }
    }

    /**
     * Quick SEO check for visible text content
     */
    private function veAiSeoCheck(array $body): void
    {
        $texts = $body['texts'] ?? []; // { "hero.headline": "text", "hero.description": "text", ... }
        $url = trim($body['url'] ?? '');
        $pageTitle = trim($body['page_title'] ?? '');

        if (empty($texts)) {
            Response::json(['ok' => false, 'error' => 'No texts provided']);
            return;
        }

        require_once \CMS_ROOT . '/core/ai_content.php';
        $settings = ai_config_load_full();
        $provider = $settings['default_provider'] ?? 'openai';
        $provConfig = $settings['providers'][$provider] ?? [];
        $model = $provConfig['default_model'] ?? '';

        $textBlock = '';
        foreach ($texts as $key => $val) {
            $textBlock .= "[{$key}]: {$val}\n";
        }

        $result = ai_universal_generate(
            $provider,
            $model,
            'You are an SEO expert. Analyze visible page text and return actionable SEO recommendations as JSON.',
            "Analyze the visible text content of this page for SEO.\n\nURL: {$url}\nTitle: {$pageTitle}\n\nVisible text by element:\n{$textBlock}\n\n"
            . "Return JSON:\n{\n"
            . "  \"score\": 0-100,\n"
            . "  \"summary\": \"one-line SEO assessment\",\n"
            . "  \"issues\": [{\"field\": \"hero.headline\", \"issue\": \"...\", \"suggestion\": \"...\"}],\n"
            . "  \"improvements\": {\"hero.headline\": \"suggested better text\", ...},\n"
            . "  \"meta_title\": \"suggested page title\",\n"
            . "  \"meta_description\": \"suggested meta description\"\n"
            . "}",
            ['max_tokens' => 2000, 'temperature' => 0.5]
        );

        if (!empty($result['ok'])) {
            $raw = trim($result['content'] ?? $result['text'] ?? '');
            if (preg_match('/\{[\s\S]*\}/s', $raw, $m)) {
                $parsed = json_decode($m[0], true);
                if (is_array($parsed)) {
                    Response::json(['ok' => true, 'seo' => $parsed]);
                    return;
                }
            }
            Response::json(['ok' => false, 'error' => 'Could not parse SEO analysis', 'raw' => substr($raw, 0, 500)]);
        } else {
            Response::json(['ok' => false, 'error' => $result['error'] ?? 'AI generation failed']);
        }
    }

    /**
     * Build context string for a field (used in prompts)
     */
    private function buildFieldContext(string $section, string $field): string
    {
        $themeSlug = $this->resolveTheme();
        $schema = theme_get_schema($themeSlug);
        $sectionSchema = $schema[$section] ?? null;

        $parts = [];
        if ($sectionSchema) {
            $parts[] = "website section: \"{$sectionSchema['label']}\"";
            if (isset($sectionSchema['fields'][$field])) {
                $parts[] = "field: \"{$sectionSchema['fields'][$field]['label']}\"";
                $type = $sectionSchema['fields'][$field]['type'] ?? '';
                if ($type) $parts[] = "type: {$type}";
            }
        } else {
            $parts[] = "section: {$section}";
            if ($field) $parts[] = "field: {$field}";
        }
        return implode(', ', $parts);
    }

    /**
     * Whitelist of allowed CSS properties for AI style suggestions
     */
    private function getAllowedCssProperties(): array
    {
        return [
            'color', 'background', 'background-color', 'background-image', 'background-size',
            'background-position', 'background-repeat', 'background-blend-mode',
            'font-size', 'font-weight', 'font-style', 'font-family', 'line-height',
            'letter-spacing', 'text-align', 'text-transform', 'text-decoration', 'text-shadow',
            'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
            'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
            'border', 'border-width', 'border-style', 'border-color', 'border-radius',
            'border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius',
            'box-shadow', 'opacity', 'filter', 'transform', 'transition',
            'width', 'max-width', 'min-width', 'height', 'max-height', 'min-height',
            'display', 'flex-direction', 'justify-content', 'align-items', 'gap',
            'overflow', 'position', 'top', 'right', 'bottom', 'left', 'z-index',
            'backdrop-filter', 'mix-blend-mode', 'outline', 'outline-offset',
            'word-spacing', 'white-space', 'text-overflow', 'cursor',
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // MENU MANAGEMENT — Navigation tab in Theme Studio
    // ═══════════════════════════════════════════════════════════

    /**
     * GET /api/theme-studio/menus — Get all menus + items for active theme
     */
    public function apiMenus(Request $request): void
    {
        $theme = $this->resolveTheme();
        $pdo = db();

        // Get menus for this theme (+ fallback global menus)
        $stmt = $pdo->prepare("
            SELECT id, name, slug, location, theme_slug, description
            FROM menus
            WHERE theme_slug = ? OR theme_slug IS NULL
            ORDER BY CASE WHEN theme_slug = ? THEN 0 ELSE 1 END, location ASC
        ");
        $stmt->execute([$theme, $theme]);
        $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Deduplicate by location — theme-specific wins
        $byLocation = [];
        foreach ($menus as $menu) {
            $loc = $menu['location'] ?: $menu['slug'];
            if (!isset($byLocation[$loc]) || $menu['theme_slug'] === $theme) {
                $byLocation[$loc] = $menu;
            }
        }

        // Get items for each menu
        $itemStmt = $pdo->prepare("
            SELECT id, menu_id, parent_id, title, url, page_id, target, css_class,
                   sort_order, icon, is_active, open_in_new_tab
            FROM menu_items
            WHERE menu_id = ?
            ORDER BY sort_order ASC, id ASC
        ");

        // Get pages for the page picker
        $pages = $pdo->query("SELECT id, title, slug, template FROM pages WHERE status = 'published' ORDER BY title ASC")
                      ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($byLocation as $loc => $menu) {
            $itemStmt->execute([$menu['id']]);
            $items = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);
            $menu['items'] = $items;
            $result[] = $menu;
        }

        Response::json(['ok' => true, 'menus' => $result, 'pages' => $pages]);
    }

    /**
     * POST /api/theme-studio/menus/item — Add a menu item
     */
    public function apiMenuAddItem(Request $request): void
    {
        $theme = $this->resolveTheme();
        $data = $GLOBALS['_JSON_DATA'] ?? [];
        $pdo = db();

        $menuId   = (int)($data['menu_id'] ?? 0);
        $title    = trim($data['title'] ?? '');
        $url      = trim($data['url'] ?? '');
        $pageId   = !empty($data['page_id']) ? (int)$data['page_id'] : null;
        $target   = $data['target'] ?? '_self';
        $icon     = trim($data['icon'] ?? '');
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        if (empty($title)) {
            Response::json(['ok' => false, 'error' => 'Title is required'], 400);
            return;
        }

        // If no menu_id given, find or create the header menu for this theme
        if ($menuId === 0) {
            $location = $data['location'] ?? 'header';
            $stmt = $pdo->prepare("SELECT id FROM menus WHERE theme_slug = ? AND location = ? LIMIT 1");
            $stmt->execute([$theme, $location]);
            $menu = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($menu) {
                $menuId = (int)$menu['id'];
            } else {
                // Create theme-specific menu
                $menuName = ucfirst($theme) . ' ' . ucfirst($location);
                $menuSlug = $theme . '-' . $location;
                $pdo->prepare("INSERT INTO menus (name, slug, location, theme_slug, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())")
                    ->execute([$menuName, $menuSlug, $location, $theme]);
                $menuId = (int)$pdo->lastInsertId();
            }
        }

        // Verify menu belongs to theme
        $stmt = $pdo->prepare("SELECT id, theme_slug FROM menus WHERE id = ?");
        $stmt->execute([$menuId]);
        $menu = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$menu) {
            Response::json(['ok' => false, 'error' => 'Menu not found'], 404);
            return;
        }

        // Get next sort order
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) + 1 FROM menu_items WHERE menu_id = ?");
        $stmt->execute([$menuId]);
        $sortOrder = (int)$stmt->fetchColumn();

        // If page_id is set, resolve URL from page
        if ($pageId) {
            $stmt = $pdo->prepare("SELECT slug, title FROM pages WHERE id = ?");
            $stmt->execute([$pageId]);
            $page = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($page) {
                $url = $url ?: '/' . $page['slug'];
                $title = $title ?: $page['title'];
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO menu_items (menu_id, parent_id, title, url, page_id, target, css_class, sort_order, icon, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, '', ?, ?, 1, NOW())
        ");
        $stmt->execute([$menuId, $parentId, $title, $url, $pageId, $target, $sortOrder, $icon]);
        $itemId = (int)$pdo->lastInsertId();

        Response::json(['ok' => true, 'item_id' => $itemId, 'sort_order' => $sortOrder]);
    }

    /**
     * POST /api/theme-studio/menus/item/update — Update a menu item
     */
    public function apiMenuUpdateItem(Request $request): void
    {
        $data = $GLOBALS['_JSON_DATA'] ?? [];
        $pdo = db();

        $itemId = (int)($data['item_id'] ?? 0);
        if ($itemId === 0) {
            Response::json(['ok' => false, 'error' => 'item_id is required'], 400);
            return;
        }

        // Verify item exists
        $stmt = $pdo->prepare("SELECT mi.*, m.theme_slug FROM menu_items mi JOIN menus m ON mi.menu_id = m.id WHERE mi.id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$item) {
            Response::json(['ok' => false, 'error' => 'Menu item not found'], 404);
            return;
        }

        $fields = [];
        $params = [];

        foreach (['title', 'url', 'icon', 'target', 'css_class'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = trim($data[$field]);
            }
        }
        if (array_key_exists('page_id', $data)) {
            $fields[] = "page_id = ?";
            $params[] = !empty($data['page_id']) ? (int)$data['page_id'] : null;
        }
        if (array_key_exists('is_active', $data)) {
            $fields[] = "is_active = ?";
            $params[] = (int)$data['is_active'];
        }
        if (array_key_exists('open_in_new_tab', $data)) {
            $fields[] = "open_in_new_tab = ?";
            $params[] = (int)$data['open_in_new_tab'];
        }

        if (empty($fields)) {
            Response::json(['ok' => false, 'error' => 'No fields to update'], 400);
            return;
        }

        $fields[] = "updated_at = NOW()";
        $params[] = $itemId;

        $pdo->prepare("UPDATE menu_items SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);

        Response::json(['ok' => true]);
    }

    /**
     * POST /api/theme-studio/menus/item/delete — Delete a menu item
     */
    public function apiMenuDeleteItem(Request $request): void
    {
        $data = $GLOBALS['_JSON_DATA'] ?? [];
        $pdo = db();

        $itemId = (int)($data['item_id'] ?? 0);
        if ($itemId === 0) {
            Response::json(['ok' => false, 'error' => 'item_id is required'], 400);
            return;
        }

        // Re-parent children (promote to top-level)
        $pdo->prepare("UPDATE menu_items SET parent_id = NULL WHERE parent_id = ?")->execute([$itemId]);

        // Delete item
        $pdo->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$itemId]);

        Response::json(['ok' => true]);
    }

    /**
     * POST /api/theme-studio/menus/reorder — Reorder menu items
     */
    public function apiMenuReorder(Request $request): void
    {
        $data = $GLOBALS['_JSON_DATA'] ?? [];
        $pdo = db();

        $order = $data['order'] ?? [];
        if (empty($order) || !is_array($order)) {
            Response::json(['ok' => false, 'error' => 'order array is required'], 400);
            return;
        }

        $stmt = $pdo->prepare("UPDATE menu_items SET sort_order = ?, parent_id = ? WHERE id = ?");
        foreach ($order as $idx => $entry) {
            $id = (int)($entry['id'] ?? 0);
            $parentId = !empty($entry['parent_id']) ? (int)$entry['parent_id'] : null;
            if ($id > 0) {
                $stmt->execute([$idx, $parentId, $id]);
            }
        }

        Response::json(['ok' => true]);
    }

}
