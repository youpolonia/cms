<?php
/**
 * Toggle Module
 * Single collapsible content block (standalone)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Toggle extends JTB_Element
{
    public string $icon = 'toggle';
    public string $category = 'interactive';

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
        return 'toggle';
    }

    public function getName(): string
    {
        return 'Toggle';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Toggle Title'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'richtext',
                'default' => '<p>Your toggle content goes here.</p>'
            ],
            'open' => [
                'label' => 'Open by Default',
                'type' => 'toggle',
                'default' => false
            ],
            'header_level' => [
                'label' => 'Title Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6'
                ],
                'default' => 'h5'
            ],
            'toggle_icon' => [
                'label' => 'Icon Style',
                'type' => 'select',
                'options' => [
                    'arrow' => 'Arrow',
                    'plus' => 'Plus/Minus',
                    'none' => 'None'
                ],
                'default' => 'arrow'
            ],
            'icon_position' => [
                'label' => 'Icon Position',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'right' => 'Right'
                ],
                'default' => 'right'
            ],
            'open_title_color' => [
                'label' => 'Open Title Color',
                'type' => 'color',
                'hover' => true
            ],
            'open_bg_color' => [
                'label' => 'Open Background',
                'type' => 'color',
                'hover' => true
            ],
            'closed_title_color' => [
                'label' => 'Closed Title Color',
                'type' => 'color',
                'hover' => true
            ],
            'closed_bg_color' => [
                'label' => 'Closed Background',
                'type' => 'color',
                'hover' => true
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#666666',
                'hover' => true
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'range',
                'min' => 10,
                'max' => 40,
                'unit' => 'px',
                'default' => 16
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $title = $this->esc($attrs['title'] ?? 'Toggle Title');
        $bodyContent = $attrs['content'] ?? '<p>Your toggle content goes here.</p>';
        $isOpen = !empty($attrs['open']);
        $headerLevel = $attrs['header_level'] ?? 'h5';
        $iconStyle = $attrs['toggle_icon'] ?? 'arrow';
        $iconPosition = $attrs['icon_position'] ?? 'right';

        // SVG icons for toggle
        $chevronIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
        $plusIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';

        $icon = '';
        if ($iconStyle === 'arrow') {
            $icon = '<span class="jtb-toggle-icon">' . $chevronIcon . '</span>';
        } elseif ($iconStyle === 'plus') {
            $icon = '<span class="jtb-toggle-icon">' . $plusIcon . '</span>';
        }

        $containerClass = 'jtb-toggle-container';
        $containerClass .= $isOpen ? ' jtb-open' : '';
        $containerClass .= ' jtb-toggle-icon-' . $iconStyle;
        $containerClass .= ' jtb-toggle-icon-pos-' . $iconPosition;

        $innerHtml = '<div class="' . $containerClass . '">';
        $innerHtml .= '<div class="jtb-toggle-header" role="button" tabindex="0">';

        if ($iconPosition === 'left') {
            $innerHtml .= $icon;
        }

        $innerHtml .= '<' . $headerLevel . ' class="jtb-toggle-title">' . $title . '</' . $headerLevel . '>';

        if ($iconPosition === 'right') {
            $innerHtml .= $icon;
        }

        $innerHtml .= '</div>';
        $innerHtml .= '<div class="jtb-toggle-content">' . $bodyContent . '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Toggle container
        $css .= $selector . ' .jtb-toggle-container { border: 1px solid #d9d9d9; }' . "\n";

        // Header
        $css .= $selector . ' .jtb-toggle-header { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: center; ';
        $css .= 'padding: 15px 20px; ';
        $css .= 'cursor: pointer; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= '}' . "\n";

        // Icon position
        $iconPos = $attrs['icon_position'] ?? 'right';
        if ($iconPos === 'right') {
            $css .= $selector . ' .jtb-toggle-header { justify-content: space-between; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-toggle-header { }' . "\n";
            $css .= $selector . ' .jtb-toggle-icon { margin-right: 15px; }' . "\n";
        }

        // Title
        $css .= $selector . ' .jtb-toggle-title { margin: 0; flex-grow: 1; }' . "\n";

        // Icon
        $iconColor = $attrs['icon_color'] ?? '#666666';
        $iconSize = $attrs['icon_size'] ?? 16;
        $css .= $selector . ' .jtb-toggle-icon { ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'display: inline-flex; ';
        $css .= 'align-items: center; ';
        $css .= 'justify-content: center; ';
        $css .= 'transition: transform 0.3s ease; ';
        $css .= '}' . "\n";
        $css .= $selector . ' .jtb-toggle-icon svg { width: ' . $iconSize . 'px; height: ' . $iconSize . 'px; }' . "\n";

        // Icon rotation when open
        $css .= $selector . ' .jtb-toggle-container.jtb-open .jtb-toggle-icon { transform: rotate(180deg); }' . "\n";
        $css .= $selector . ' .jtb-toggle-container.jtb-open.jtb-toggle-icon-plus .jtb-toggle-icon { transform: rotate(45deg); }' . "\n";

        // Content
        $css .= $selector . ' .jtb-toggle-content { ';
        $css .= 'display: none; ';
        $css .= 'padding: 20px; ';
        $css .= 'border-top: 1px solid #d9d9d9; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-toggle-container.jtb-open .jtb-toggle-content { display: block; }' . "\n";

        // Open state styles
        if (!empty($attrs['open_title_color'])) {
            $css .= $selector . ' .jtb-toggle-container.jtb-open .jtb-toggle-title { color: ' . $attrs['open_title_color'] . '; }' . "\n";
        }
        if (!empty($attrs['open_bg_color'])) {
            $css .= $selector . ' .jtb-toggle-container.jtb-open .jtb-toggle-header { background-color: ' . $attrs['open_bg_color'] . '; }' . "\n";
        }

        // Closed state styles
        if (!empty($attrs['closed_title_color'])) {
            $css .= $selector . ' .jtb-toggle-container:not(.jtb-open) .jtb-toggle-title { color: ' . $attrs['closed_title_color'] . '; }' . "\n";
        }
        if (!empty($attrs['closed_bg_color'])) {
            $css .= $selector . ' .jtb-toggle-container:not(.jtb-open) .jtb-toggle-header { background-color: ' . $attrs['closed_bg_color'] . '; }' . "\n";
        }

        // Hover states
        if (!empty($attrs['icon_color__hover'])) {
            $css .= $selector . ' .jtb-toggle-header:hover .jtb-toggle-icon { color: ' . $attrs['icon_color__hover'] . '; }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('toggle', JTB_Module_Toggle::class);
