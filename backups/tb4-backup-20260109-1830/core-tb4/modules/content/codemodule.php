<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Code Module
 * Displays code snippets with CSS-based syntax highlighting
 */
class CodeModule extends Module
{
    /**
     * Language keywords for CSS highlighting
     */
    private array $language_keywords = [
        'php' => ['function', 'class', 'public', 'private', 'protected', 'static', 'const', 'return', 'if', 'else', 'elseif', 'foreach', 'for', 'while', 'do', 'switch', 'case', 'break', 'continue', 'try', 'catch', 'throw', 'finally', 'new', 'extends', 'implements', 'interface', 'trait', 'namespace', 'use', 'require', 'require_once', 'include', 'include_once', 'echo', 'print', 'isset', 'empty', 'null', 'true', 'false', 'array', 'int', 'string', 'bool', 'float', 'void', 'mixed'],
        'javascript' => ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'do', 'switch', 'case', 'break', 'continue', 'try', 'catch', 'throw', 'finally', 'new', 'class', 'extends', 'import', 'export', 'default', 'async', 'await', 'yield', 'typeof', 'instanceof', 'null', 'undefined', 'true', 'false', 'this', 'super', 'get', 'set', 'static', 'constructor'],
        'python' => ['def', 'class', 'return', 'if', 'elif', 'else', 'for', 'while', 'break', 'continue', 'try', 'except', 'finally', 'raise', 'import', 'from', 'as', 'with', 'pass', 'lambda', 'yield', 'global', 'nonlocal', 'True', 'False', 'None', 'and', 'or', 'not', 'in', 'is', 'async', 'await', 'self'],
        'sql' => ['SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'SET', 'DELETE', 'CREATE', 'TABLE', 'DROP', 'ALTER', 'INDEX', 'JOIN', 'LEFT', 'RIGHT', 'INNER', 'OUTER', 'ON', 'AS', 'ORDER', 'BY', 'GROUP', 'HAVING', 'LIMIT', 'OFFSET', 'UNION', 'NULL', 'NOT', 'IN', 'LIKE', 'BETWEEN', 'EXISTS', 'DISTINCT', 'PRIMARY', 'KEY', 'FOREIGN', 'REFERENCES', 'CASCADE', 'DEFAULT', 'AUTO_INCREMENT', 'INT', 'VARCHAR', 'TEXT', 'BOOLEAN', 'TIMESTAMP', 'DATE'],
        'css' => ['@import', '@media', '@keyframes', '@font-face', '@supports', '@charset', '!important', 'inherit', 'initial', 'unset', 'none', 'auto', 'block', 'inline', 'flex', 'grid', 'hidden', 'visible', 'absolute', 'relative', 'fixed', 'sticky', 'static'],
        'html' => ['DOCTYPE', 'html', 'head', 'body', 'title', 'meta', 'link', 'script', 'style', 'div', 'span', 'p', 'a', 'img', 'ul', 'ol', 'li', 'table', 'tr', 'td', 'th', 'form', 'input', 'button', 'select', 'option', 'textarea', 'label', 'header', 'footer', 'nav', 'main', 'section', 'article', 'aside', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        'bash' => ['if', 'then', 'else', 'elif', 'fi', 'for', 'do', 'done', 'while', 'until', 'case', 'esac', 'function', 'return', 'exit', 'break', 'continue', 'echo', 'printf', 'read', 'export', 'local', 'source', 'true', 'false', 'cd', 'ls', 'pwd', 'mkdir', 'rm', 'cp', 'mv', 'cat', 'grep', 'sed', 'awk', 'chmod', 'chown', 'sudo'],
        'json' => ['true', 'false', 'null'],
        'xml' => [],
        'markdown' => [],
        'plain' => []
    ];

    public function __construct()
    {
        $this->name = 'Code';
        $this->slug = "code";
        $this->icon = 'Code';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-code',
            'header' => '.tb4-code__header',
            'content' => '.tb4-code__content',
            'line_numbers' => '.tb4-code__line-numbers',
            'copy_button' => '.tb4-code__copy-btn',
            'language_badge' => '.tb4-code__language'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'code' => [
                'label' => 'Code',
                'type' => 'textarea',
                'default' => '// Your code here',
                'rows' => 12
            ],
            'language' => [
                'label' => 'Language',
                'type' => 'select',
                'options' => [
                    'plain' => 'Plain Text',
                    'html' => 'HTML',
                    'css' => 'CSS',
                    'javascript' => 'JavaScript',
                    'php' => 'PHP',
                    'python' => 'Python',
                    'sql' => 'SQL',
                    'bash' => 'Bash / Shell',
                    'json' => 'JSON',
                    'xml' => 'XML',
                    'markdown' => 'Markdown'
                ],
                'default' => 'javascript'
            ],
            'filename' => [
                'label' => 'Filename (optional)',
                'type' => 'text',
                'default' => '',
                'description' => 'Display as header, e.g., "index.php"'
            ],
            'description' => [
                'label' => 'Description (optional)',
                'type' => 'text',
                'default' => '',
                'description' => 'Caption shown below the code block'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'theme' => [
                'label' => 'Theme',
                'type' => 'select',
                'options' => [
                    'dark' => 'Dark',
                    'light' => 'Light',
                    'monokai' => 'Monokai',
                    'github' => 'GitHub',
                    'dracula' => 'Dracula'
                ],
                'default' => 'dark'
            ],
            'show_line_numbers' => [
                'label' => 'Show Line Numbers',
                'type' => 'toggle',
                'default' => true
            ],
            'show_copy_button' => [
                'label' => 'Show Copy Button',
                'type' => 'toggle',
                'default' => true
            ],
            'show_language_badge' => [
                'label' => 'Show Language Badge',
                'type' => 'toggle',
                'default' => true
            ],
            'max_height' => [
                'label' => 'Max Height',
                'type' => 'select',
                'options' => [
                    'auto' => 'Auto (no limit)',
                    '200px' => '200px',
                    '300px' => '300px',
                    '400px' => '400px',
                    '500px' => '500px',
                    '600px' => '600px'
                ],
                'default' => 'auto'
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'select',
                'options' => [
                    '12px' => '12px',
                    '13px' => '13px',
                    '14px' => '14px',
                    '15px' => '15px',
                    '16px' => '16px'
                ],
                'default' => '14px'
            ],
            'font_family' => [
                'label' => 'Font Family',
                'type' => 'select',
                'options' => [
                    'monospace' => 'System Monospace',
                    "'Fira Code', monospace" => 'Fira Code',
                    "'Source Code Pro', monospace" => 'Source Code Pro',
                    "'JetBrains Mono', monospace" => 'JetBrains Mono',
                    "'Consolas', monospace" => 'Consolas',
                    "'Monaco', monospace" => 'Monaco'
                ],
                'default' => 'monospace'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'code_padding' => [
                'label' => 'Padding',
                'type' => 'text',
                'default' => '16px'
            ],
            'highlight_lines' => [
                'label' => 'Highlight Lines',
                'type' => 'text',
                'default' => '',
                'description' => 'e.g., "1,3,5-7" to highlight specific lines'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Parse highlight lines string to array of line numbers
     */
    private function parse_highlight_lines(string $input): array
    {
        $lines = [];
        $parts = explode(',', $input);

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            if (strpos($part, '-') !== false) {
                [$start, $end] = explode('-', $part, 2);
                $start = (int) trim($start);
                $end = (int) trim($end);
                if ($start > 0 && $end >= $start) {
                    for ($i = $start; $i <= $end; $i++) {
                        $lines[] = $i;
                    }
                }
            } else {
                $num = (int) $part;
                if ($num > 0) {
                    $lines[] = $num;
                }
            }
        }

        return array_unique($lines);
    }

    /**
     * Get theme colors
     */
    private function get_theme_colors(string $theme): array
    {
        $themes = [
            'dark' => [
                'bg' => '#1e1e1e',
                'text' => '#d4d4d4',
                'header_bg' => '#2d2d2d',
                'header_text' => '#cccccc',
                'line_numbers' => '#858585',
                'line_numbers_bg' => '#1e1e1e',
                'highlight_bg' => 'rgba(255,255,255,0.1)',
                'keyword' => '#569cd6',
                'string' => '#ce9178',
                'comment' => '#6a9955',
                'number' => '#b5cea8',
                'function' => '#dcdcaa',
                'variable' => '#9cdcfe',
                'tag' => '#569cd6',
                'attribute' => '#9cdcfe',
                'punctuation' => '#d4d4d4'
            ],
            'light' => [
                'bg' => '#ffffff',
                'text' => '#24292e',
                'header_bg' => '#f6f8fa',
                'header_text' => '#24292e',
                'line_numbers' => '#959da5',
                'line_numbers_bg' => '#f6f8fa',
                'highlight_bg' => 'rgba(255,220,0,0.2)',
                'keyword' => '#d73a49',
                'string' => '#032f62',
                'comment' => '#6a737d',
                'number' => '#005cc5',
                'function' => '#6f42c1',
                'variable' => '#e36209',
                'tag' => '#22863a',
                'attribute' => '#6f42c1',
                'punctuation' => '#24292e'
            ],
            'monokai' => [
                'bg' => '#272822',
                'text' => '#f8f8f2',
                'header_bg' => '#1e1f1c',
                'header_text' => '#f8f8f2',
                'line_numbers' => '#90908a',
                'line_numbers_bg' => '#272822',
                'highlight_bg' => 'rgba(255,255,255,0.1)',
                'keyword' => '#f92672',
                'string' => '#e6db74',
                'comment' => '#75715e',
                'number' => '#ae81ff',
                'function' => '#a6e22e',
                'variable' => '#fd971f',
                'tag' => '#f92672',
                'attribute' => '#a6e22e',
                'punctuation' => '#f8f8f2'
            ],
            'github' => [
                'bg' => '#f6f8fa',
                'text' => '#24292e',
                'header_bg' => '#e1e4e8',
                'header_text' => '#24292e',
                'line_numbers' => '#959da5',
                'line_numbers_bg' => '#fafbfc',
                'highlight_bg' => '#fffbdd',
                'keyword' => '#d73a49',
                'string' => '#032f62',
                'comment' => '#6a737d',
                'number' => '#005cc5',
                'function' => '#6f42c1',
                'variable' => '#e36209',
                'tag' => '#22863a',
                'attribute' => '#6f42c1',
                'punctuation' => '#24292e'
            ],
            'dracula' => [
                'bg' => '#282a36',
                'text' => '#f8f8f2',
                'header_bg' => '#21222c',
                'header_text' => '#f8f8f2',
                'line_numbers' => '#6272a4',
                'line_numbers_bg' => '#282a36',
                'highlight_bg' => 'rgba(68,71,90,0.5)',
                'keyword' => '#ff79c6',
                'string' => '#f1fa8c',
                'comment' => '#6272a4',
                'number' => '#bd93f9',
                'function' => '#50fa7b',
                'variable' => '#ffb86c',
                'tag' => '#ff79c6',
                'attribute' => '#50fa7b',
                'punctuation' => '#f8f8f2'
            ]
        ];

        return $themes[$theme] ?? $themes['dark'];
    }

    /**
     * Apply CSS-based syntax highlighting (simple regex)
     */
    private function highlight_code(string $code, string $language): string
    {
        // First escape HTML
        $escaped = esc_html($code);

        if ($language === 'plain') {
            return $escaped;
        }

        // Comments (must come first to prevent keyword matching inside comments)
        // Single line comments //
        $escaped = preg_replace(
            '/(?<!:)(\/\/[^\n]*)/m',
            '<span class="tb4-code-comment">$1</span>',
            $escaped
        );

        // Hash comments #
        if (in_array($language, ['python', 'bash', 'php'])) {
            $escaped = preg_replace(
                '/(#[^\n]*)/m',
                '<span class="tb4-code-comment">$1</span>',
                $escaped
            );
        }

        // Multi-line comments /* */
        $escaped = preg_replace(
            '/(\/\*[\s\S]*?\*\/)/m',
            '<span class="tb4-code-comment">$1</span>',
            $escaped
        );

        // HTML comments
        if (in_array($language, ['html', 'xml'])) {
            $escaped = preg_replace(
                '/(&lt;!--[\s\S]*?--&gt;)/m',
                '<span class="tb4-code-comment">$1</span>',
                $escaped
            );
        }

        // SQL comments --
        if ($language === 'sql') {
            $escaped = preg_replace(
                '/(--[^\n]*)/m',
                '<span class="tb4-code-comment">$1</span>',
                $escaped
            );
        }

        // Strings (double and single quotes)
        $escaped = preg_replace(
            '/(&quot;(?:[^&]|&(?!quot;))*?&quot;|&#039;(?:[^&]|&(?!#039;))*?&#039;|"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\')/s',
            '<span class="tb4-code-string">$1</span>',
            $escaped
        );

        // Template literals for JS
        if ($language === 'javascript') {
            $escaped = preg_replace(
                '/(`[^`]*`)/s',
                '<span class="tb4-code-string">$1</span>',
                $escaped
            );
        }

        // Numbers
        $escaped = preg_replace(
            '/\b(\d+\.?\d*)\b/',
            '<span class="tb4-code-number">$1</span>',
            $escaped
        );

        // HTML/XML tags
        if (in_array($language, ['html', 'xml'])) {
            // Tags
            $escaped = preg_replace(
                '/(&lt;\/?)([\w-]+)/i',
                '$1<span class="tb4-code-tag">$2</span>',
                $escaped
            );
            // Attributes
            $escaped = preg_replace(
                '/\s([\w-]+)(=)/i',
                ' <span class="tb4-code-attr">$1</span>$2',
                $escaped
            );
        }

        // CSS selectors and properties
        if ($language === 'css') {
            // Properties
            $escaped = preg_replace(
                '/([\w-]+)(\s*:)/m',
                '<span class="tb4-code-attr">$1</span>$2',
                $escaped
            );
        }

        // PHP variables
        if ($language === 'php') {
            $escaped = preg_replace(
                '/(\$[\w_]+)/',
                '<span class="tb4-code-variable">$1</span>',
                $escaped
            );
        }

        // Bash variables
        if ($language === 'bash') {
            $escaped = preg_replace(
                '/(\$[\w_]+|\$\{[^}]+\})/',
                '<span class="tb4-code-variable">$1</span>',
                $escaped
            );
        }

        // Python decorators
        if ($language === 'python') {
            $escaped = preg_replace(
                '/(@[\w_]+)/',
                '<span class="tb4-code-function">$1</span>',
                $escaped
            );
        }

        // JSON keys
        if ($language === 'json') {
            $escaped = preg_replace(
                '/(<span class="tb4-code-string">[^<]+<\/span>)\s*:/',
                '<span class="tb4-code-attr">$1</span>:',
                $escaped
            );
        }

        // Keywords (language specific)
        $keywords = $this->language_keywords[$language] ?? [];
        if (!empty($keywords)) {
            foreach ($keywords as $keyword) {
                $pattern = '/\b(' . preg_quote($keyword, '/') . ')\b/';
                if ($language === 'sql') {
                    // SQL is case-insensitive
                    $pattern = '/\b(' . preg_quote($keyword, '/') . ')\b/i';
                }
                $escaped = preg_replace(
                    $pattern,
                    '<span class="tb4-code-keyword">$1</span>',
                    $escaped
                );
            }
        }

        // Function calls
        $escaped = preg_replace(
            '/\b([\w_]+)(\s*\()/i',
            '<span class="tb4-code-function">$1</span>$2',
            $escaped
        );

        return $escaped;
    }

    public function render(array $settings): string
    {
        $code = $settings['code'] ?? '// Your code here';
        $language = $settings['language'] ?? 'javascript';
        $filename = $settings['filename'] ?? '';
        $description = $settings['description'] ?? '';
        $theme = $settings['theme'] ?? 'dark';
        $showLineNumbers = $settings['show_line_numbers'] ?? true;
        $showCopyButton = $settings['show_copy_button'] ?? true;
        $showLanguageBadge = $settings['show_language_badge'] ?? true;
        $maxHeight = $settings['max_height'] ?? 'auto';
        $fontSize = $settings['font_size'] ?? '14px';
        $fontFamily = $settings['font_family'] ?? 'monospace';
        $borderRadius = $settings['border_radius'] ?? '8px';
        $codePadding = $settings['code_padding'] ?? '16px';
        $highlightLines = $settings['highlight_lines'] ?? '';

        $colors = $this->get_theme_colors($theme);
        $highlightedLines = $this->parse_highlight_lines($highlightLines);
        $uniqueId = 'tb4-code-' . uniqid();

        // Generate inline styles
        $wrapperStyles = [
            'background-color:' . esc_attr($colors['bg']),
            'border-radius:' . esc_attr($borderRadius),
            'overflow:hidden',
            'font-family:' . esc_attr($fontFamily),
            'font-size:' . esc_attr($fontSize),
            'line-height:1.5'
        ];

        // Build HTML
        $html = '<div class="tb4-code tb4-code--' . esc_attr($theme) . '" id="' . esc_attr($uniqueId) . '" style="' . implode(';', $wrapperStyles) . '">';

        // Header (if filename or language badge)
        if ($filename || $showLanguageBadge) {
            $headerStyles = [
                'display:flex',
                'justify-content:space-between',
                'align-items:center',
                'padding:8px 16px',
                'background-color:' . esc_attr($colors['header_bg']),
                'color:' . esc_attr($colors['header_text']),
                'font-size:12px',
                'border-bottom:1px solid rgba(128,128,128,0.2)'
            ];
            $html .= '<div class="tb4-code__header" style="' . implode(';', $headerStyles) . '">';

            if ($filename) {
                $html .= '<span class="tb4-code__filename" style="font-weight:600">' . esc_html($filename) . '</span>';
            } else {
                $html .= '<span></span>';
            }

            $rightItems = [];
            if ($showLanguageBadge) {
                $langLabels = [
                    'html' => 'HTML',
                    'css' => 'CSS',
                    'javascript' => 'JavaScript',
                    'php' => 'PHP',
                    'python' => 'Python',
                    'sql' => 'SQL',
                    'bash' => 'Bash',
                    'json' => 'JSON',
                    'xml' => 'XML',
                    'markdown' => 'Markdown',
                    'plain' => 'Text'
                ];
                $langLabel = $langLabels[$language] ?? ucfirst($language);
                $rightItems[] = '<span class="tb4-code__language" style="opacity:0.7;text-transform:uppercase;font-size:11px;letter-spacing:0.5px">' . esc_html($langLabel) . '</span>';
            }

            if ($showCopyButton) {
                $btnStyles = 'background:transparent;border:none;color:' . esc_attr($colors['header_text']) . ';cursor:pointer;padding:4px 8px;border-radius:4px;font-size:12px;display:flex;align-items:center;gap:4px;transition:background 0.2s';
                $rightItems[] = '<button type="button" class="tb4-code__copy-btn" data-code-id="' . esc_attr($uniqueId) . '" style="' . $btnStyles . '" onclick="tb4CopyCode(this)" title="Copy code">'
                    . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>'
                    . '<span>Copy</span></button>';
            }

            if (!empty($rightItems)) {
                $html .= '<div style="display:flex;align-items:center;gap:12px">' . implode('', $rightItems) . '</div>';
            }

            $html .= '</div>';
        }

        // Code content area
        $contentStyles = [
            'display:flex',
            'overflow:auto'
        ];
        if ($maxHeight !== 'auto') {
            $contentStyles[] = 'max-height:' . esc_attr($maxHeight);
        }
        $html .= '<div class="tb4-code__content" style="' . implode(';', $contentStyles) . '">';

        // Split code into lines
        $lines = explode("\n", $code);
        $lineCount = count($lines);

        // Line numbers column
        if ($showLineNumbers) {
            $lineNumStyles = [
                'padding:' . esc_attr($codePadding),
                'padding-right:12px',
                'text-align:right',
                'user-select:none',
                'color:' . esc_attr($colors['line_numbers']),
                'background-color:' . esc_attr($colors['line_numbers_bg']),
                'border-right:1px solid rgba(128,128,128,0.2)',
                'flex-shrink:0'
            ];
            $html .= '<div class="tb4-code__line-numbers" aria-hidden="true" style="' . implode(';', $lineNumStyles) . '">';
            for ($i = 1; $i <= $lineCount; $i++) {
                $isHighlighted = in_array($i, $highlightedLines);
                $lineStyle = $isHighlighted ? 'background-color:' . esc_attr($colors['highlight_bg']) . ';margin:0 -12px 0 -' . esc_attr($codePadding) . ';padding:0 12px 0 ' . esc_attr($codePadding) : '';
                $html .= '<div class="tb4-code__line-num" style="' . $lineStyle . '">' . $i . '</div>';
            }
            $html .= '</div>';
        }

        // Code block
        $codeBlockStyles = [
            'flex:1',
            'padding:' . esc_attr($codePadding),
            'margin:0',
            'overflow-x:auto',
            'color:' . esc_attr($colors['text']),
            'white-space:pre',
            'tab-size:4'
        ];
        $html .= '<pre class="tb4-code__pre" style="' . implode(';', $codeBlockStyles) . '"><code class="tb4-code__code" data-language="' . esc_attr($language) . '">';

        // Render each line
        foreach ($lines as $index => $line) {
            $lineNum = $index + 1;
            $isHighlighted = in_array($lineNum, $highlightedLines);
            $lineStyle = '';
            if ($isHighlighted) {
                $lineStyle = ' style="display:block;background-color:' . esc_attr($colors['highlight_bg']) . ';margin:0 -' . esc_attr($codePadding) . ';padding:0 ' . esc_attr($codePadding) . '"';
            }

            $highlightedCode = $this->highlight_code($line, $language);
            $html .= '<span class="tb4-code__line"' . $lineStyle . '>' . $highlightedCode . '</span>' . "\n";
        }

        $html .= '</code></pre>';
        $html .= '</div>'; // End content

        // Description
        if ($description) {
            $descStyles = [
                'padding:12px 16px',
                'font-size:13px',
                'color:' . esc_attr($colors['header_text']),
                'background-color:' . esc_attr($colors['header_bg']),
                'border-top:1px solid rgba(128,128,128,0.2)'
            ];
            $html .= '<div class="tb4-code__description" style="' . implode(';', $descStyles) . '">' . esc_html($description) . '</div>';
        }

        $html .= '</div>'; // End wrapper

        // Add scoped CSS for syntax highlighting colors
        $html .= $this->generate_scoped_css($uniqueId, $colors);

        // Add copy-to-clipboard JavaScript (only once per page)
        $html .= $this->generate_copy_script();

        return $html;
    }

    /**
     * Generate copy-to-clipboard JavaScript (only once)
     */
    private static bool $scriptIncluded = false;

    private function generate_copy_script(): string
    {
        if (self::$scriptIncluded) {
            return '';
        }
        self::$scriptIncluded = true;

        return '<script>
function tb4CopyCode(btn) {
    var codeId = btn.getAttribute("data-code-id");
    var codeBlock = document.getElementById(codeId);
    if (!codeBlock) return;

    var codeElement = codeBlock.querySelector(".tb4-code__code");
    if (!codeElement) return;

    var text = codeElement.textContent || codeElement.innerText;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            tb4ShowCopied(btn);
        }).catch(function() {
            tb4FallbackCopy(text, btn);
        });
    } else {
        tb4FallbackCopy(text, btn);
    }
}

function tb4FallbackCopy(text, btn) {
    var textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.left = "-9999px";
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand("copy");
        tb4ShowCopied(btn);
    } catch (err) {
        console.error("Copy failed:", err);
    }
    document.body.removeChild(textarea);
}

function tb4ShowCopied(btn) {
    var span = btn.querySelector("span");
    var originalText = span ? span.textContent : "Copy";
    btn.classList.add("copied");
    if (span) span.textContent = "Copied!";

    setTimeout(function() {
        btn.classList.remove("copied");
        if (span) span.textContent = originalText;
    }, 2000);
}
</script>';
    }

    /**
     * Generate scoped CSS for syntax highlighting
     */
    private function generate_scoped_css(string $uniqueId, array $colors): string
    {
        return '<style>
#' . esc_attr($uniqueId) . ' .tb4-code-keyword { color: ' . esc_attr($colors['keyword']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code-string { color: ' . esc_attr($colors['string']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code-comment { color: ' . esc_attr($colors['comment']) . '; font-style: italic; }
#' . esc_attr($uniqueId) . ' .tb4-code-number { color: ' . esc_attr($colors['number']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code-function { color: ' . esc_attr($colors['function']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code-variable { color: ' . esc_attr($colors['variable']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code-tag { color: ' . esc_attr($colors['tag']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code-attr { color: ' . esc_attr($colors['attribute']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code-punctuation { color: ' . esc_attr($colors['punctuation']) . '; }
#' . esc_attr($uniqueId) . ' .tb4-code__copy-btn:hover { background-color: rgba(128,128,128,0.2) !important; }
#' . esc_attr($uniqueId) . ' .tb4-code__copy-btn.copied { color: #22c55e !important; }
#' . esc_attr($uniqueId) . ' .tb4-code__content::-webkit-scrollbar { width: 8px; height: 8px; }
#' . esc_attr($uniqueId) . ' .tb4-code__content::-webkit-scrollbar-track { background: transparent; }
#' . esc_attr($uniqueId) . ' .tb4-code__content::-webkit-scrollbar-thumb { background: rgba(128,128,128,0.4); border-radius: 4px; }
#' . esc_attr($uniqueId) . ' .tb4-code__content::-webkit-scrollbar-thumb:hover { background: rgba(128,128,128,0.6); }
</style>';
    }
}
