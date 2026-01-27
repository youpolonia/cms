<?php
/**
 * Sidebar Module
 * Display a widget area/sidebar
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Sidebar extends JTB_Element
{
    public string $icon = 'sidebar';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'sidebar';
    }

    public function getName(): string
    {
        return 'Sidebar';
    }

    public function getFields(): array
    {
        return [
            'area' => [
                'label' => 'Widget Area',
                'type' => 'select',
                'options' => [
                    'sidebar-1' => 'Main Sidebar',
                    'sidebar-2' => 'Secondary Sidebar',
                    'footer-1' => 'Footer Widget Area 1',
                    'footer-2' => 'Footer Widget Area 2'
                ],
                'default' => 'sidebar-1'
            ],
            'widget_title_color' => [
                'label' => 'Widget Title Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'widget_title_font_size' => [
                'label' => 'Widget Title Size',
                'type' => 'range',
                'min' => 14,
                'max' => 32,
                'unit' => 'px',
                'default' => 18
            ],
            'widget_text_color' => [
                'label' => 'Widget Text Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'widget_link_color' => [
                'label' => 'Widget Link Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'widget_spacing' => [
                'label' => 'Widget Spacing',
                'type' => 'range',
                'min' => 10,
                'max' => 60,
                'unit' => 'px',
                'default' => 30
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $area = $attrs['area'] ?? 'sidebar-1';

        // Sample widgets - search icon SVG
        $searchIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
        $widgets = [
            [
                'title' => 'Search',
                'type' => 'search',
                'content' => '<form class="jtb-widget-search-form"><input type="text" placeholder="Search..."><button type="submit">' . $searchIconSvg . '</button></form>'
            ],
            [
                'title' => 'Recent Posts',
                'type' => 'recent-posts',
                'content' => '<ul><li><a href="#">Getting Started with Web Development</a></li><li><a href="#">10 Tips for Better Design</a></li><li><a href="#">The Future of Technology</a></li><li><a href="#">Understanding User Experience</a></li></ul>'
            ],
            [
                'title' => 'Categories',
                'type' => 'categories',
                'content' => '<ul><li><a href="#">Design</a> (12)</li><li><a href="#">Development</a> (8)</li><li><a href="#">Technology</a> (15)</li><li><a href="#">Marketing</a> (6)</li></ul>'
            ],
            [
                'title' => 'Archives',
                'type' => 'archives',
                'content' => '<ul><li><a href="#">January 2025</a></li><li><a href="#">December 2024</a></li><li><a href="#">November 2024</a></li></ul>'
            ]
        ];

        $innerHtml = '<div class="jtb-sidebar-container" data-area="' . $this->esc($area) . '">';

        foreach ($widgets as $widget) {
            $innerHtml .= '<div class="jtb-widget jtb-widget-' . $widget['type'] . '">';
            $innerHtml .= '<h4 class="jtb-widget-title">' . $this->esc($widget['title']) . '</h4>';
            $innerHtml .= '<div class="jtb-widget-content">' . $widget['content'] . '</div>';
            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $titleColor = $attrs['widget_title_color'] ?? '#333333';
        $titleSize = $attrs['widget_title_font_size'] ?? 18;
        $textColor = $attrs['widget_text_color'] ?? '#666666';
        $linkColor = $attrs['widget_link_color'] ?? '#2ea3f2';
        $widgetSpacing = $attrs['widget_spacing'] ?? 30;

        // Widget
        $css .= $selector . ' .jtb-widget { margin-bottom: ' . $widgetSpacing . 'px; }' . "\n";
        $css .= $selector . ' .jtb-widget:last-child { margin-bottom: 0; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-widget-title { '
            . 'color: ' . $titleColor . '; '
            . 'font-size: ' . $titleSize . 'px; '
            . 'margin: 0 0 15px; '
            . 'padding-bottom: 10px; '
            . 'border-bottom: 1px solid #eee; '
            . '}' . "\n";

        // Content
        $css .= $selector . ' .jtb-widget-content { color: ' . $textColor . '; }' . "\n";
        $css .= $selector . ' .jtb-widget-content ul { list-style: none; margin: 0; padding: 0; }' . "\n";
        $css .= $selector . ' .jtb-widget-content li { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }' . "\n";
        $css .= $selector . ' .jtb-widget-content li:last-child { border-bottom: none; }' . "\n";
        $css .= $selector . ' .jtb-widget-content a { color: ' . $linkColor . '; text-decoration: none; transition: color 0.3s ease; }' . "\n";

        if (!empty($attrs['widget_link_color__hover'])) {
            $css .= $selector . ' .jtb-widget-content a:hover { color: ' . $attrs['widget_link_color__hover'] . '; }' . "\n";
        }

        // Search widget
        $css .= $selector . ' .jtb-widget-search-form { display: flex; }' . "\n";
        $css .= $selector . ' .jtb-widget-search-form input { flex: 1; padding: 10px; border: 1px solid #ddd; border-right: none; }' . "\n";
        $css .= $selector . ' .jtb-widget-search-form button { padding: 10px 15px; background: ' . $linkColor . '; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; }' . "\n";
        $css .= $selector . ' .jtb-widget-search-form button svg { width: 14px; height: 14px; }' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('sidebar', JTB_Module_Sidebar::class);
