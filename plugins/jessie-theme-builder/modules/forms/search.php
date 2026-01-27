<?php
/**
 * Search Module
 * Site search form
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Search extends JTB_Element
{
    public string $icon = 'search';
    public string $category = 'forms';

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
        return 'search';
    }

    public function getName(): string
    {
        return 'Search';
    }

    public function getFields(): array
    {
        return [
            'placeholder' => [
                'label' => 'Placeholder Text',
                'type' => 'text',
                'default' => 'Search...'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Search'
            ],
            'show_button' => [
                'label' => 'Show Button',
                'type' => 'toggle',
                'default' => true
            ],
            'use_icon' => [
                'label' => 'Use Icon Instead of Text',
                'type' => 'toggle',
                'default' => false,
                'show_if' => ['show_button' => true]
            ],
            // Styling
            'field_bg_color' => [
                'label' => 'Field Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'field_text_color' => [
                'label' => 'Field Text Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'field_border_color' => [
                'label' => 'Field Border Color',
                'type' => 'color',
                'default' => '#bbb',
                'hover' => true
            ],
            'field_border_width' => [
                'label' => 'Field Border Width',
                'type' => 'range',
                'min' => 0,
                'max' => 5,
                'unit' => 'px',
                'default' => 1
            ],
            'field_border_radius' => [
                'label' => 'Field Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 0
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text/Icon Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $placeholder = $this->esc($attrs['placeholder'] ?? 'Search...');
        $buttonText = $this->esc($attrs['button_text'] ?? 'Search');
        $showButton = $attrs['show_button'] ?? true;
        $useIcon = !empty($attrs['use_icon']);

        $formId = 'jtb-search-form-' . $this->generateId();

        $innerHtml = '<div class="jtb-search-container">';
        $innerHtml .= '<form class="jtb-search-form" id="' . $formId . '" method="get" action="/">';

        $innerHtml .= '<div class="jtb-search-field">';
        $innerHtml .= '<input type="text" name="s" placeholder="' . $placeholder . '" class="jtb-search-input">';
        $innerHtml .= '</div>';

        if ($showButton) {
            $searchIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
            $innerHtml .= '<button type="submit" class="jtb-button jtb-search-submit">';
            if ($useIcon) {
                $innerHtml .= '<span class="jtb-icon jtb-icon-search">' . $searchIcon . '</span>';
            } else {
                $innerHtml .= $buttonText;
            }
            $innerHtml .= '</button>';
        }

        $innerHtml .= '</form>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Container
        $css .= $selector . ' .jtb-search-form { display: flex; }' . "\n";

        // Field
        $fieldBg = $attrs['field_bg_color'] ?? '#ffffff';
        $fieldText = $attrs['field_text_color'] ?? '#666666';
        $fieldBorder = $attrs['field_border_color'] ?? '#bbb';
        $fieldBorderWidth = $attrs['field_border_width'] ?? 1;
        $fieldBorderRadius = $attrs['field_border_radius'] ?? 0;

        $css .= $selector . ' .jtb-search-field { flex: 1; }' . "\n";

        $css .= $selector . ' .jtb-search-input { ';
        $css .= 'width: 100%; ';
        $css .= 'padding: 12px 15px; ';
        $css .= 'background-color: ' . $fieldBg . '; ';
        $css .= 'color: ' . $fieldText . '; ';
        $css .= 'border: ' . $fieldBorderWidth . 'px solid ' . $fieldBorder . '; ';
        $css .= 'border-radius: ' . $fieldBorderRadius . 'px; ';
        $css .= 'font-size: 14px; ';
        $css .= 'box-sizing: border-box; ';
        $css .= '}' . "\n";

        // Focus
        if (!empty($attrs['field_border_color__hover'])) {
            $css .= $selector . ' .jtb-search-input:focus { border-color: ' . $attrs['field_border_color__hover'] . '; outline: none; }' . "\n";
        }

        // Button
        $btnBg = $attrs['button_bg_color'] ?? '#2ea3f2';
        $btnText = $attrs['button_text_color'] ?? '#ffffff';

        $css .= $selector . ' .jtb-search-submit { ';
        $css .= 'background-color: ' . $btnBg . '; ';
        $css .= 'color: ' . $btnText . '; ';
        $css .= 'border: none; ';
        $css .= 'padding: 12px 20px; ';
        $css .= 'cursor: pointer; ';
        $css .= 'font-size: 14px; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= 'margin-left: -1px; ';
        $css .= '}' . "\n";

        if (!empty($attrs['button_bg_color__hover'])) {
            $css .= $selector . ' .jtb-search-submit:hover { background-color: ' . $attrs['button_bg_color__hover'] . '; }' . "\n";
        }

        // Icon styling
        $css .= $selector . ' .jtb-icon-search { display: inline-flex; align-items: center; }' . "\n";
        $css .= $selector . ' .jtb-icon-search svg { width: 16px; height: 16px; }' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('search', JTB_Module_Search::class);
