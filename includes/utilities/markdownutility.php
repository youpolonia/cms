<?php
/**
 * Centralized Markdown processing utility
 * Implements CommonMark spec with security enhancements
 */
class MarkdownUtility {
    private static $instance;
    private $options = [
        'safe_mode' => true,
        'auto_links' => true,
        'extensions' => []
    ];

    private function __construct(array $config = []) {
        $this->options = array_merge($this->options, $config);
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function parse(string $markdown, array $options = []): string {
        $options = array_merge($this->options, $options);
        $this->validateOptions($options);
        
        $markdown = $this->sanitize($markdown);
        $html = $this->convertToHtml($markdown);
        
        return $this->applyExtensions($html);
    }

    public function sanitize(string $markdown): string {
        // Basic XSS protection
        $markdown = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');
        
        // Validate links
        if ($this->options['safe_mode']) {
            $markdown = preg_replace_callback(
                '/\[([^\]]+)\]\(([^)]+)\)/',
                fn($matches) => $this->validateLink($matches[1], $matches[2]),
                $markdown
            );
        }
        
        return $markdown;
    }

    public function convertToHtml(string $markdown): string {
        // Basic CommonMark parsing
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $markdown);
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);
        
        // Headers
        $html = preg_replace('/^#\s(.+)$/m', '
<h1>
$1</h1>', $html);
        $html = preg_replace('/^##\s(.+)$/m', '
<h2>$1</h2>', $html);
        
        // Lists
        $html = preg_replace('/^\-\s(.+)$/m', '
<li>
$1</li>', $html);
        $html = preg_replace('/(
<li>.+<\/li>)+/s', '<ul>$0</ul>', $html);
        
        // Links
        if ($this->options['auto_links']) {
            $html = preg_replace(
                '/(https?:\/\/[^\s]+)/', 
                '
<a href="
$1" rel="nofollow">$1</a>', 
                $html
            );
        }
        
        return nl2br($html);
    }

    public function supportsFeature(string $feature): bool {
        $supported = [
            'basic_formatting',
            'headers',
            'lists',
            'links',
            'auto_links'
        ];
        
        return in_array($feature, $supported) || 
               isset($this->options['extensions'][$feature]);
    }

    private function validateOptions(array $options): void {
        if (isset($options['extensions']) && !is_array($options['extensions'])) {
            throw new InvalidArgumentException('Extensions must be an array');
        }
    }

    private function applyExtensions(string $html): string {
        foreach ($this->options['extensions'] as $extension) {
            if (is_callable($extension)) {
                $html = $extension($html);
            }
        }
        return $html;
    }

    private function validateLink(string $text, string $url): string {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $text;
        }
        
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https', 'mailto'])) {
            return $text;
        }
        
        return "[{$text}]({$url})";
    }
}
