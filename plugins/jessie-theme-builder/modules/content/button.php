<?php
/**
 * Button Module
 * Customizable button with multiple styles
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Button extends JTB_Element
{
    public string $icon = 'button';
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
        return 'button';
    }

    public function getName(): string
    {
        return 'Button';
    }

    public function getFields(): array
    {
        return [
            'text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Click Here'
            ],
            'link_url' => [
                'label' => 'Link URL',
                'type' => 'url',
                'default' => '#'
            ],
            'link_target' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'align' => [
                'label' => 'Button Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justify' => 'Full Width'
                ],
                'responsive' => true
            ],
            'icon' => [
                'label' => 'Icon',
                'type' => 'icon',
                'description' => 'Optional icon for the button'
            ],
            'icon_position' => [
                'label' => 'Icon Position',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'right' => 'Right'
                ],
                'default' => 'left',
                'show_if_not' => ['icon' => '']
            ],
            'button_style' => [
                'label' => 'Button Style',
                'type' => 'select',
                'options' => [
                    'solid' => 'Solid',
                    'outline' => 'Outline',
                    'ghost' => 'Ghost'
                ],
                'default' => 'solid'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $text = $attrs['text'] ?? 'Click Here';
        $linkUrl = $attrs['link_url'] ?? '#';
        $linkTarget = !empty($attrs['link_target']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $icon = $attrs['icon'] ?? '';
        $iconPosition = $attrs['icon_position'] ?? 'left';
        $buttonStyle = $attrs['button_style'] ?? 'solid';

        // Button classes
        $buttonClasses = ['jtb-button', 'jtb-button-' . $this->esc($buttonStyle)];

        // Build button content
        $buttonContent = '';

        if (!empty($icon) && $iconPosition === 'left') {
            $buttonContent .= '<span class="jtb-button-icon jtb-button-icon-left ' . $this->esc($icon) . '"></span>';
        }

        $buttonContent .= '<span class="jtb-button-text">' . $this->esc($text) . '</span>';

        if (!empty($icon) && $iconPosition === 'right') {
            $buttonContent .= '<span class="jtb-button-icon jtb-button-icon-right ' . $this->esc($icon) . '"></span>';
        }

        $buttonHtml = '<a href="' . $this->esc($linkUrl) . '"' . $linkTarget . ' class="' . implode(' ', $buttonClasses) . '">' . $buttonContent . '</a>';

        $innerHtml = '<div class="jtb-button-wrapper">' . $buttonHtml . '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Alignment
        $align = $attrs['align'] ?? 'left';
        if ($align === 'center') {
            $css .= $selector . ' .jtb-button-wrapper { text-align: center; }' . "\n";
        } elseif ($align === 'right') {
            $css .= $selector . ' .jtb-button-wrapper { text-align: right; }' . "\n";
        } elseif ($align === 'justify') {
            $css .= $selector . ' .jtb-button-wrapper { display: block; }' . "\n";
            $css .= $selector . ' .jtb-button { display: block; width: 100%; text-align: center; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-button-wrapper { text-align: left; }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['align__tablet'])) {
            $alignTablet = $attrs['align__tablet'];
            $css .= '@media (max-width: 980px) {' . "\n";
            if ($alignTablet === 'justify') {
                $css .= '  ' . $selector . ' .jtb-button-wrapper { display: block; }' . "\n";
                $css .= '  ' . $selector . ' .jtb-button { display: block; width: 100%; text-align: center; }' . "\n";
            } else {
                $textAlign = ($alignTablet === 'center') ? 'center' : (($alignTablet === 'right') ? 'right' : 'left');
                $css .= '  ' . $selector . ' .jtb-button-wrapper { text-align: ' . $textAlign . '; }' . "\n";
                $css .= '  ' . $selector . ' .jtb-button { display: inline-flex; width: auto; }' . "\n";
            }
            $css .= '}' . "\n";
        }

        if (!empty($attrs['align__phone'])) {
            $alignPhone = $attrs['align__phone'];
            $css .= '@media (max-width: 767px) {' . "\n";
            if ($alignPhone === 'justify') {
                $css .= '  ' . $selector . ' .jtb-button-wrapper { display: block; }' . "\n";
                $css .= '  ' . $selector . ' .jtb-button { display: block; width: 100%; text-align: center; }' . "\n";
            } else {
                $textAlign = ($alignPhone === 'center') ? 'center' : (($alignPhone === 'right') ? 'right' : 'left');
                $css .= '  ' . $selector . ' .jtb-button-wrapper { text-align: ' . $textAlign . '; }' . "\n";
                $css .= '  ' . $selector . ' .jtb-button { display: inline-flex; width: auto; }' . "\n";
            }
            $css .= '}' . "\n";
        }

        // Apply design options to button element
        $buttonSelector = $selector . ' .jtb-button';

        // Typography for button
        if ($this->use_typography) {
            $css .= $this->generateButtonTypographyCss($attrs, $buttonSelector);
        }

        // Background for button
        $css .= $this->generateButtonBackgroundCss($attrs, $buttonSelector);

        // Border for button
        $css .= $this->generateButtonBorderCss($attrs, $buttonSelector);

        // Box shadow for button
        $css .= $this->generateButtonBoxShadowCss($attrs, $buttonSelector);

        // Parent CSS (for the wrapper)
        $css .= parent::generateSpacingCss($attrs, $selector);
        $css .= parent::generateTransformCss($attrs, $selector);

        return $css;
    }

    protected function generateButtonTypographyCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        if (!empty($attrs['font_family'])) {
            $rules[] = "font-family: '{$attrs['font_family']}', sans-serif";
        }

        if (!empty($attrs['font_size'])) {
            $rules[] = 'font-size: ' . $attrs['font_size'] . 'px';
        }

        if (!empty($attrs['font_weight'])) {
            $rules[] = 'font-weight: ' . $attrs['font_weight'];
        }

        if (!empty($attrs['letter_spacing'])) {
            $rules[] = 'letter-spacing: ' . $attrs['letter_spacing'] . 'px';
        }

        if (!empty($attrs['text_transform'])) {
            $rules[] = 'text-transform: ' . $attrs['text_transform'];
        }

        if (!empty($attrs['text_color'])) {
            $rules[] = 'color: ' . $attrs['text_color'];
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        // Hover state
        if (!empty($attrs['text_color__hover'])) {
            $css .= $selector . ':hover { color: ' . $attrs['text_color__hover'] . '; }' . "\n";
        }

        return $css;
    }

    protected function generateButtonBackgroundCss(array $attrs, string $selector): string
    {
        $css = '';
        $bgType = $attrs['background_type'] ?? 'none';

        if ($bgType === 'color' && !empty($attrs['background_color'])) {
            $css .= $selector . ' { background-color: ' . $attrs['background_color'] . '; }' . "\n";

            if (!empty($attrs['background_color__hover'])) {
                $css .= $selector . ':hover { background-color: ' . $attrs['background_color__hover'] . '; }' . "\n";
            }
        } elseif ($bgType === 'gradient') {
            $start = $attrs['background_gradient_start'] ?? '#ffffff';
            $end = $attrs['background_gradient_end'] ?? '#000000';
            $type = $attrs['background_gradient_type'] ?? 'linear';
            $direction = $attrs['background_gradient_direction'] ?? 180;

            if ($type === 'linear') {
                $css .= $selector . " { background: linear-gradient({$direction}deg, {$start}, {$end}); }\n";
            } else {
                $css .= $selector . " { background: radial-gradient(circle, {$start}, {$end}); }\n";
            }
        }

        return $css;
    }

    protected function generateButtonBorderCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        if (!empty($attrs['border_width'])) {
            $width = $attrs['border_width'];
            if (is_array($width)) {
                $rules[] = 'border-width: ' . ($width['top'] ?? 0) . 'px ' . ($width['right'] ?? 0) . 'px ' . ($width['bottom'] ?? 0) . 'px ' . ($width['left'] ?? 0) . 'px';
            }
        }

        if (!empty($attrs['border_style']) && $attrs['border_style'] !== 'none') {
            $rules[] = 'border-style: ' . $attrs['border_style'];
        }

        if (!empty($attrs['border_color'])) {
            $rules[] = 'border-color: ' . $attrs['border_color'];
        }

        if (!empty($attrs['border_radius'])) {
            $radius = $attrs['border_radius'];
            if (is_array($radius)) {
                $rules[] = 'border-radius: ' . ($radius['top_left'] ?? 0) . 'px ' . ($radius['top_right'] ?? 0) . 'px ' . ($radius['bottom_right'] ?? 0) . 'px ' . ($radius['bottom_left'] ?? 0) . 'px';
            }
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        if (!empty($attrs['border_color__hover'])) {
            $css .= $selector . ':hover { border-color: ' . $attrs['border_color__hover'] . '; }' . "\n";
        }

        return $css;
    }

    protected function generateButtonBoxShadowCss(array $attrs, string $selector): string
    {
        $css = '';
        $style = $attrs['box_shadow_style'] ?? 'none';

        if ($style === 'none') {
            return $css;
        }

        $shadow = '';

        if ($style === 'preset1') {
            $shadow = '0 2px 4px rgba(0,0,0,0.1)';
        } elseif ($style === 'preset2') {
            $shadow = '0 4px 12px rgba(0,0,0,0.15)';
        } elseif ($style === 'preset3') {
            $shadow = '0 8px 24px rgba(0,0,0,0.2)';
        } elseif ($style === 'custom') {
            $h = $attrs['box_shadow_horizontal'] ?? 0;
            $v = $attrs['box_shadow_vertical'] ?? 0;
            $blur = $attrs['box_shadow_blur'] ?? 0;
            $spread = $attrs['box_shadow_spread'] ?? 0;
            $color = $attrs['box_shadow_color'] ?? 'rgba(0,0,0,0.3)';
            $shadow = "{$h}px {$v}px {$blur}px {$spread}px {$color}";
        }

        if (!empty($shadow)) {
            $css .= $selector . ' { box-shadow: ' . $shadow . '; }' . "\n";
        }

        return $css;
    }
}

// Register module
JTB_Registry::register('button', JTB_Module_Button::class);
