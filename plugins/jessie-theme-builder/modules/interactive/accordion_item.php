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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        // Header styling
        $titleColor = $attrs['title_color'] ?? '#333333';
        $titleSize = $attrs['title_font_size'] ?? 16;
        $iconColor = $attrs['icon_color'] ?? '#666666';
        $headerBg = $attrs['header_bg_color'] ?? '#f8f9fa';
        $contentBg = $attrs['content_bg_color'] ?? '#ffffff';

        // Accordion item container
        $css .= $selector . ' { ';
        $css .= 'border: 1px solid #e0e0e0; ';
        $css .= 'border-radius: 4px; ';
        $css .= 'margin-bottom: 10px; ';
        $css .= 'overflow: hidden; ';
        $css .= '}' . "\n";

        // Header
        $css .= $selector . ' .jtb-accordion-header { ';
        $css .= 'display: flex; ';
        $css .= 'justify-content: space-between; ';
        $css .= 'align-items: center; ';
        $css .= 'padding: 15px 20px; ';
        $css .= 'background-color: ' . $headerBg . '; ';
        $css .= 'cursor: pointer; ';
        $css .= 'transition: background-color 0.3s ease; ';
        $css .= '}' . "\n";

        // Header hover
        if (!empty($attrs['header_bg_color__hover'])) {
            $css .= $selector . ' .jtb-accordion-header:hover { background-color: ' . $attrs['header_bg_color__hover'] . '; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-accordion-header:hover { filter: brightness(0.97); }' . "\n";
        }

        // Title
        $css .= $selector . ' .jtb-accordion-title { ';
        $css .= 'margin: 0; ';
        $css .= 'font-size: ' . intval($titleSize) . 'px; ';
        $css .= 'font-weight: 600; ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        if (!empty($attrs['title_color__hover'])) {
            $css .= $selector . ' .jtb-accordion-header:hover .jtb-accordion-title { color: ' . $attrs['title_color__hover'] . '; }' . "\n";
        }

        // Icon
        $css .= $selector . ' .jtb-accordion-icon { ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'transition: transform 0.3s ease; ';
        $css .= '}' . "\n";

        // Icon rotation when open
        $css .= $selector . '.jtb-open .jtb-accordion-icon { transform: rotate(180deg); }' . "\n";

        // Content
        $css .= $selector . ' .jtb-accordion-content { ';
        $css .= 'background-color: ' . $contentBg . '; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-accordion-content-inner { ';
        $css .= 'padding: 20px; ';
        $css .= '}' . "\n";

        return $css;
    }
}

JTB_Registry::register('accordion_item', JTB_Module_AccordionItem::class);
