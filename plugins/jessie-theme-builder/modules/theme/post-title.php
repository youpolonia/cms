<?php
/**
 * Post Title Module
 * Displays the dynamic post/page title
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Post_Title extends JTB_Element
{
    public string $slug = 'post_title';
    public string $name = 'Post Title';
    public string $icon = 'type';
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
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'show_meta' => [
                'label' => 'Show Meta',
                'type' => 'toggle',
                'description' => 'Display post meta (author, date, categories)',
                'default' => true
            ],
            'title_level' => [
                'label' => 'Title Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'p' => 'Paragraph'
                ],
                'default' => 'h1'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 120,
                'step' => 1,
                'default' => 36,
                'unit' => 'px',
                'responsive' => true
            ],
            'title_font_weight' => [
                'label' => 'Title Font Weight',
                'type' => 'select',
                'options' => [
                    '300' => 'Light (300)',
                    '400' => 'Normal (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi Bold (600)',
                    '700' => 'Bold (700)',
                    '800' => 'Extra Bold (800)'
                ],
                'default' => '700'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ],
            'title_line_height' => [
                'label' => 'Title Line Height',
                'type' => 'range',
                'min' => 0.8,
                'max' => 2.5,
                'step' => 0.1,
                'default' => 1.2,
                'unit' => 'em'
            ],
            'meta_font_size' => [
                'label' => 'Meta Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 24,
                'step' => 1,
                'default' => 14,
                'unit' => 'px'
            ],
            'meta_color' => [
                'label' => 'Meta Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'meta_link_color' => [
                'label' => 'Meta Link Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'meta_spacing' => [
                'label' => 'Meta Top Spacing',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'default' => 10,
                'unit' => 'px'
            ],
            'text_alignment' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'left',
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['id'] ?? 'post_title_' . uniqid();
        $showTitle = $attrs['show_title'] ?? true;
        $showMeta = $attrs['show_meta'] ?? true;
        $titleLevel = $attrs['title_level'] ?? 'h1';
        $alignment = $attrs['text_alignment'] ?? 'left';

        // Validate title level
        $allowedLevels = ['h1', 'h2', 'h3', 'h4', 'p'];
        if (!in_array($titleLevel, $allowedLevels)) {
            $titleLevel = 'h1';
        }

        $classes = ['jtb-post-title', 'jtb-align-' . $this->esc($alignment)];

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';

        if ($showTitle) {
            $html .= '<' . $titleLevel . ' class="jtb-title">';
            $html .= 'Your Dynamic Post Title Will Display Here';
            $html .= '</' . $titleLevel . '>';
        }

        if ($showMeta) {
            $html .= '<div class="jtb-post-meta">';
            $html .= 'By <a href="#">Author</a> | ' . date('F j, Y') . ' | In <a href="#">Category</a>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        // Title styling
        $titleSize = $attrs['title_font_size'] ?? 36;
        $titleWeight = $attrs['title_font_weight'] ?? '700';
        $titleColor = $attrs['title_color'] ?? '#333333';
        $titleLineHeight = $attrs['title_line_height'] ?? 1.2;
        $alignment = $attrs['text_alignment'] ?? 'left';

        $css .= $selector . ' { text-align: ' . $alignment . '; }' . "\n";

        $css .= $selector . ' .jtb-title { ';
        $css .= 'font-size: ' . intval($titleSize) . 'px; ';
        $css .= 'font-weight: ' . $titleWeight . '; ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= 'line-height: ' . floatval($titleLineHeight) . 'em; ';
        $css .= 'margin: 0; ';
        $css .= '}' . "\n";

        // Title hover
        if (!empty($attrs['title_color__hover'])) {
            $css .= $selector . ':hover .jtb-title { color: ' . $attrs['title_color__hover'] . '; }' . "\n";
        }

        // Meta styling
        $metaSize = $attrs['meta_font_size'] ?? 14;
        $metaColor = $attrs['meta_color'] ?? '#666666';
        $metaLinkColor = $attrs['meta_link_color'] ?? '#2ea3f2';
        $metaSpacing = $attrs['meta_spacing'] ?? 10;

        $css .= $selector . ' .jtb-post-meta { ';
        $css .= 'font-size: ' . intval($metaSize) . 'px; ';
        $css .= 'color: ' . $metaColor . '; ';
        $css .= 'margin-top: ' . intval($metaSpacing) . 'px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-post-meta a { ';
        $css .= 'color: ' . $metaLinkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        if (!empty($attrs['meta_link_color__hover'])) {
            $css .= $selector . ' .jtb-post-meta a:hover { color: ' . $attrs['meta_link_color__hover'] . '; }' . "\n";
        }

        // Responsive title size
        if (!empty($attrs['title_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-title { font-size: ' . intval($attrs['title_font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['title_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-title { font-size: ' . intval($attrs['title_font_size__phone']) . 'px; }';
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

JTB_Registry::register('post_title', JTB_Module_Post_Title::class);
