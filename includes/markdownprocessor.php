<?php
namespace Includes;

/**
 * MarkdownProcessor
 * 
 * A simple Markdown processor for converting Markdown to HTML
 */
class MarkdownProcessor {
    protected $options = [];
    
    /**
     * Constructor
     * 
     * @param array $options Configuration options
     */
    public function __construct(array $options = []) {
        // Default options
        $defaultOptions = [
            'auto_links' => true,
            'auto_paragraphs' => true,
            'html_allowed' => false,
            'safe_mode' => true
        ];
        
        $this->options = array_merge($defaultOptions, $options);
    }
    
    /**
     * Process Markdown text to HTML
     * 
     * @param string $text The Markdown text to process
     * @return string The processed HTML
     */
    public function process($text) {
        if (empty($text)) {
            return '';
        }
        
        // Sanitize input if safe mode is enabled
        if ($this->options['safe_mode'] && !$this->options['html_allowed']) {
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
        
        // Process Markdown elements
        $html = $this->processHeaders($text);
        $html = $this->processBoldItalic($html);
        $html = $this->processLists($html);
        $html = $this->processCodeBlocks($html);
        $html = $this->processInlineCode($html);
        $html = $this->processHorizontalRules($html);
        $html = $this->processBlockquotes($html);
        
        // Process links and images
        $html = $this->processLinks($html);
        $html = $this->processImages($html);
        
        // Auto-convert URLs to links if enabled
        if ($this->options['auto_links']) {
            $html = $this->processAutoLinks($html);
        }
        
        // Add paragraphs if enabled
        if ($this->options['auto_paragraphs']) {
            $html = $this->processParagraphs($html);
        }
        
        return $html;
    }
    
    /**
     * Process headers (# Header)
     */
    protected function processHeaders($text) {
        return preg_replace_callback('/^(#{1,6})\s+(.+?)(?:\s+\1)?$/m', function($matches) {
            $level = strlen($matches[1]);
            $content = trim($matches[2]);
            return "<h{$level}>{$content}</h{$level}>";
        }, $text);
    }
    
    /**
     * Process bold and italic text
     */
    protected function processBoldItalic($text) {
        // Bold: **text** or __text__
        $text = preg_replace('/\*\*(.*?)\*\*|__(.*?)__/s', '<strong>$1$2</strong>', $text);
        
        // Italic: *text* or _text_
        $text = preg_replace('/\*(.*?)\*|_(.*?)_/s', '<em>$1$2</em>', $text);
        
        return $text;
    }
    
    /**
     * Process unordered and ordered lists
     */
    protected function processLists($text) {
        // Unordered lists
        $text = preg_replace_callback('/(?:^|\n)((?:[ ]{0,3}[*+-][ \t]+.+(?:\n|$))+)/s', function($matches) {
            $list = preg_replace('/^[ ]{0,3}[*+-][ \t]+(.+)(?:\n|$)/m', '
<li>
$1</li>', $matches[1]);
            return "
<ul>{$list}</ul>";
        },
 $text);
        
        // Ordered lists
        $text = preg_replace_callback('/(?:^|\n)((?:[ ]{0,3}\d+\.[ \t]+.+(?:\n|$))+)/s', function($matches) {
            $list = preg_replace('/^[ ]{0,3}\d+\.[ \t]+(.+)(?:\n|$)/m', '
<li>
$1</li>', $matches[1]);
            return "
<ol>{$list}</ol>";
        },
 $text);
        
        return $text;
    }
    
    /**
     * Process code blocks
     */
    protected function processCodeBlocks($text) {
        return preg_replace_callback('/(?:^|\n)```(?:([a-zA-Z]+)\n)?(.*?)```(?:\n|$)/s', function($matches) {
            $language = !empty($matches[1]) ? " class=\"language-{$matches[1]}\"" : '';
            $code = htmlspecialchars(trim($matches[2]), ENT_QUOTES, 'UTF-8');
            return "
<pre><code{
$language}>{$code}</code></pre>";
        },
 $text);
    }
    
    /**
     * Process inline code
     */
    protected function processInlineCode($text) {
        return preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
    }
    
    /**
     * Process horizontal rules
     */
    protected function processHorizontalRules($text) {
        return preg_replace('/^(?:[ ]{0,3}(?:[-*_][ \t]*){3,}[ \t]*)$/m', '
<hr />',
 $text);
    }
    
    /**
     * Process blockquotes
     */
    protected
 function processBlockquotes($text) {
        return preg_replace_callback('/(?:^|\n)(?:>[ \t]?.+(?:\n|$))+/s', function($matches) {
            $content = preg_replace('/^>[ \t]?(.+)(?:\n|$)/m', '$1', $matches[0]);
            return "<blockquote>{$content}</blockquote>";
        }, $text);
    }
    
    /**
     * Process links [text](url "title")
     */
    protected function processLinks($text) {
        return preg_replace_callback('/\[([^\]]+)\]\(([^)]+)(?:[ \t]+"([^"]+)")?\)/', function($matches) {
            $text = $matches[1];
            $url = $this->sanitizeUrl($matches[2]);
            $title = isset($matches[3]) ? " title=\"{$matches[3]}\"" : '';
            return "
<a href=\"{
$url}\"{$title}>{$text}</a>";
        },
 $text);
    }
    
    /**
     * Process images ![alt](url "title")
     */
    protected function processImages($text) {
        return preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)(?:[ \t]+"([^"]+)")?\)/', function($matches) {
            $alt = $matches[1];
            $url = $this->sanitizeUrl($matches[2]);
            $title = isset($matches[3]) ? " title=\"{$matches[3]}\"" : '';
            return "
<img src=\"{
$url}\" alt=\"{$alt}\"{$title} />";
        },
 $text);
    }
    
    /**
     * Process auto links
     */
    protected function processAutoLinks($text) {
        $pattern = '~(?<![\w/])(https?://[\w\-]+(?:\.[\w\-]+)+(?:[\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?)~i';
        return preg_replace($pattern, '
<a href="
$1">$1</a>', $text);
    }
    
    /**
     * Process paragraphs
     */
    protected
 function processParagraphs($text) {
        // Split text into blocks
        $blocks = preg_split('/\n{2,}/', $text);
        
        foreach ($blocks as &$block) {
            // Skip blocks that are already HTML elements
            if (preg_match('/^<(?:p|div|h[1-6]|blockquote|pre|table|ul|ol|li)[\s>]/i', trim($block))) {
                continue;
            }
            
            // Wrap block in paragraph tags
            $block = '
<p>' . trim(
$block) . '</p>';
        }
        
        return implode("\n\n", $blocks);
    }
    
    /**
     * Sanitize a URL
     */
    protected function sanitizeUrl($url) {
        // Only allow http, https, mailto, and relative URLs
        if (preg_match('/^(?:https?:\/\/|mailto:|\/|\.\/|\.\.\/)/i', $url)) {
            return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        }
        
        return '#';
    }
}
