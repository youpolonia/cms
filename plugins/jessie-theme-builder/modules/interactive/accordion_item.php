<?php
/**
 * Accordion Item Module (Child)
 * Single accordion panel
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_AccordionItem extends JTB_Element
{
    public string $icon = 'chevron-down';
    public string $category = 'interactive';
    public bool $is_child = true;

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = false;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'accordion_item';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-accordion-title',
            'hover' => true
        ],
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-accordion-title',
            'unit' => 'px'
        ],
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-accordion-icon'
        ],
        'header_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-accordion-header',
            'hover' => true
        ],
        'content_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-accordion-content'
        ]
    ];

    public function getSlug(): string
    {
        return 'accordion_item';
    }

    public function getName(): string
    {
        return 'Accordion Item';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Accordion Title'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'richtext',
                'default' => '<p>Your accordion content goes here.</p>'
            ],
            'open' => [
                'label' => 'Open by Default',
                'type' => 'toggle',
                'default' => false
            ],
            'title_tag' => [
                'label' => 'Title HTML Tag',
                'type' => 'select',
                'options' => [
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'DIV'
                ],
                'default' => 'h4'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#333333',
                'hover' => true
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 36,
                'step' => 1,
                'default' => 16,
                'unit' => 'px'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'header_bg_color' => [
                'label' => 'Header Background',
                'type' => 'color',
                'default' => '#f8f9fa',
                'hover' => true
            ],
            'content_bg_color' => [
                'label' => 'Content Background',
                'type' => 'color',
                'default' => '#ffffff'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Accordion Title');
        $bodyContent = $attrs['content'] ?? '<p>Your accordion content goes here.</p>';
        $isOpen = !empty($attrs['open']);
        $titleTag = $attrs['title_tag'] ?? 'h4';

        // Validate title tag
        $allowedTags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div'];
        if (!in_array($titleTag, $allowedTags)) {
            $titleTag = 'h4';
        }

        $itemClass = 'jtb-accordion-item' . ($isOpen ? ' jtb-open' : '');

        // SVG chevron icon
        $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        $html = '<div class="' . $itemClass . '">';
        $html .= '<div class="jtb-accordion-header" role="button" tabindex="0" aria-expanded="' . ($isOpen ? 'true' : 'false') . '">';
        $html .= '<' . $titleTag . ' class="jtb-accordion-title">' . $title . '</' . $titleTag . '>';
        $html .= '<span class="jtb-accordion-icon">' . $iconSvg . '</span>';
        $html .= '</div>';
        $html .= '<div class="jtb-accordion-content"' . ($isOpen ? '' : ' style="display: none;"') . '>';
        $html .= '<div class="jtb-accordion-content-inner">' . $bodyContent . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate CSS for Accordion Item module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Accordion item container
        $css .= $selector . ' { border: 1px solid #e0e0e0; border-radius: 4px; margin-bottom: 10px; overflow: hidden; }' . "\n";

        // Header
        $css .= $selector . ' .jtb-accordion-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; cursor: pointer; transition: background-color 0.3s ease; }' . "\n";

        // Header hover fallback
        if (empty($attrs['header_bg_color__hover'])) {
            $css .= $selector . ' .jtb-accordion-header:hover { filter: brightness(0.97); }' . "\n";
        }

        // Title
        $css .= $selector . ' .jtb-accordion-title { margin: 0; font-weight: 600; transition: color 0.3s ease; }' . "\n";

        // Icon
        $css .= $selector . ' .jtb-accordion-icon { display: flex; align-items: center; transition: transform 0.3s ease; }' . "\n";

        // Icon rotation when open
        $css .= $selector . '.jtb-open .jtb-accordion-icon { transform: rotate(180deg); }' . "\n";

        // Content inner
        $css .= $selector . ' .jtb-accordion-content-inner { padding: 20px; }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('accordion_item', JTB_Module_AccordionItem::class);
