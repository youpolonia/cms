<?php
/**
 * Portfolio Module
 * Portfolio/Projects grid with filtering
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Portfolio extends JTB_Element
{
    public string $icon = 'portfolio';
    public string $category = 'blog';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'portfolio';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'gutter' => [
            'property' => 'gap',
            'selector' => '.jtb-portfolio-container',
            'unit' => 'px'
        ],
        'hover_overlay_color' => [
            'property' => 'background',
            'selector' => '.jtb-portfolio-overlay'
        ],
        'zoom_icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-portfolio-zoom'
        ],
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-portfolio-title',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'portfolio';
    }

    public function getName(): string
    {
        return 'Portfolio';
    }

    public function getFields(): array
    {
        return [
            'fullwidth' => [
                'label' => 'Fullwidth Layout',
                'type' => 'toggle',
                'default' => false
            ],
            'posts_number' => [
                'label' => 'Projects Count',
                'type' => 'range',
                'min' => 1,
                'max' => 50,
                'default' => 8
            ],
            'include_categories' => [
                'label' => 'Include Categories',
                'type' => 'text',
                'description' => 'Comma-separated category IDs'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'show_categories' => [
                'label' => 'Show Categories',
                'type' => 'toggle',
                'default' => true
            ],
            'show_pagination' => [
                'label' => 'Show Pagination',
                'type' => 'toggle',
                'default' => false
            ],
            'zoom_icon_color' => [
                'label' => 'Zoom Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'hover_overlay_color' => [
                'label' => 'Hover Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.7)'
            ],
            'columns' => [
                'label' => 'Columns',
                'type' => 'select',
                'options' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                    '5' => '5 Columns',
                    '6' => '6 Columns'
                ],
                'default' => '4',
                'responsive' => true
            ],
            'gutter' => [
                'label' => 'Gutter Width',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 10
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 40,
                'unit' => 'px',
                'default' => 18,
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $postsNumber = $attrs['posts_number'] ?? 8;
        $showTitle = $attrs['show_title'] ?? true;
        $showCategories = $attrs['show_categories'] ?? true;
        $columns = $attrs['columns'] ?? '4';

        $innerHtml = '<div class="jtb-portfolio-container jtb-portfolio-cols-' . $columns . '">';

        // Sample portfolio items
        $sampleItems = [
            ['title' => 'Project Alpha', 'categories' => ['Web Design'], 'image' => ''],
            ['title' => 'Project Beta', 'categories' => ['Branding'], 'image' => ''],
            ['title' => 'Project Gamma', 'categories' => ['Web Design', 'Development'], 'image' => ''],
            ['title' => 'Project Delta', 'categories' => ['Photography'], 'image' => ''],
            ['title' => 'Project Epsilon', 'categories' => ['Branding'], 'image' => ''],
            ['title' => 'Project Zeta', 'categories' => ['Development'], 'image' => ''],
            ['title' => 'Project Eta', 'categories' => ['Web Design'], 'image' => ''],
            ['title' => 'Project Theta', 'categories' => ['Photography'], 'image' => ''],
        ];

        foreach ($sampleItems as $item) {
            $innerHtml .= '<div class="jtb-portfolio-item">';
            $innerHtml .= '<div class="jtb-portfolio-image">';
            $innerHtml .= '<a href="#">';

            if (!empty($item['image'])) {
                $innerHtml .= '<img src="' . $this->esc($item['image']) . '" alt="' . $this->esc($item['title']) . '" />';
            } else {
                $innerHtml .= '<div class="jtb-portfolio-placeholder"></div>';
            }

            $innerHtml .= '<div class="jtb-portfolio-overlay">';
            $innerHtml .= '<div class="jtb-portfolio-overlay-content">';

            if ($showTitle) {
                $innerHtml .= '<h3 class="jtb-portfolio-title">' . $this->esc($item['title']) . '</h3>';
            }

            if ($showCategories && !empty($item['categories'])) {
                $innerHtml .= '<div class="jtb-portfolio-categories">' . implode(', ', array_map([$this, 'esc'], $item['categories'])) . '</div>';
            }

            $innerHtml .= '<span class="jtb-portfolio-zoom">+</span>';
            $innerHtml .= '</div>';
            $innerHtml .= '</div>';
            $innerHtml .= '</a>';
            $innerHtml .= '</div>';
            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Portfolio module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $columns = $attrs['columns'] ?? '4';

        // Grid
        $css .= $selector . ' .jtb-portfolio-container { display: grid; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-2 { grid-template-columns: repeat(2, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-3 { grid-template-columns: repeat(3, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-4 { grid-template-columns: repeat(4, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-5 { grid-template-columns: repeat(5, 1fr); }' . "\n";
        $css .= $selector . ' .jtb-portfolio-cols-6 { grid-template-columns: repeat(6, 1fr); }' . "\n";

        // Item
        $css .= $selector . ' .jtb-portfolio-item { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-image { position: relative; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-image a { display: block; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-image img { width: 100%; height: auto; display: block; }' . "\n";

        // Placeholder
        $css .= $selector . ' .jtb-portfolio-placeholder { background: #f0f0f0; padding-bottom: 100%; }' . "\n";

        // Overlay
        $css .= $selector . ' .jtb-portfolio-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease; }' . "\n";
        $css .= $selector . ' .jtb-portfolio-item:hover .jtb-portfolio-overlay { opacity: 1; }' . "\n";

        // Overlay content
        $css .= $selector . ' .jtb-portfolio-overlay-content { text-align: center; color: #fff; padding: 20px; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-portfolio-title { margin: 0 0 10px; }' . "\n";

        // Categories
        $css .= $selector . ' .jtb-portfolio-categories { font-size: 13px; opacity: 0.8; margin-bottom: 15px; }' . "\n";

        // Zoom icon
        $zoomColor = $attrs['zoom_icon_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-portfolio-zoom { display: inline-flex; width: 40px; height: 40px; border: 2px solid ' . $zoomColor . '; font-size: 24px; align-items: center; justify-content: center; border-radius: 50%; }' . "\n";

        // Responsive
        $tabletCols = $attrs['columns__tablet'] ?? '3';
        $phoneCols = $attrs['columns__phone'] ?? '2';

        $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-portfolio-container { grid-template-columns: repeat(' . $tabletCols . ', 1fr); } }' . "\n";
        $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-portfolio-container { grid-template-columns: repeat(' . $phoneCols . ', 1fr); } }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('portfolio', JTB_Module_Portfolio::class);
