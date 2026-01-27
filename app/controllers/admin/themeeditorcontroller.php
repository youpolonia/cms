<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class ThemeEditorController
{
    private string $themesDir;
    private string $configPath;
    private array $protectedThemes = ['core', 'presets', 'current'];

    public function __construct()
    {
        $this->themesDir = CMS_ROOT . '/themes';
        $this->configPath = CMS_ROOT . '/config_core/theme.php';
    }

    public function edit(Request $request): void
    {
        $name = $request->param('name') ?? '';
        $themeName = preg_replace('/[^a-z0-9_-]/i', '', $name);
        $themePath = $this->themesDir . '/' . $themeName;

        if (empty($themeName) || !is_dir($themePath)) {
            Session::flash('error', 'Theme not found.');
            Response::redirect('/admin/themes');
            return;
        }

        $themeData = $this->loadThemeData($themePath);
        
        render('admin/theme-editor/index', [
            'themeName' => $themeName,
            'themePath' => $themePath,
            'themeData' => $themeData,
            'presets' => $this->getColorPresets(),
            'fonts' => $this->getFontList(),
        ]);
    }

    public function save(Request $request): void
    {
        header('Content-Type: application/json');
        
        $name = $request->param('name') ?? '';
        $themeName = preg_replace('/[^a-z0-9_-]/i', '', $name);
        $themePath = $this->themesDir . '/' . $themeName;

        if (empty($themeName) || !is_dir($themePath)) {
            echo json_encode(['success' => false, 'error' => 'Theme not found']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            return;
        }

        $result = $this->saveThemeData($themePath, $data);
        echo json_encode($result);
    }

    public function preview(Request $request): void
    {
        header('Content-Type: application/json');
        
        $name = $request->param('name') ?? '';
        $themeName = preg_replace('/[^a-z0-9_-]/i', '', $name);
        $themePath = $this->themesDir . '/' . $themeName;

        if (empty($themeName) || !is_dir($themePath)) {
            echo json_encode(['success' => false, 'error' => 'Theme not found']);
            return;
        }

        // Generate preview CSS from posted data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            return;
        }

        $css = $this->generatePreviewCSS($data);
        echo json_encode(['success' => true, 'css' => $css]);
    }

    public function getThemeData(Request $request): void
    {
        header('Content-Type: application/json');
        
        $name = $request->param('name') ?? '';
        $themeName = preg_replace('/[^a-z0-9_-]/i', '', $name);
        $themePath = $this->themesDir . '/' . $themeName;

        if (empty($themeName) || !is_dir($themePath)) {
            echo json_encode(['success' => false, 'error' => 'Theme not found']);
            return;
        }

        $data = $this->loadThemeData($themePath);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function loadThemeData(string $themePath): array
    {
        $data = [
            'info' => [
                'name' => '',
                'description' => '',
                'version' => '1.0.0',
                'author' => '',
            ],
            'colors' => [
                'primary' => '#8b5cf6',
                'secondary' => '#6366f1',
                'accent' => '#ec4899',
                'background' => '#0f0f12',
                'surface' => '#1a1a21',
                'text' => '#ffffff',
                'textMuted' => '#a0a0b0',
                'border' => '#2d2d3a',
                'success' => '#10b981',
                'warning' => '#f59e0b',
                'error' => '#ef4444',
            ],
            'typography' => [
                'fontFamily' => 'Inter',
                'headingFont' => 'Inter',
                'baseFontSize' => '16',
                'lineHeight' => '1.6',
                'h1Size' => '3',
                'h2Size' => '2.25',
                'h3Size' => '1.5',
                'fontWeight' => '400',
                'headingWeight' => '700',
            ],
            'header' => [
                'background' => 'transparent',
                'sticky' => true,
                'blur' => true,
                'height' => '72',
                'logoSize' => '32',
            ],
            'buttons' => [
                'borderRadius' => '8',
                'paddingX' => '24',
                'paddingY' => '12',
                'fontWeight' => '600',
                'uppercase' => false,
                'shadow' => true,
            ],
            'layout' => [
                'containerWidth' => '1200',
                'sectionSpacing' => '100',
                'borderRadius' => '12',
            ],
            'effects' => [
                'shadowStrength' => '20',
                'hoverScale' => '1.02',
                'transitionSpeed' => '200',
            ],
            'customCSS' => '',
        ];

        // Load theme.json
        $jsonPath = $themePath . '/theme.json';
        if (file_exists($jsonPath)) {
            $json = json_decode(file_get_contents($jsonPath), true);
            if ($json) {
                $data['info']['name'] = $json['name'] ?? basename($themePath);
                $data['info']['description'] = $json['description'] ?? '';
                $data['info']['version'] = $json['version'] ?? '1.0.0';
                $data['info']['author'] = $json['author'] ?? '';
                
                if (!empty($json['colors'])) {
                    foreach ($json['colors'] as $key => $value) {
                        $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
                        if (isset($data['colors'][$camelKey])) {
                            $data['colors'][$camelKey] = $value;
                        } elseif (isset($data['colors'][$key])) {
                            $data['colors'][$key] = $value;
                        }
                    }
                }
                
                if (!empty($json['typography'])) {
                    $data['typography'] = array_merge($data['typography'], $json['typography']);
                }
                if (!empty($json['header'])) {
                    $data['header'] = array_merge($data['header'], $json['header']);
                }
                if (!empty($json['buttons'])) {
                    $data['buttons'] = array_merge($data['buttons'], $json['buttons']);
                }
                if (!empty($json['layout'])) {
                    $data['layout'] = array_merge($data['layout'], $json['layout']);
                }
                if (!empty($json['effects'])) {
                    $data['effects'] = array_merge($data['effects'], $json['effects']);
                }
                if (!empty($json['customCSS'])) {
                    $data['customCSS'] = $json['customCSS'];
                }
            }
        }

        // Load custom CSS if exists
        $cssPath = $themePath . '/assets/css/custom.css';
        if (file_exists($cssPath) && empty($data['customCSS'])) {
            $data['customCSS'] = file_get_contents($cssPath);
        }

        return $data;
    }

    private function saveThemeData(string $themePath, array $data): array
    {
        // Load existing theme.json to preserve supports/options
        $jsonPath = $themePath . '/theme.json';
        $existingData = [];
        if (file_exists($jsonPath)) {
            $json = @file_get_contents($jsonPath);
            if ($json) {
                $existingData = json_decode($json, true) ?: [];
            }
        }

        // Prepare new data, preserving existing supports/options
        $jsonData = [
            'name' => $data['info']['name'] ?? $existingData['name'] ?? basename($themePath),
            'description' => $data['info']['description'] ?? $existingData['description'] ?? '',
            'version' => $data['info']['version'] ?? $existingData['version'] ?? '1.0.0',
            'author' => $data['info']['author'] ?? $existingData['author'] ?? '',
            'supports' => $existingData['supports'] ?? [],
            'options' => $existingData['options'] ?? [],
            'colors' => [],
            'typography' => $data['typography'] ?? $existingData['typography'] ?? [],
            'header' => $data['header'] ?? $existingData['header'] ?? [],
            'buttons' => $data['buttons'] ?? $existingData['buttons'] ?? [],
            'layout' => $data['layout'] ?? $existingData['layout'] ?? [],
            'effects' => $data['effects'] ?? $existingData['effects'] ?? [],
        ];

        // Convert camelCase colors to snake_case for storage
        if (!empty($data['colors'])) {
            foreach ($data['colors'] as $key => $value) {
                $snakeKey = strtolower(preg_replace('/([A-Z])/', '_$1', $key));
                $jsonData['colors'][$snakeKey] = $value;
            }
        } else {
            $jsonData['colors'] = $existingData['colors'] ?? [];
        }

        // Save theme.json
        $jsonPath = $themePath . '/theme.json';
        if (!file_put_contents($jsonPath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            return ['success' => false, 'error' => 'Failed to save theme.json'];
        }

        // Generate and save CSS
        $css = $this->generateThemeCSS($data);
        $cssDir = $themePath . '/assets/css';
        if (!is_dir($cssDir)) {
            @mkdir($cssDir, 0755, true);
        }
        
        $cssPath = $cssDir . '/style.css';
        if (!file_put_contents($cssPath, $css)) {
            return ['success' => false, 'error' => 'Failed to save CSS'];
        }

        // Save custom CSS separately
        if (!empty($data['customCSS'])) {
            file_put_contents($cssDir . '/custom.css', $data['customCSS']);
        }

        return ['success' => true, 'message' => 'Theme saved successfully'];
    }

    private function generatePreviewCSS(array $data): string
    {
        return $this->generateCSSVariables($data);
    }

    private function generateThemeCSS(array $data): string
    {
        $css = $this->generateCSSVariables($data);
        $css .= $this->generateBaseStyles($data);
        
        if (!empty($data['customCSS'])) {
            $css .= "\n/* Custom CSS */\n" . $data['customCSS'];
        }
        
        return $css;
    }

    private function generateCSSVariables(array $data): string
    {
        $colors = $data['colors'] ?? [];
        $typography = $data['typography'] ?? [];
        $buttons = $data['buttons'] ?? [];
        $layout = $data['layout'] ?? [];
        $effects = $data['effects'] ?? [];
        $header = $data['header'] ?? [];

        $css = ":root {\n";
        
        // Colors
        $css .= "    /* Colors */\n";
        $css .= "    --color-primary: " . ($colors['primary'] ?? '#8b5cf6') . ";\n";
        $css .= "    --color-secondary: " . ($colors['secondary'] ?? '#6366f1') . ";\n";
        $css .= "    --color-accent: " . ($colors['accent'] ?? '#ec4899') . ";\n";
        $css .= "    --color-background: " . ($colors['background'] ?? '#0f0f12') . ";\n";
        $css .= "    --color-surface: " . ($colors['surface'] ?? '#1a1a21') . ";\n";
        $css .= "    --color-text: " . ($colors['text'] ?? '#ffffff') . ";\n";
        $css .= "    --color-text-muted: " . ($colors['textMuted'] ?? '#a0a0b0') . ";\n";
        $css .= "    --color-border: " . ($colors['border'] ?? '#2d2d3a') . ";\n";
        $css .= "    --color-success: " . ($colors['success'] ?? '#10b981') . ";\n";
        $css .= "    --color-warning: " . ($colors['warning'] ?? '#f59e0b') . ";\n";
        $css .= "    --color-error: " . ($colors['error'] ?? '#ef4444') . ";\n";
        
        // Typography
        $css .= "\n    /* Typography */\n";
        $fontFamily = $typography['fontFamily'] ?? 'Inter';
        $headingFont = $typography['headingFont'] ?? $fontFamily;
        $css .= "    --font-family: '{$fontFamily}', -apple-system, BlinkMacSystemFont, sans-serif;\n";
        $css .= "    --font-heading: '{$headingFont}', -apple-system, BlinkMacSystemFont, sans-serif;\n";
        $css .= "    --font-size-base: " . ($typography['baseFontSize'] ?? '16') . "px;\n";
        $css .= "    --line-height: " . ($typography['lineHeight'] ?? '1.6') . ";\n";
        $css .= "    --font-weight-normal: " . ($typography['fontWeight'] ?? '400') . ";\n";
        $css .= "    --font-weight-heading: " . ($typography['headingWeight'] ?? '700') . ";\n";
        $css .= "    --h1-size: " . ($typography['h1Size'] ?? '3') . "rem;\n";
        $css .= "    --h2-size: " . ($typography['h2Size'] ?? '2.25') . "rem;\n";
        $css .= "    --h3-size: " . ($typography['h3Size'] ?? '1.5') . "rem;\n";
        
        // Layout
        $css .= "\n    /* Layout */\n";
        $css .= "    --container-width: " . ($layout['containerWidth'] ?? '1200') . "px;\n";
        $css .= "    --section-spacing: " . ($layout['sectionSpacing'] ?? '100') . "px;\n";
        $css .= "    --border-radius: " . ($layout['borderRadius'] ?? '12') . "px;\n";
        
        // Buttons
        $css .= "\n    /* Buttons */\n";
        $css .= "    --btn-radius: " . ($buttons['borderRadius'] ?? '8') . "px;\n";
        $css .= "    --btn-padding-x: " . ($buttons['paddingX'] ?? '24') . "px;\n";
        $css .= "    --btn-padding-y: " . ($buttons['paddingY'] ?? '12') . "px;\n";
        $css .= "    --btn-font-weight: " . ($buttons['fontWeight'] ?? '600') . ";\n";
        
        // Header
        $css .= "\n    /* Header */\n";
        $css .= "    --header-height: " . ($header['height'] ?? '72') . "px;\n";
        $css .= "    --header-bg: " . ($header['background'] ?? 'transparent') . ";\n";
        
        // Effects
        $css .= "\n    /* Effects */\n";
        $shadowStrength = ($effects['shadowStrength'] ?? '20') / 100;
        $css .= "    --shadow: 0 4px 20px rgba(0, 0, 0, {$shadowStrength});\n";
        $css .= "    --shadow-lg: 0 10px 40px rgba(0, 0, 0, " . ($shadowStrength * 1.5) . ");\n";
        $css .= "    --hover-scale: " . ($effects['hoverScale'] ?? '1.02') . ";\n";
        $css .= "    --transition-speed: " . ($effects['transitionSpeed'] ?? '200') . "ms;\n";
        
        $css .= "}\n";
        
        return $css;
    }

    private function generateBaseStyles(array $data): string
    {
        $buttons = $data['buttons'] ?? [];
        $header = $data['header'] ?? [];
        
        $css = "\n/* Base Styles */\n";
        $css .= "* { box-sizing: border-box; margin: 0; padding: 0; }\n";
        $css .= "html { scroll-behavior: smooth; }\n";
        $css .= "body {\n";
        $css .= "    font-family: var(--font-family);\n";
        $css .= "    font-size: var(--font-size-base);\n";
        $css .= "    line-height: var(--line-height);\n";
        $css .= "    color: var(--color-text);\n";
        $css .= "    background: var(--color-background);\n";
        $css .= "}\n\n";

        $css .= ".container { max-width: var(--container-width); margin: 0 auto; padding: 0 24px; }\n\n";

        // Headings
        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        $css .= "    font-family: var(--font-heading);\n";
        $css .= "    font-weight: var(--font-weight-heading);\n";
        $css .= "    line-height: 1.2;\n";
        $css .= "}\n";
        $css .= "h1 { font-size: var(--h1-size); }\n";
        $css .= "h2 { font-size: var(--h2-size); }\n";
        $css .= "h3 { font-size: var(--h3-size); }\n\n";

        // Links
        $css .= "a { color: var(--color-primary); text-decoration: none; transition: color var(--transition-speed); }\n";
        $css .= "a:hover { color: var(--color-accent); }\n\n";

        // Buttons
        $css .= ".btn {\n";
        $css .= "    display: inline-flex;\n";
        $css .= "    align-items: center;\n";
        $css .= "    justify-content: center;\n";
        $css .= "    padding: var(--btn-padding-y) var(--btn-padding-x);\n";
        $css .= "    font-family: var(--font-family);\n";
        $css .= "    font-size: 0.95rem;\n";
        $css .= "    font-weight: var(--btn-font-weight);\n";
        $css .= "    border-radius: var(--btn-radius);\n";
        $css .= "    border: none;\n";
        $css .= "    cursor: pointer;\n";
        $css .= "    transition: all var(--transition-speed);\n";
        if (!empty($buttons['uppercase'])) {
            $css .= "    text-transform: uppercase;\n";
            $css .= "    letter-spacing: 0.05em;\n";
        }
        $css .= "}\n";
        $css .= ".btn-primary {\n";
        $css .= "    background: var(--color-primary);\n";
        $css .= "    color: #fff;\n";
        if (!empty($buttons['shadow'])) {
            $css .= "    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);\n";
        }
        $css .= "}\n";
        $css .= ".btn-primary:hover {\n";
        $css .= "    transform: translateY(-2px) scale(var(--hover-scale));\n";
        if (!empty($buttons['shadow'])) {
            $css .= "    box-shadow: 0 6px 25px rgba(139, 92, 246, 0.4);\n";
        }
        $css .= "}\n\n";

        // Header
        $css .= ".site-header {\n";
        $css .= "    position: " . (($header['sticky'] ?? true) ? 'fixed' : 'relative') . ";\n";
        if (($header['sticky'] ?? true)) {
            $css .= "    top: 0; left: 0; right: 0;\n";
            $css .= "    z-index: 1000;\n";
        }
        $css .= "    height: var(--header-height);\n";
        $css .= "    background: var(--header-bg);\n";
        if (($header['blur'] ?? true)) {
            $css .= "    backdrop-filter: blur(10px);\n";
        }
        $css .= "    border-bottom: 1px solid var(--color-border);\n";
        $css .= "}\n\n";

        // Sections
        $css .= "section { padding: var(--section-spacing) 0; }\n\n";

        // Cards
        $css .= ".card {\n";
        $css .= "    background: var(--color-surface);\n";
        $css .= "    border: 1px solid var(--color-border);\n";
        $css .= "    border-radius: var(--border-radius);\n";
        $css .= "    padding: 24px;\n";
        $css .= "    transition: all var(--transition-speed);\n";
        $css .= "}\n";
        $css .= ".card:hover {\n";
        $css .= "    border-color: var(--color-primary);\n";
        $css .= "    box-shadow: var(--shadow);\n";
        $css .= "}\n";

        return $css;
    }

    private function getColorPresets(): array
    {
        return [
            [
                'name' => 'Purple Dream',
                'colors' => [
                    'primary' => '#8b5cf6',
                    'secondary' => '#6366f1',
                    'accent' => '#ec4899',
                    'background' => '#0f0f12',
                    'surface' => '#1a1a21',
                    'text' => '#ffffff',
                    'textMuted' => '#a0a0b0',
                    'border' => '#2d2d3a',
                ]
            ],
            [
                'name' => 'Ocean Blue',
                'colors' => [
                    'primary' => '#0ea5e9',
                    'secondary' => '#06b6d4',
                    'accent' => '#f59e0b',
                    'background' => '#0c1222',
                    'surface' => '#1e293b',
                    'text' => '#f1f5f9',
                    'textMuted' => '#94a3b8',
                    'border' => '#334155',
                ]
            ],
            [
                'name' => 'Forest Green',
                'colors' => [
                    'primary' => '#10b981',
                    'secondary' => '#14b8a6',
                    'accent' => '#f59e0b',
                    'background' => '#0a0f0d',
                    'surface' => '#1a2420',
                    'text' => '#ecfdf5',
                    'textMuted' => '#6ee7b7',
                    'border' => '#064e3b',
                ]
            ],
            [
                'name' => 'Sunset Orange',
                'colors' => [
                    'primary' => '#f97316',
                    'secondary' => '#fb923c',
                    'accent' => '#ec4899',
                    'background' => '#18120a',
                    'surface' => '#292017',
                    'text' => '#fff7ed',
                    'textMuted' => '#fdba74',
                    'border' => '#431407',
                ]
            ],
            [
                'name' => 'Corporate Blue',
                'colors' => [
                    'primary' => '#1e40af',
                    'secondary' => '#3b82f6',
                    'accent' => '#f59e0b',
                    'background' => '#0f172a',
                    'surface' => '#1e293b',
                    'text' => '#f1f5f9',
                    'textMuted' => '#94a3b8',
                    'border' => '#334155',
                ]
            ],
            [
                'name' => 'Light Mode',
                'colors' => [
                    'primary' => '#6366f1',
                    'secondary' => '#8b5cf6',
                    'accent' => '#ec4899',
                    'background' => '#ffffff',
                    'surface' => '#f8fafc',
                    'text' => '#1e293b',
                    'textMuted' => '#64748b',
                    'border' => '#e2e8f0',
                ]
            ],
        ];
    }

    private function getFontList(): array
    {
        return [
            ['name' => 'Inter', 'category' => 'sans-serif'],
            ['name' => 'Poppins', 'category' => 'sans-serif'],
            ['name' => 'Roboto', 'category' => 'sans-serif'],
            ['name' => 'Open Sans', 'category' => 'sans-serif'],
            ['name' => 'Lato', 'category' => 'sans-serif'],
            ['name' => 'Montserrat', 'category' => 'sans-serif'],
            ['name' => 'Nunito', 'category' => 'sans-serif'],
            ['name' => 'Raleway', 'category' => 'sans-serif'],
            ['name' => 'Playfair Display', 'category' => 'serif'],
            ['name' => 'Merriweather', 'category' => 'serif'],
            ['name' => 'Lora', 'category' => 'serif'],
            ['name' => 'Source Code Pro', 'category' => 'monospace'],
            ['name' => 'JetBrains Mono', 'category' => 'monospace'],
        ];
    }
}
