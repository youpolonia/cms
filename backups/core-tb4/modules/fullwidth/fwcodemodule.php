<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Fullwidth Code Module
 *
 * Full-width code display with syntax highlighting, line numbers,
 * and copy functionality. Features multiple themes, language badges,
 * and customizable styling.
 */
class FwCodeModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Code';
        $this->slug = 'fw_code';
        $this->icon = 'terminal';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-code',
            'container' => '.tb4-fw-code-container',
            'header' => '.tb4-fw-code-header',
            'title' => '.tb4-fw-code-title',
            'badge' => '.tb4-fw-code-badge',
            'copy' => '.tb4-fw-code-copy',
            'body' => '.tb4-fw-code-body',
            'lines' => '.tb4-fw-code-lines',
            'content' => '.tb4-fw-code-content',
            'pre' => '.tb4-fw-code-pre'
        ];

        // Content fields
        $this->content_fields = [
            'code_content' => ['type' => 'textarea', 'label' => 'Code Content', 'default' => "// Your code here\nfunction helloWorld() {\n    console.log(\"Hello, World!\");\n    return true;\n}"],
            'language' => ['type' => 'select', 'label' => 'Language', 'options' => ['javascript' => 'JavaScript', 'php' => 'PHP', 'html' => 'HTML', 'css' => 'CSS', 'python' => 'Python', 'sql' => 'SQL', 'bash' => 'Bash/Shell', 'json' => 'JSON', 'xml' => 'XML', 'typescript' => 'TypeScript', 'java' => 'Java', 'csharp' => 'C#', 'cpp' => 'C++', 'ruby' => 'Ruby', 'go' => 'Go', 'rust' => 'Rust', 'swift' => 'Swift', 'kotlin' => 'Kotlin', 'yaml' => 'YAML', 'markdown' => 'Markdown', 'plaintext' => 'Plain Text'], 'default' => 'javascript'],
            'title' => ['type' => 'text', 'label' => 'Title/Filename', 'default' => ''],
            'show_line_numbers' => ['type' => 'select', 'label' => 'Show Line Numbers', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'start_line' => ['type' => 'text', 'label' => 'Start Line Number', 'default' => '1'],
            'highlight_lines' => ['type' => 'text', 'label' => 'Highlight Lines (e.g., 2,4-6)', 'default' => ''],
            'show_copy_button' => ['type' => 'select', 'label' => 'Show Copy Button', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'show_language_badge' => ['type' => 'select', 'label' => 'Show Language Badge', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'default' => 'yes'],
            'wrap_lines' => ['type' => 'select', 'label' => 'Wrap Long Lines', 'options' => ['no' => 'No (Scroll)', 'yes' => 'Yes (Wrap)'], 'default' => 'no'],
            'max_height' => ['type' => 'select', 'label' => 'Max Height', 'options' => ['none' => 'No Limit', '300' => '300px', '400' => '400px', '500' => '500px', '600' => '600px'], 'default' => 'none']
        ];

        // Design fields
        $this->design_fields_custom = [
            'theme' => ['type' => 'select', 'label' => 'Color Theme', 'options' => ['dark' => 'Dark (VS Code)', 'light' => 'Light', 'monokai' => 'Monokai', 'dracula' => 'Dracula', 'github-dark' => 'GitHub Dark', 'github-light' => 'GitHub Light', 'nord' => 'Nord', 'one-dark' => 'One Dark'], 'default' => 'dark'],
            'background_color' => ['type' => 'color', 'label' => 'Background Override', 'default' => ''],
            'text_color' => ['type' => 'color', 'label' => 'Text Color Override', 'default' => ''],
            'font_family' => ['type' => 'select', 'label' => 'Font Family', 'options' => ['monospace' => 'System Monospace', 'fira-code' => 'Fira Code', 'jetbrains-mono' => 'JetBrains Mono', 'source-code-pro' => 'Source Code Pro', 'consolas' => 'Consolas'], 'default' => 'monospace'],
            'font_size' => ['type' => 'text', 'label' => 'Font Size', 'default' => '14px'],
            'line_height' => ['type' => 'text', 'label' => 'Line Height', 'default' => '1.6'],
            'padding' => ['type' => 'text', 'label' => 'Code Padding', 'default' => '24px'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'default' => '12px'],
            'border_width' => ['type' => 'text', 'label' => 'Border Width', 'default' => '0px'],
            'border_color' => ['type' => 'color', 'label' => 'Border Color', 'default' => '#374151'],
            'box_shadow' => ['type' => 'select', 'label' => 'Box Shadow', 'options' => ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra Large'], 'default' => 'lg'],
            'line_number_color' => ['type' => 'color', 'label' => 'Line Number Color', 'default' => '#6b7280'],
            'line_number_bg' => ['type' => 'color', 'label' => 'Line Number Background', 'default' => ''],
            'highlight_bg' => ['type' => 'color', 'label' => 'Highlighted Line Background', 'default' => 'rgba(255,255,0,0.1)'],
            'header_bg' => ['type' => 'color', 'label' => 'Header Background', 'default' => ''],
            'header_border_color' => ['type' => 'color', 'label' => 'Header Border Color', 'default' => 'rgba(255,255,255,0.1)'],
            'copy_button_bg' => ['type' => 'color', 'label' => 'Copy Button Background', 'default' => 'rgba(255,255,255,0.1)'],
            'copy_button_color' => ['type' => 'color', 'label' => 'Copy Button Color', 'default' => '#9ca3af'],
            'badge_bg' => ['type' => 'color', 'label' => 'Language Badge Background', 'default' => 'rgba(255,255,255,0.1)'],
            'badge_color' => ['type' => 'color', 'label' => 'Language Badge Color', 'default' => '#9ca3af'],
            'section_bg' => ['type' => 'color', 'label' => 'Section Background', 'default' => 'transparent'],
            'section_padding' => ['type' => 'text', 'label' => 'Section Padding', 'default' => '0'],
            'content_width' => ['type' => 'select', 'label' => 'Content Width', 'options' => ['full' => 'Full Width', 'contained' => 'Contained (1200px)', 'narrow' => 'Narrow (900px)'], 'default' => 'full']
        ];

        // Advanced fields
        $this->advanced_fields = array_merge($this->advanced_fields, [
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'default' => ''],
            'css_class' => ['type' => 'text', 'label' => 'CSS Class', 'default' => ''],
            'custom_css' => ['type' => 'textarea', 'label' => 'Custom CSS', 'default' => '']
        ]);
    }

    public function get_content_fields(): array
    {
        return $this->content_fields;
    }

    public function get_design_fields(): array
    {
        return array_merge(parent::get_design_fields(), $this->design_fields_custom);
    }

    /**
     * Parse highlight lines specification (e.g., "2,4-6,9")
     */
    private function parseHighlightLines(string $spec): array
    {
        $lines = [];
        if (empty(trim($spec))) {
            return $lines;
        }

        $parts = explode(',', $spec);
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, '-') !== false) {
                $range = explode('-', $part);
                $start = (int)$range[0];
                $end = (int)($range[1] ?? $start);
                for ($i = $start; $i <= $end; $i++) {
                    $lines[] = $i;
                }
            } else {
                $lines[] = (int)$part;
            }
        }

        return array_unique($lines);
    }

    /**
     * Get theme colors
     */
    private function getThemeColors(string $theme): array
    {
        $themes = [
            'dark' => ['bg' => '#1e1e1e', 'text' => '#d4d4d4', 'headerBg' => '#252526'],
            'light' => ['bg' => '#ffffff', 'text' => '#24292e', 'headerBg' => '#f6f8fa'],
            'monokai' => ['bg' => '#272822', 'text' => '#f8f8f2', 'headerBg' => '#1e1f1c'],
            'dracula' => ['bg' => '#282a36', 'text' => '#f8f8f2', 'headerBg' => '#21222c'],
            'github-dark' => ['bg' => '#0d1117', 'text' => '#c9d1d9', 'headerBg' => '#161b22'],
            'github-light' => ['bg' => '#ffffff', 'text' => '#24292f', 'headerBg' => '#f6f8fa'],
            'nord' => ['bg' => '#2e3440', 'text' => '#d8dee9', 'headerBg' => '#3b4252'],
            'one-dark' => ['bg' => '#282c34', 'text' => '#abb2bf', 'headerBg' => '#21252b']
        ];

        return $themes[$theme] ?? $themes['dark'];
    }

    /**
     * Get language display name
     */
    private function getLanguageDisplayName(string $lang): string
    {
        $names = [
            'javascript' => 'JavaScript',
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'python' => 'Python',
            'sql' => 'SQL',
            'bash' => 'Bash',
            'json' => 'JSON',
            'xml' => 'XML',
            'typescript' => 'TypeScript',
            'java' => 'Java',
            'csharp' => 'C#',
            'cpp' => 'C++',
            'ruby' => 'Ruby',
            'go' => 'Go',
            'rust' => 'Rust',
            'swift' => 'Swift',
            'kotlin' => 'Kotlin',
            'yaml' => 'YAML',
            'markdown' => 'Markdown',
            'plaintext' => 'Text'
        ];

        return $names[$lang] ?? $lang;
    }

    /**
     * Get font family CSS value
     */
    private function getFontFamily(string $font): string
    {
        $fonts = [
            'monospace' => 'monospace',
            'fira-code' => "'Fira Code', monospace",
            'jetbrains-mono' => "'JetBrains Mono', monospace",
            'source-code-pro' => "'Source Code Pro', monospace",
            'consolas' => "'Consolas', monospace"
        ];

        return $fonts[$font] ?? 'monospace';
    }

    /**
     * Get box shadow CSS value
     */
    private function getBoxShadow(string $shadow): string
    {
        $shadows = [
            'none' => 'none',
            'sm' => '0 1px 2px rgba(0,0,0,0.1)',
            'md' => '0 4px 6px rgba(0,0,0,0.1)',
            'lg' => '0 10px 25px rgba(0,0,0,0.2)',
            'xl' => '0 20px 40px rgba(0,0,0,0.3)'
        ];

        return $shadows[$shadow] ?? 'none';
    }

    public function render(array $attrs): string
    {
        // Content settings
        $codeContent = $attrs['code_content'] ?? "// Your code here\nfunction helloWorld() {\n    console.log(\"Hello, World!\");\n    return true;\n}";
        $language = $attrs['language'] ?? 'javascript';
        $title = $attrs['title'] ?? '';
        $showLineNumbers = ($attrs['show_line_numbers'] ?? 'yes') === 'yes';
        $startLine = (int)($attrs['start_line'] ?? 1);
        $highlightLines = $attrs['highlight_lines'] ?? '';
        $showCopyButton = ($attrs['show_copy_button'] ?? 'yes') === 'yes';
        $showLanguageBadge = ($attrs['show_language_badge'] ?? 'yes') === 'yes';
        $wrapLines = ($attrs['wrap_lines'] ?? 'no') === 'yes';
        $maxHeight = $attrs['max_height'] ?? 'none';

        // Design settings
        $theme = $attrs['theme'] ?? 'dark';
        $bgOverride = $attrs['background_color'] ?? '';
        $textOverride = $attrs['text_color'] ?? '';
        $fontFamily = $attrs['font_family'] ?? 'monospace';
        $fontSize = $attrs['font_size'] ?? '14px';
        $lineHeight = $attrs['line_height'] ?? '1.6';
        $padding = $attrs['padding'] ?? '24px';
        $borderRadius = $attrs['border_radius'] ?? '12px';
        $borderWidth = $attrs['border_width'] ?? '0px';
        $borderColor = $attrs['border_color'] ?? '#374151';
        $boxShadow = $attrs['box_shadow'] ?? 'lg';
        $lineNumberColor = $attrs['line_number_color'] ?? '#6b7280';
        $lineNumberBg = $attrs['line_number_bg'] ?? '';
        $highlightBg = $attrs['highlight_bg'] ?? 'rgba(255,255,0,0.1)';
        $headerBg = $attrs['header_bg'] ?? '';
        $headerBorderColor = $attrs['header_border_color'] ?? 'rgba(255,255,255,0.1)';
        $copyButtonBg = $attrs['copy_button_bg'] ?? 'rgba(255,255,255,0.1)';
        $copyButtonColor = $attrs['copy_button_color'] ?? '#9ca3af';
        $badgeBg = $attrs['badge_bg'] ?? 'rgba(255,255,255,0.1)';
        $badgeColor = $attrs['badge_color'] ?? '#9ca3af';
        $sectionBg = $attrs['section_bg'] ?? 'transparent';
        $sectionPadding = $attrs['section_padding'] ?? '0';
        $contentWidth = $attrs['content_width'] ?? 'full';

        // Advanced settings
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        // Get theme colors
        $themeColors = $this->getThemeColors($theme);
        $bgColor = $bgOverride ?: $themeColors['bg'];
        $textColor = $textOverride ?: $themeColors['text'];
        $headerBgColor = $headerBg ?: $themeColors['headerBg'];

        // Get computed values
        $fontFamilyValue = $this->getFontFamily($fontFamily);
        $boxShadowValue = $this->getBoxShadow($boxShadow);
        $langDisplay = $this->getLanguageDisplayName($language);

        // Content width
        $maxWidth = '100%';
        if ($contentWidth === 'contained') {
            $maxWidth = '1200px';
        } elseif ($contentWidth === 'narrow') {
            $maxWidth = '900px';
        }

        // Parse highlighted lines
        $highlightedLines = $this->parseHighlightLines($highlightLines);

        // Split code into lines
        $codeLines = explode("\n", $codeContent);

        // Max height style
        $maxHeightStyle = $maxHeight !== 'none' ? 'max-height:' . $maxHeight . 'px;overflow-y:auto;' : '';

        // White space style
        $whiteSpaceStyle = $wrapLines ? 'white-space:pre-wrap;word-break:break-all;' : 'white-space:pre;';

        // Build HTML
        $idAttr = $cssId ? ' id="' . htmlspecialchars($cssId, ENT_QUOTES, 'UTF-8') . '"' : '';
        $classAttr = 'tb4-fw-code' . ($cssClass ? ' ' . htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8') : '');

        $html = '<div' . $idAttr . ' class="' . $classAttr . '">';
        $html .= '<div class="tb4-fw-code-section" style="background:' . htmlspecialchars($sectionBg, ENT_QUOTES, 'UTF-8') . ';padding:' . htmlspecialchars($sectionPadding, ENT_QUOTES, 'UTF-8') . ';">';
        $html .= '<div class="tb4-fw-code-wrapper" style="max-width:' . htmlspecialchars($maxWidth, ENT_QUOTES, 'UTF-8') . ';margin:0 auto;background:' . htmlspecialchars($bgColor, ENT_QUOTES, 'UTF-8') . ';border-radius:' . htmlspecialchars($borderRadius, ENT_QUOTES, 'UTF-8') . ';border:' . htmlspecialchars($borderWidth, ENT_QUOTES, 'UTF-8') . ' solid ' . htmlspecialchars($borderColor, ENT_QUOTES, 'UTF-8') . ';box-shadow:' . $boxShadowValue . ';overflow:hidden;">';

        // Header with macOS-style dots
        $showHeader = $title || $showLanguageBadge || $showCopyButton;
        if ($showHeader) {
            $html .= '<div class="tb4-fw-code-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:' . htmlspecialchars($headerBgColor, ENT_QUOTES, 'UTF-8') . ';border-bottom:1px solid ' . htmlspecialchars($headerBorderColor, ENT_QUOTES, 'UTF-8') . ';">';

            // Left side: dots + title
            $html .= '<div class="tb4-fw-code-title" style="display:flex;align-items:center;gap:12px;font-size:13px;font-weight:500;color:' . htmlspecialchars($lineNumberColor, ENT_QUOTES, 'UTF-8') . ';">';
            $html .= '<div class="tb4-fw-code-dots" style="display:flex;gap:6px;">';
            $html .= '<span style="width:12px;height:12px;border-radius:50%;background:#ff5f56;"></span>';
            $html .= '<span style="width:12px;height:12px;border-radius:50%;background:#ffbd2e;"></span>';
            $html .= '<span style="width:12px;height:12px;border-radius:50%;background:#27c93f;"></span>';
            $html .= '</div>';
            if ($title) {
                $html .= '<span>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>';
            }
            $html .= '</div>';

            // Right side: badge + copy
            $html .= '<div class="tb4-fw-code-actions" style="display:flex;align-items:center;gap:12px;">';
            if ($showLanguageBadge) {
                $html .= '<span class="tb4-fw-code-badge" style="padding:4px 10px;background:' . htmlspecialchars($badgeBg, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($badgeColor, ENT_QUOTES, 'UTF-8') . ';font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px;border-radius:4px;">' . htmlspecialchars($langDisplay, ENT_QUOTES, 'UTF-8') . '</span>';
            }
            if ($showCopyButton) {
                $html .= '<button class="tb4-fw-code-copy" style="padding:6px 12px;background:' . htmlspecialchars($copyButtonBg, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($copyButtonColor, ENT_QUOTES, 'UTF-8') . ';border:none;font-size:12px;font-weight:500;border-radius:4px;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all 0.2s;">';
                $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
                $html .= 'Copy</button>';
            }
            $html .= '</div></div>';
        }

        // Code body
        $html .= '<div class="tb4-fw-code-body" style="display:flex;' . $maxHeightStyle . '">';

        // Line numbers
        if ($showLineNumbers) {
            $lineNumBgStyle = $lineNumberBg ? 'background:' . htmlspecialchars($lineNumberBg, ENT_QUOTES, 'UTF-8') . ';' : '';
            $html .= '<div class="tb4-fw-code-lines" style="padding:' . htmlspecialchars($padding, ENT_QUOTES, 'UTF-8') . ' 0;text-align:right;user-select:none;border-right:1px solid ' . htmlspecialchars($headerBorderColor, ENT_QUOTES, 'UTF-8') . ';flex-shrink:0;' . $lineNumBgStyle . '">';
            foreach ($codeLines as $index => $line) {
                $lineNum = $startLine + $index;
                $isHighlighted = in_array($lineNum, $highlightedLines);
                $highlightStyle = $isHighlighted ? 'background:' . htmlspecialchars($highlightBg, ENT_QUOTES, 'UTF-8') . ';' : '';
                $html .= '<div class="tb4-fw-code-line-num" style="padding:0 16px;font-family:' . $fontFamilyValue . ';font-size:' . htmlspecialchars($fontSize, ENT_QUOTES, 'UTF-8') . ';line-height:' . htmlspecialchars($lineHeight, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($lineNumberColor, ENT_QUOTES, 'UTF-8') . ';' . $highlightStyle . '">' . $lineNum . '</div>';
            }
            $html .= '</div>';
        }

        // Code content
        $html .= '<div class="tb4-fw-code-content" style="flex:1;padding:' . htmlspecialchars($padding, ENT_QUOTES, 'UTF-8') . ';overflow-x:auto;">';
        $html .= '<pre class="tb4-fw-code-pre" style="margin:0;font-family:' . $fontFamilyValue . ';font-size:' . htmlspecialchars($fontSize, ENT_QUOTES, 'UTF-8') . ';line-height:' . htmlspecialchars($lineHeight, ENT_QUOTES, 'UTF-8') . ';color:' . htmlspecialchars($textColor, ENT_QUOTES, 'UTF-8') . ';' . $whiteSpaceStyle . '">';

        foreach ($codeLines as $index => $line) {
            $lineNum = $startLine + $index;
            $isHighlighted = in_array($lineNum, $highlightedLines);
            $highlightStyle = $isHighlighted ? 'background:' . htmlspecialchars($highlightBg, ENT_QUOTES, 'UTF-8') . ';' : '';
            $highlightClass = $isHighlighted ? ' highlighted' : '';
            $escapedLine = htmlspecialchars($line, ENT_QUOTES, 'UTF-8') ?: ' ';
            $html .= '<span class="tb4-fw-code-line' . $highlightClass . '" style="display:block;padding:0 4px;margin:0 -4px;' . $highlightStyle . '">' . $escapedLine . '</span>';
        }

        $html .= '</pre></div></div></div></div></div>';

        // Add hover style for copy button
        $html .= '<style>.tb4-fw-code-copy:hover{background:rgba(255,255,255,0.15)!important;color:#ffffff!important}</style>';

        return $html;
    }
}
