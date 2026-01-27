<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Bar Counter Module
 * Displays multiple animated progress bars with labels and percentages
 */
class BarCounterModule extends Module
{
    public function __construct()
    {
        $this->name = 'Bar Counter';
        $this->slug = 'bar_counter';
        $this->icon = 'bar-chart-2';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-bar-counter',
            'item' => '.tb4-bar-item',
            'header' => '.tb4-bar-header',
            'label' => '.tb4-bar-label',
            'percent' => '.tb4-bar-percent',
            'track' => '.tb4-bar-track',
            'fill' => '.tb4-bar-fill'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'bar1_label' => [
                'label' => 'Bar 1 Label',
                'type' => 'text',
                'default' => 'Web Design'
            ],
            'bar1_percent' => [
                'label' => 'Bar 1 Percent',
                'type' => 'text',
                'default' => '90',
                'description' => 'Value from 0 to 100'
            ],
            'bar2_label' => [
                'label' => 'Bar 2 Label',
                'type' => 'text',
                'default' => 'Development'
            ],
            'bar2_percent' => [
                'label' => 'Bar 2 Percent',
                'type' => 'text',
                'default' => '85'
            ],
            'bar3_label' => [
                'label' => 'Bar 3 Label',
                'type' => 'text',
                'default' => 'SEO'
            ],
            'bar3_percent' => [
                'label' => 'Bar 3 Percent',
                'type' => 'text',
                'default' => '75'
            ],
            'bar4_label' => [
                'label' => 'Bar 4 Label',
                'type' => 'text',
                'default' => 'Marketing'
            ],
            'bar4_percent' => [
                'label' => 'Bar 4 Percent',
                'type' => 'text',
                'default' => '80'
            ],
            'bar5_label' => [
                'label' => 'Bar 5 Label',
                'type' => 'text',
                'default' => ''
            ],
            'bar5_percent' => [
                'label' => 'Bar 5 Percent',
                'type' => 'text',
                'default' => ''
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
                    'outside' => 'After Label',
                    'end' => 'End of Bar'
                ],
                'default' => 'inside'
            ],
            'animate' => [
                'label' => 'Animate on Load',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'bar_height' => [
                'label' => 'Bar Height',
                'type' => 'text',
                'default' => '24px'
            ],
            'bar_gap' => [
                'label' => 'Gap Between Bars',
                'type' => 'text',
                'default' => '20px'
            ],
            'bar_color' => [
                'label' => 'Bar Fill Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'bar_bg_color' => [
                'label' => 'Bar Background',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'bar_border_radius' => [
                'label' => 'Bar Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'use_gradient' => [
                'label' => 'Use Gradient',
                'type' => 'select',
                'options' => [
                    'no' => 'No',
                    'yes' => 'Yes'
                ],
                'default' => 'no'
            ],
            'gradient_color' => [
                'label' => 'Gradient End Color',
                'type' => 'color',
                'default' => '#10b981'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#111827'
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
                'default' => '#ffffff'
            ],
            'percent_font_size' => [
                'label' => 'Percent Font Size',
                'type' => 'text',
                'default' => '12px'
            ],
            'striped' => [
                'label' => 'Striped Effect',
                'type' => 'select',
                'options' => [
                    'no' => 'No',
                    'yes' => 'Yes'
                ],
                'default' => 'no'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $settings): string
    {
        // Collect bars with data
        $bars = [];
        for ($i = 1; $i <= 5; $i++) {
            $label = trim($settings['bar' . $i . '_label'] ?? '');
            $percent = $settings['bar' . $i . '_percent'] ?? '';
            if ($label !== '') {
                $bars[] = [
                    'label' => $label,
                    'percent' => min(100, max(0, intval($percent)))
                ];
            }
        }

        // Default bars if none specified
        if (empty($bars)) {
            $bars = [
                ['label' => 'Web Design', 'percent' => 90],
                ['label' => 'Development', 'percent' => 85],
                ['label' => 'SEO', 'percent' => 75],
                ['label' => 'Marketing', 'percent' => 80]
            ];
        }

        // Content settings
        $showPercent = ($settings['show_percent'] ?? 'yes') === 'yes';
        $percentPosition = $settings['percent_position'] ?? 'inside';
        $animate = ($settings['animate'] ?? 'yes') === 'yes';

        // Design settings
        $barHeight = $settings['bar_height'] ?? '24px';
        $barGap = $settings['bar_gap'] ?? '20px';
        $barColor = $settings['bar_color'] ?? '#2563eb';
        $barBgColor = $settings['bar_bg_color'] ?? '#e5e7eb';
        $barBorderRadius = $settings['bar_border_radius'] ?? '12px';
        $useGradient = ($settings['use_gradient'] ?? 'no') === 'yes';
        $gradientColor = $settings['gradient_color'] ?? '#10b981';
        $labelColor = $settings['label_color'] ?? '#111827';
        $labelFontSize = $settings['label_font_size'] ?? '14px';
        $labelFontWeight = $settings['label_font_weight'] ?? '500';
        $percentColor = $settings['percent_color'] ?? '#ffffff';
        $percentFontSize = $settings['percent_font_size'] ?? '12px';
        $striped = ($settings['striped'] ?? 'no') === 'yes';

        // Build bar fill background
        $barFillBg = $barColor;
        if ($useGradient) {
            $barFillBg = 'linear-gradient(90deg, ' . esc_attr($barColor) . ' 0%, ' . esc_attr($gradientColor) . ' 100%)';
        }

        // Build striped style
        $stripedStyle = '';
        if ($striped) {
            $stripedStyle = 'background-image:linear-gradient(45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-size:1rem 1rem;';
        }

        // Generate unique ID
        $uniqueId = 'tb4-bar-counter-' . uniqid();

        // Label styles
        $labelStyles = [
            'font-size:' . esc_attr($labelFontSize),
            'font-weight:' . esc_attr($labelFontWeight),
            'color:' . esc_attr($labelColor)
        ];

        // Build HTML
        $html = '<div class="tb4-bar-counter" id="' . esc_attr($uniqueId) . '" style="display:flex;flex-direction:column;gap:' . esc_attr($barGap) . ';">';

        foreach ($bars as $index => $bar) {
            $percentVal = $bar['percent'];
            $initialWidth = $animate ? '0' : $percentVal;

            $html .= '<div class="tb4-bar-item">';

            // Header with label and optional outside/end percent
            $html .= '<div class="tb4-bar-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">';
            $html .= '<span class="tb4-bar-label" style="' . implode(';', $labelStyles) . ';">' . esc_html($bar['label']);
            if ($showPercent && $percentPosition === 'outside') {
                $html .= ' <span style="color:' . esc_attr($labelColor) . ';">(' . $percentVal . '%)</span>';
            }
            $html .= '</span>';
            if ($showPercent && $percentPosition === 'end') {
                $html .= '<span style="font-size:' . esc_attr($labelFontSize) . ';font-weight:600;color:' . esc_attr($labelColor) . ';">' . $percentVal . '%</span>';
            }
            $html .= '</div>';

            // Bar track
            $html .= '<div class="tb4-bar-track" style="position:relative;width:100%;height:' . esc_attr($barHeight) . ';background:' . esc_attr($barBgColor) . ';border-radius:' . esc_attr($barBorderRadius) . ';overflow:hidden;">';

            // Bar fill
            $fillStyles = [
                'width:' . $initialWidth . '%',
                'height:100%',
                'background:' . $barFillBg,
                'border-radius:inherit',
                'display:flex',
                'align-items:center',
                'justify-content:flex-end',
                'padding-right:10px',
                'transition:width 1s ease-out'
            ];
            if (!empty($stripedStyle)) {
                $fillStyles[] = $stripedStyle;
            }

            $html .= '<div class="tb4-bar-fill" data-percent="' . $percentVal . '" style="' . implode(';', $fillStyles) . '">';

            // Percent inside bar (only show if bar is wide enough)
            if ($showPercent && $percentPosition === 'inside' && $percentVal >= 15) {
                $html .= '<span class="tb4-bar-percent-inside" style="font-size:' . esc_attr($percentFontSize) . ';font-weight:600;color:' . esc_attr($percentColor) . ';">' . $percentVal . '%</span>';
            }

            $html .= '</div>'; // End fill
            $html .= '</div>'; // End track
            $html .= '</div>'; // End item
        }

        $html .= '</div>'; // End main container

        // Add animation script if enabled
        if ($animate) {
            $html .= $this->generate_animation_script();
        }

        return $html;
    }

    /**
     * Generate animation script (only once per page)
     */
    private static bool $scriptIncluded = false;

    private function generate_animation_script(): string
    {
        if (self::$scriptIncluded) {
            return '';
        }
        self::$scriptIncluded = true;

        return '<script>
(function() {
    function animateBarCounters() {
        var bars = document.querySelectorAll(".tb4-bar-counter .tb4-bar-fill[data-percent]");
        if ("IntersectionObserver" in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && !entry.target.classList.contains("animated")) {
                        entry.target.classList.add("animated");
                        var percent = entry.target.getAttribute("data-percent") || 0;
                        entry.target.style.width = percent + "%";
                    }
                });
            }, { threshold: 0.2 });

            bars.forEach(function(bar) {
                observer.observe(bar);
            });
        } else {
            bars.forEach(function(bar) {
                var percent = bar.getAttribute("data-percent") || 0;
                bar.style.width = percent + "%";
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", animateBarCounters);
    } else {
        animateBarCounters();
    }
})();
</script>';
    }
}
