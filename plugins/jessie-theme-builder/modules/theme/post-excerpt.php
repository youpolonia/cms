<?php
/**
 * Post Excerpt Module
 * Displays the post excerpt/summary
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Post_Excerpt extends JTB_Element
{
    public string $slug = 'post_excerpt';
    public string $name = 'Post Excerpt';
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
            'excerpt_length' => [
                'label' => 'Excerpt Length',
                'type' => 'range',
                'min' => 20,
                'max' => 100,
                'step' => 5,
                'default' => 55,
                'unit' => 'words',
                'description' => 'Number of words to display'
            ],
            'show_read_more' => [
                'label' => 'Show Read More Link',
                'type' => 'toggle',
                'default' => true
            ],
            'read_more_text' => [
                'label' => 'Read More Text',
                'type' => 'text',
                'default' => 'Read More'
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#4b5563'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#7c3aed',
                'hover' => true
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 24,
                'step' => 1,
                'default' => 16,
                'unit' => 'px',
                'responsive' => true
            ],
            'line_height' => [
                'label' => 'Line Height',
                'type' => 'range',
                'min' => 1,
                'max' => 2.5,
                'step' => 0.1,
                'default' => 1.6,
                'unit' => ''
            ],
            'text_alignment' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justify' => 'Justify'
                ],
                'default' => 'left',
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['id'] ?? 'post_excerpt_' . uniqid();
        $alignment = $attrs['text_alignment'] ?? 'left';
        $showReadMore = $attrs['show_read_more'] ?? true;
        $readMoreText = $attrs['read_more_text'] ?? 'Read More';

        $classes = ['jtb-post-excerpt', 'jtb-align-' . $this->esc($alignment)];

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';
        $html .= '<p class="jtb-excerpt-text">This is where your post excerpt will appear. The excerpt provides a brief summary of your post content, giving readers a preview of what to expect when they click through to read the full article.</p>';

        if ($showReadMore) {
            $html .= '<a href="#" class="jtb-read-more">' . $this->esc($readMoreText) . ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>';
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $textColor = $attrs['text_color'] ?? '#4b5563';
        $linkColor = $attrs['link_color'] ?? '#7c3aed';
        $fontSize = $attrs['font_size'] ?? 16;
        $lineHeight = $attrs['line_height'] ?? 1.6;
        $alignment = $attrs['text_alignment'] ?? 'left';

        // Container
        $css .= $selector . ' { ';
        $css .= 'text-align: ' . $alignment . '; ';
        $css .= '}' . "\n";

        // Text
        $css .= $selector . ' .jtb-excerpt-text { ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'line-height: ' . floatval($lineHeight) . '; ';
        $css .= 'margin: 0 0 16px; ';
        $css .= '}' . "\n";

        // Read more link
        $css .= $selector . ' .jtb-read-more { ';
        $css .= 'display: inline-flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: 6px; ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'font-weight: 500; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease, gap 0.3s ease; ';
        $css .= '}' . "\n";

        // Hover
        if (!empty($attrs['link_color__hover'])) {
            $css .= $selector . ' .jtb-read-more:hover { color: ' . $attrs['link_color__hover'] . '; gap: 10px; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-read-more:hover { gap: 10px; opacity: 0.8; }' . "\n";
        }

        // Responsive
        if (!empty($attrs['font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-excerpt-text { font-size: ' . intval($attrs['font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-excerpt-text { font-size: ' . intval($attrs['font_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['text_alignment__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['text_alignment__tablet'] . '; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['text_alignment__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['text_alignment__phone'] . '; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('post_excerpt', JTB_Module_Post_Excerpt::class);
