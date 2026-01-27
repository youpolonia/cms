<?php
/**
 * Text Module
 * Rich text content block
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Text extends JTB_Element
{
    public string $icon = 'text';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'text';
    }

    public function getName(): string
    {
        return 'Text';
    }

    public function getFields(): array
    {
        return [
            'content' => [
                'label' => 'Content',
                'type' => 'richtext',
                'default' => '<p>Enter your text here...</p>'
            ],
            'text_orientation' => [
                'label' => 'Text Orientation',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justify' => 'Justify'
                ],
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $textContent = $attrs['content'] ?? '<p>Enter your text here...</p>';

        $innerHtml = '<div class="jtb-text-inner">' . $textContent . '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Text orientation
        if (!empty($attrs['text_orientation'])) {
            $css .= $selector . ' .jtb-text-inner { text-align: ' . $attrs['text_orientation'] . '; }' . "\n";
        }

        // Responsive text orientation
        if (!empty($attrs['text_orientation__tablet'])) {
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-text-inner { text-align: ' . $attrs['text_orientation__tablet'] . '; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['text_orientation__phone'])) {
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-text-inner { text-align: ' . $attrs['text_orientation__phone'] . '; }' . "\n";
            $css .= '}' . "\n";
        }

        // Parent CSS
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

// Register module
JTB_Registry::register('text', JTB_Module_Text::class);
