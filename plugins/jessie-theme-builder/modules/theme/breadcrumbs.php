<?php
/**
 * Breadcrumbs Module
 * Displays navigation breadcrumbs
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Breadcrumbs extends JTB_Element
{
    public string $slug = 'breadcrumbs';
    public string $name = 'Breadcrumbs';
    public string $icon = 'chevrons-right';
    public string $category = 'dynamic';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'breadcrumbs';

    protected array $style_config = [
        'text_color' => [
            'property' => 'color'
        ],
        'link_color' => [
            'property' => 'color',
            'selector' => '.jtb-bc-link',
            'hover' => true
        ],
        'separator_color' => [
            'property' => 'color',
            'selector' => '.jtb-bc-separator, .jtb-bc-sep-text, .jtb-bc-sep-icon'
        ],
        'current_color' => [
            'property' => 'color',
            'selector' => '.jtb-bc-current-text'
        ],
        'font_size' => [
            'property' => 'font-size',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

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
            'home_text' => [
                'label' => 'Home Text',
                'type' => 'text',
                'default' => 'Home'
            ],
            'home_icon' => [
                'label' => 'Show Home Icon',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Show home icon instead of/with text'
            ],
            'separator' => [
                'label' => 'Separator',
                'type' => 'select',
                'options' => [
                    '/' => '/ (Slash)',
                    '>' => '> (Arrow)',
                    '>>' => '>> (Double Arrow)',
                    '|' => '| (Pipe)',
                    '-' => '- (Dash)',
                    'chevron' => 'Chevron Icon'
                ],
                'default' => 'chevron'
            ],
            'show_current' => [
                'label' => 'Show Current Page',
                'type' => 'toggle',
                'default' => true
            ],
            'link_current' => [
                'label' => 'Link Current Page',
                'type' => 'toggle',
                'default' => false
            ],
            'show_category' => [
                'label' => 'Show Category in Posts',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Include category in breadcrumb trail for posts'
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#7c3aed',
                'hover' => true
            ],
            'separator_color' => [
                'label' => 'Separator Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'current_color' => [
                'label' => 'Current Page Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 11,
                'max' => 18,
                'step' => 1,
                'default' => 14,
                'unit' => 'px',
                'responsive' => true
            ],
            'text_alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'left',
                'responsive' => true
            ],
            'schema_markup' => [
                'label' => 'Schema Markup',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Add structured data for SEO'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'breadcrumbs_' . uniqid();
        $homeText = $attrs['home_text'] ?? 'Home';
        $homeIcon = $attrs['home_icon'] ?? true;
        $separator = $attrs['separator'] ?? 'chevron';
        $showCurrent = $attrs['show_current'] ?? true;
        $linkCurrent = $attrs['link_current'] ?? false;
        $schema = $attrs['schema_markup'] ?? true;

        // Get dynamic breadcrumbs
        $isPreview = JTB_Dynamic_Context::isPreviewMode();
        $crumbs = JTB_Dynamic_Context::getBreadcrumbs();

        // Fallback for preview mode
        if (empty($crumbs) || $isPreview) {
            $crumbs = [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Category', 'url' => '/category/example'],
                ['label' => 'Current Page Title', 'url' => '']
            ];
        }

        // Override home text
        if (!empty($crumbs[0])) {
            $crumbs[0]['label'] = $homeText;
        }

        // SVG icons
        $chevronIcon = '<svg class="jtb-bc-sep-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';

        $homeIconSvg = '<svg class="jtb-bc-home-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';

        // Separator HTML
        if ($separator === 'chevron') {
            $sepHtml = '<li class="jtb-bc-separator" aria-hidden="true">' . $chevronIcon . '</li>';
        } else {
            $sepHtml = '<li class="jtb-bc-separator" aria-hidden="true"><span class="jtb-bc-sep-text">' . $this->esc($separator) . '</span></li>';
        }

        $wrapperAttrs = $schema ? ' itemscope itemtype="https://schema.org/BreadcrumbList"' : '';

        $html = '<nav id="' . $this->esc($id) . '" class="jtb-breadcrumbs"' . $wrapperAttrs . '>';
        $html .= '<ol class="jtb-bc-list">';

        $position = 0;
        $lastIndex = count($crumbs) - 1;

        foreach ($crumbs as $index => $crumb) {
            $position++;
            $isLast = ($index === $lastIndex);
            $isCurrent = $isLast && $showCurrent;
            $itemAttrs = $schema ? ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"' : '';

            // Add separator before items (except first)
            if ($index > 0) {
                $html .= $sepHtml;
            }

            // Skip last item if showCurrent is false
            if ($isLast && !$showCurrent) {
                continue;
            }

            $html .= '<li class="jtb-bc-item' . ($isCurrent ? ' jtb-bc-current' : '') . '"' . $itemAttrs . '>';

            // Render as link or text
            $hasUrl = !empty($crumb['url']) && (!$isCurrent || $linkCurrent);

            if ($hasUrl) {
                $html .= '<a href="' . $this->esc($crumb['url']) . '" class="jtb-bc-link"' . ($schema ? ' itemprop="item"' : '') . '>';
            }

            // Home icon (only for first item)
            if ($index === 0 && $homeIcon) {
                $html .= $homeIconSvg;
            }

            if ($isCurrent && !$hasUrl) {
                $html .= '<span class="jtb-bc-current-text"' . ($schema ? ' itemprop="name"' : '') . '>' . $this->esc($crumb['label']) . '</span>';
            } else {
                $html .= '<span' . ($schema ? ' itemprop="name"' : '') . '>' . $this->esc($crumb['label']) . '</span>';
            }

            if ($hasUrl) {
                $html .= '</a>';
            }

            if ($schema) {
                $html .= '<meta itemprop="position" content="' . $position . '" />';
            }

            $html .= '</li>';
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $textColor = $attrs['text_color'] ?? '#6b7280';
        $linkColor = $attrs['link_color'] ?? '#7c3aed';
        $linkHoverColor = $attrs['link_color__hover'] ?? '#5b21b6';
        $sepColor = $attrs['separator_color'] ?? '#9ca3af';
        $currentColor = $attrs['current_color'] ?? '#374151';
        $fontSize = $attrs['font_size'] ?? 14;
        $alignment = $attrs['text_alignment'] ?? 'left';

        // Justify content based on alignment
        $justifyContent = 'flex-start';
        if ($alignment === 'center') $justifyContent = 'center';
        if ($alignment === 'right') $justifyContent = 'flex-end';

        // Container
        $css .= $selector . ' { ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= '}' . "\n";

        // List
        $css .= $selector . ' .jtb-bc-list { ';
        $css .= 'list-style: none; ';
        $css .= 'margin: 0; ';
        $css .= 'padding: 0; ';
        $css .= 'display: flex; ';
        $css .= 'flex-wrap: wrap; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: ' . $justifyContent . '; ';
        $css .= '}' . "\n";

        // Item
        $css .= $selector . ' .jtb-bc-item { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= '}' . "\n";

        // Link
        $css .= $selector . ' .jtb-bc-link { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-bc-link:hover { ';
        $css .= 'color: ' . $linkHoverColor . '; ';
        $css .= '}' . "\n";

        // Home icon
        $css .= $selector . ' .jtb-bc-home-icon { ';
        $css .= 'margin-right: 4px; ';
        $css .= '}' . "\n";

        // Separator
        $css .= $selector . ' .jtb-bc-separator { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'margin: 0 6px; ';
        $css .= 'color: ' . $sepColor . '; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-bc-sep-text { ';
        $css .= 'color: ' . $sepColor . '; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-bc-sep-icon { ';
        $css .= 'color: ' . $sepColor . '; ';
        $css .= '}' . "\n";

        // Current page
        $css .= $selector . ' .jtb-bc-current-text { ';
        $css .= 'color: ' . $currentColor . '; ';
        $css .= 'font-weight: 500; ';
        $css .= '}' . "\n";

        // Responsive - Font size
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

        // Responsive - Alignment
        if (!empty($attrs['text_alignment__tablet'])) {
            $justifyTablet = $attrs['text_alignment__tablet'] === 'center' ? 'center' : ($attrs['text_alignment__tablet'] === 'right' ? 'flex-end' : 'flex-start');
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-bc-list { justify-content: ' . $justifyTablet . '; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['text_alignment__phone'])) {
            $justifyPhone = $attrs['text_alignment__phone'] === 'center' ? 'center' : ($attrs['text_alignment__phone'] === 'right' ? 'flex-end' : 'flex-start');
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-bc-list { justify-content: ' . $justifyPhone . '; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('breadcrumbs', JTB_Module_Breadcrumbs::class);
