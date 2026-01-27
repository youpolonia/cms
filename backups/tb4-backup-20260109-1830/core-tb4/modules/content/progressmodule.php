<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Progress Bar Module
 * Displays horizontal progress bar with label and percentage
 */
class ProgressModule extends Module
{
    public function __construct()
    {
        $this->name = 'Progress Bar';
        $this->slug = 'progress';
        $this->icon = 'minus';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-progress',
            'header' => '.tb4-progress-header',
            'label' => '.tb4-progress-label',
            'percent' => '.tb4-progress-percent',
            'track' => '.tb4-progress-track',
            'bar' => '.tb4-progress-bar'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'label' => [
                'label' => 'Label',
                'type' => 'text',
                'default' => 'Progress'
            ],
            'percent' => [
                'label' => 'Percentage',
                'type' => 'text',
                'default' => '75',
                'description' => 'Value from 0 to 100'
            ],
            'show_percent' => [
                'label' => 'Show Percentage',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'percent_position' => [
                'label' => 'Percent Position',
                'type' => 'select',
                'options' => [
                    'inside' => 'Inside Bar',
                    'right' => 'Right of Bar',
                    'top' => 'Top Right'
                ],
                'default' => 'right'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'bar_color' => [
                'label' => 'Bar Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'bar_gradient' => [
                'label' => 'Bar Style',
                'type' => 'select',
                'options' => [
                    'solid' => 'Solid',
                    'gradient' => 'Gradient',
                    'striped' => 'Striped'
                ],
                'default' => 'solid'
            ],
            'bar_gradient_end' => [
                'label' => 'Gradient End Color',
                'type' => 'color',
                'default' => '#7c3aed'
            ],
            'track_color' => [
                'label' => 'Track Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'bar_height' => [
                'label' => 'Bar Height',
                'type' => 'select',
                'options' => [
                    '4' => 'Thin (4px)',
                    '8' => 'Small (8px)',
                    '16' => 'Medium (16px)',
                    '24' => 'Large (24px)',
                    '32' => 'Extra Large (32px)'
                ],
                'default' => '16'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'select',
                'options' => [
                    '0' => 'Square',
                    '4' => 'Slightly Rounded',
                    '8' => 'Rounded',
                    '999' => 'Pill'
                ],
                'default' => '999'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'label_font_size' => [
                'label' => 'Label Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'label_font_weight' => [
                'label' => 'Label Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '500'
            ],
            'percent_color' => [
                'label' => 'Percent Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'percent_font_size' => [
                'label' => 'Percent Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'percent_font_weight' => [
                'label' => 'Percent Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '600'
            ],
            'spacing' => [
                'label' => 'Label-Bar Spacing',
                'type' => 'text',
                'default' => '8px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $settings): string
    {
        // Content settings
        $label = $settings['label'] ?? 'Progress';
        $percent = min(100, max(0, intval($settings['percent'] ?? 75)));
        $showPercent = ($settings['show_percent'] ?? 'yes') === 'yes';
        $percentPosition = $settings['percent_position'] ?? 'right';

        // Design settings
        $barColor = $settings['bar_color'] ?? '#2563eb';
        $barGradient = $settings['bar_gradient'] ?? 'solid';
        $barGradientEnd = $settings['bar_gradient_end'] ?? '#7c3aed';
        $trackColor = $settings['track_color'] ?? '#e5e7eb';
        $barHeight = ($settings['bar_height'] ?? '16') . 'px';
        $borderRadius = ($settings['border_radius'] ?? '999') . 'px';
        $labelColor = $settings['label_color'] ?? '#374151';
        $labelFontSize = $settings['label_font_size'] ?? '14px';
        $labelFontWeight = $settings['label_font_weight'] ?? '500';
        $percentColor = $settings['percent_color'] ?? '#374151';
        $percentFontSize = $settings['percent_font_size'] ?? '14px';
        $percentFontWeight = $settings['percent_font_weight'] ?? '600';
        $spacing = $settings['spacing'] ?? '8px';

        // Build bar background based on style
        $barBg = $barColor;
        if ($barGradient === 'gradient') {
            $barBg = 'linear-gradient(90deg, ' . esc_attr($barColor) . ', ' . esc_attr($barGradientEnd) . ')';
        }

        // Build striped style
        $stripedStyle = '';
        if ($barGradient === 'striped') {
            $stripedStyle = 'background-image:linear-gradient(45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-size:1rem 1rem;';
        }

        // Build label styles
        $labelStyles = [
            'font-size:' . esc_attr($labelFontSize),
            'font-weight:' . esc_attr($labelFontWeight),
            'color:' . esc_attr($labelColor)
        ];

        // Build percent styles
        $percentStyles = [
            'font-size:' . esc_attr($percentFontSize),
            'font-weight:' . esc_attr($percentFontWeight),
            'color:' . esc_attr($percentColor)
        ];

        // Generate unique ID
        $uniqueId = 'tb4-progress-' . uniqid();

        // Build HTML
        $html = '<div class="tb4-progress" id="' . esc_attr($uniqueId) . '">';

        // Top header (label + percent if position is "top")
        if ($percentPosition === 'top') {
            $html .= '<div class="tb4-progress-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:' . esc_attr($spacing) . ';">';
            $html .= '<span class="tb4-progress-label" style="' . implode(';', $labelStyles) . ';">' . esc_html($label) . '</span>';
            if ($showPercent) {
                $html .= '<span class="tb4-progress-percent" style="' . implode(';', $percentStyles) . ';">' . $percent . '%</span>';
            }
            $html .= '</div>';
        } else {
            // Just label above bar
            $html .= '<div class="tb4-progress-header" style="margin-bottom:' . esc_attr($spacing) . ';">';
            $html .= '<span class="tb4-progress-label" style="' . implode(';', $labelStyles) . ';">' . esc_html($label) . '</span>';
            $html .= '</div>';
        }

        // Progress bar container (with percent on right if position is "right")
        if ($percentPosition === 'right') {
            $html .= '<div class="tb4-progress-wrapper" style="display:flex;align-items:center;gap:12px;">';
        }

        // Track with bar
        $html .= '<div class="tb4-progress-track" style="background:' . esc_attr($trackColor) . ';height:' . esc_attr($barHeight) . ';border-radius:' . esc_attr($borderRadius) . ';overflow:hidden;flex-grow:1;position:relative;">';

        // Bar
        $barStyles = [
            'background:' . $barBg,
            'width:' . $percent . '%',
            'height:100%',
            'border-radius:' . esc_attr($borderRadius),
            'transition:width 0.5s ease'
        ];
        if (!empty($stripedStyle)) {
            $barStyles[] = $stripedStyle;
        }

        $html .= '<div class="tb4-progress-bar" style="' . implode(';', $barStyles) . '">';

        // Percent inside bar
        if ($showPercent && $percentPosition === 'inside') {
            $insidePercentStyles = $percentStyles;
            $insidePercentStyles[] = 'position:absolute';
            $insidePercentStyles[] = 'left:50%';
            $insidePercentStyles[] = 'top:50%';
            $insidePercentStyles[] = 'transform:translate(-50%,-50%)';
            $insidePercentStyles[] = 'color:#fff';
            $insidePercentStyles[] = 'text-shadow:0 1px 2px rgba(0,0,0,0.3)';
            $html .= '<span class="tb4-progress-percent tb4-progress-percent-inside" style="' . implode(';', $insidePercentStyles) . ';">' . $percent . '%</span>';
        }

        $html .= '</div>'; // End bar
        $html .= '</div>'; // End track

        // Percent on right
        if ($showPercent && $percentPosition === 'right') {
            $html .= '<span class="tb4-progress-percent" style="' . implode(';', $percentStyles) . ';flex-shrink:0;">' . $percent . '%</span>';
        }

        if ($percentPosition === 'right') {
            $html .= '</div>'; // End wrapper
        }

        $html .= '</div>'; // End main container

        return $html;
    }
}
