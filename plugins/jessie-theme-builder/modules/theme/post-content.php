<?php
/**
 * Post Content Module
 * Displays the dynamic post/page content
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Post_Content extends JTB_Element
{
    public string $slug = 'post_content';
    public string $name = 'Post Content';
    public string $icon = 'file-text';
    public string $category = 'theme';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFields(): array
    {
        return [
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'heading_color' => [
                'label' => 'Heading Color',
                'type' => 'color',
                'default' => '#222222'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 28,
                'step' => 1,
                'default' => 16,
                'unit' => 'px',
                'responsive' => true
            ],
            'line_height' => [
                'label' => 'Line Height',
                'type' => 'range',
                'min' => 1.0,
                'max' => 2.5,
                'step' => 0.1,
                'default' => 1.7,
                'unit' => 'em'
            ],
            'paragraph_spacing' => [
                'label' => 'Paragraph Spacing',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'default' => 20,
                'unit' => 'px'
            ],
            'max_width' => [
                'label' => 'Content Max Width',
                'type' => 'range',
                'min' => 400,
                'max' => 1400,
                'step' => 10,
                'default' => 800,
                'unit' => 'px'
            ],
            'content_alignment' => [
                'label' => 'Content Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center'
                ],
                'default' => 'left'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['id'] ?? 'post_content_' . uniqid();

        $html = '<div id="' . $this->esc($id) . '" class="jtb-post-content">';
        $html .= '<div class="jtb-content-placeholder">';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
        $html .= '<strong>Post Content</strong>';
        $html .= '<span>Your dynamic post content will be displayed here.</span>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        // Main styling
        $textColor = $attrs['text_color'] ?? '#333333';
        $headingColor = $attrs['heading_color'] ?? '#222222';
        $linkColor = $attrs['link_color'] ?? '#2ea3f2';
        $fontSize = $attrs['font_size'] ?? 16;
        $lineHeight = $attrs['line_height'] ?? 1.7;
        $paragraphSpacing = $attrs['paragraph_spacing'] ?? 20;
        $maxWidth = $attrs['max_width'] ?? 800;
        $alignment = $attrs['content_alignment'] ?? 'left';

        // Content container
        $css .= $selector . ' { ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'line-height: ' . floatval($lineHeight) . 'em; ';
        $css .= 'max-width: ' . intval($maxWidth) . 'px; ';
        if ($alignment === 'center') {
            $css .= 'margin-left: auto; margin-right: auto; ';
        }
        $css .= '}' . "\n";

        // Paragraphs
        $css .= $selector . ' p { margin-bottom: ' . intval($paragraphSpacing) . 'px; }' . "\n";
        $css .= $selector . ' p:last-child { margin-bottom: 0; }' . "\n";

        // Headings
        $css .= $selector . ' h1, ' . $selector . ' h2, ' . $selector . ' h3, ';
        $css .= $selector . ' h4, ' . $selector . ' h5, ' . $selector . ' h6 { ';
        $css .= 'color: ' . $headingColor . '; ';
        $css .= 'margin-top: 1.5em; margin-bottom: 0.5em; ';
        $css .= '}' . "\n";

        // Links
        $css .= $selector . ' a { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        if (!empty($attrs['link_color__hover'])) {
            $css .= $selector . ' a:hover { color: ' . $attrs['link_color__hover'] . '; }' . "\n";
        } else {
            $css .= $selector . ' a:hover { text-decoration: underline; }' . "\n";
        }

        // Lists
        $css .= $selector . ' ul, ' . $selector . ' ol { ';
        $css .= 'margin-bottom: ' . intval($paragraphSpacing) . 'px; ';
        $css .= 'padding-left: 1.5em; ';
        $css .= '}' . "\n";

        // Blockquotes
        $css .= $selector . ' blockquote { ';
        $css .= 'border-left: 4px solid ' . $linkColor . '; ';
        $css .= 'padding-left: 20px; ';
        $css .= 'margin: ' . intval($paragraphSpacing) . 'px 0; ';
        $css .= 'font-style: italic; ';
        $css .= 'color: #666; ';
        $css .= '}' . "\n";

        // Images
        $css .= $selector . ' img { max-width: 100%; height: auto; }' . "\n";

        // Placeholder styling
        $css .= $selector . ' .jtb-content-placeholder { ';
        $css .= 'padding: 40px; ';
        $css .= 'background: #f8f9fa; ';
        $css .= 'border: 2px dashed #ddd; ';
        $css .= 'border-radius: 8px; ';
        $css .= 'text-align: center; ';
        $css .= 'color: #888; ';
        $css .= 'display: flex; flex-direction: column; align-items: center; gap: 10px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-content-placeholder svg { opacity: 0.5; }' . "\n";
        $css .= $selector . ' .jtb-content-placeholder strong { font-size: 1.1em; }' . "\n";
        $css .= $selector . ' .jtb-content-placeholder span { font-size: 0.9em; }' . "\n";

        // Responsive
        if (!empty($attrs['font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { font-size: ' . intval($attrs['font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { font-size: ' . intval($attrs['font_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('post_content', JTB_Module_Post_Content::class);
