<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Circle Counter Module
 * Displays circular progress indicator with percentage
 */
class CircleModule extends Module
{
    public function __construct()
    {
        $this->name = 'Circle Counter';
        $this->slug = 'circle';
        $this->icon = 'circle-dot';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-circle-counter',
            'svg' => '.tb4-circle-svg',
            'track' => '.tb4-circle-track',
            'progress' => '.tb4-circle-progress',
            'number' => '.tb4-circle-number',
            'title' => '.tb4-circle-title'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'percent' => [
                'label' => 'Percentage',
                'type' => 'text',
                'default' => '75',
                'description' => 'Value from 0 to 100'
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Progress'
            ],
            'show_percent' => [
                'label' => 'Show Percent Sign',
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
            'circle_size' => [
                'label' => 'Circle Size',
                'type' => 'select',
                'options' => [
                    '100' => 'Small (100px)',
                    '150' => 'Medium (150px)',
                    '200' => 'Large (200px)',
                    '250' => 'Extra Large (250px)'
                ],
                'default' => '150'
            ],
            'stroke_width' => [
                'label' => 'Stroke Width',
                'type' => 'select',
                'options' => [
                    '8' => 'Thin',
                    '12' => 'Medium',
                    '16' => 'Thick',
                    '20' => 'Extra Thick'
                ],
                'default' => '12'
            ],
            'bar_color' => [
                'label' => 'Progress Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'track_color' => [
                'label' => 'Track Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'number_color' => [
                'label' => 'Number Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'number_font_size' => [
                'label' => 'Number Font Size',
                'type' => 'text',
                'default' => '32px'
            ],
            'number_font_weight' => [
                'label' => 'Number Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '700'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'text_align' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
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
        $percent = min(100, max(0, intval($settings['percent'] ?? 75)));
        $title = $settings['title'] ?? 'Progress';
        $showPercent = ($settings['show_percent'] ?? 'yes') === 'yes';

        // Design settings
        $size = intval($settings['circle_size'] ?? 150);
        $strokeWidth = intval($settings['stroke_width'] ?? 12);
        $barColor = $settings['bar_color'] ?? '#2563eb';
        $trackColor = $settings['track_color'] ?? '#e5e7eb';
        $numberColor = $settings['number_color'] ?? '#111827';
        $numberSize = $settings['number_font_size'] ?? '32px';
        $numberWeight = $settings['number_font_weight'] ?? '700';
        $titleColor = $settings['title_color'] ?? '#6b7280';
        $titleSize = $settings['title_font_size'] ?? '14px';
        $textAlign = $settings['text_align'] ?? 'center';

        // Calculate SVG circle parameters
        $radius = ($size - $strokeWidth) / 2;
        $circumference = 2 * M_PI * $radius;
        $offset = $circumference - ($percent / 100) * $circumference;
        $center = $size / 2;

        // Generate unique ID for this instance
        $uniqueId = 'tb4-circle-' . uniqid();

        // Build container styles
        $containerStyles = [
            'text-align:' . esc_attr($textAlign),
            'padding:20px'
        ];

        // Build number styles
        $numberStyles = [
            'position:absolute',
            'top:50%',
            'left:50%',
            'transform:translate(-50%,-50%)',
            'font-size:' . esc_attr($numberSize),
            'font-weight:' . esc_attr($numberWeight),
            'color:' . esc_attr($numberColor),
            'line-height:1'
        ];

        // Build title styles
        $titleStyles = [
            'font-size:' . esc_attr($titleSize),
            'color:' . esc_attr($titleColor),
            'margin-top:12px'
        ];

        // Build HTML
        $html = '<div class="tb4-circle-counter" id="' . esc_attr($uniqueId) . '" style="' . implode(';', $containerStyles) . '">';

        // SVG wrapper with relative positioning
        $html .= '<div style="position:relative;display:inline-block;">';

        // SVG element
        $html .= '<svg class="tb4-circle-svg" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" viewBox="0 0 ' . $size . ' ' . $size . '">';

        // Track circle (background)
        $html .= '<circle class="tb4-circle-track" cx="' . $center . '" cy="' . $center . '" r="' . $radius . '" fill="none" stroke="' . esc_attr($trackColor) . '" stroke-width="' . $strokeWidth . '"/>';

        // Progress circle
        $html .= '<circle class="tb4-circle-progress" cx="' . $center . '" cy="' . $center . '" r="' . $radius . '" fill="none" stroke="' . esc_attr($barColor) . '" stroke-width="' . $strokeWidth . '" stroke-linecap="round" stroke-dasharray="' . $circumference . '" stroke-dashoffset="' . $offset . '" transform="rotate(-90 ' . $center . ' ' . $center . ')" data-percent="' . $percent . '" data-circumference="' . $circumference . '"/>';

        $html .= '</svg>';

        // Number overlay
        $html .= '<div class="tb4-circle-number" style="' . implode(';', $numberStyles) . '">';
        $html .= esc_html($percent) . ($showPercent ? '%' : '');
        $html .= '</div>';

        $html .= '</div>'; // End SVG wrapper

        // Title
        if (!empty($title)) {
            $html .= '<div class="tb4-circle-title" style="' . implode(';', $titleStyles) . '">' . esc_html($title) . '</div>';
        }

        $html .= '</div>'; // End container

        // Add animation script (only once per page)
        $html .= $this->generate_animation_script();

        return $html;
    }

    /**
     * Generate animation script (only once)
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
    function animateCircle(el) {
        var percent = parseFloat(el.getAttribute("data-percent")) || 0;
        var circumference = parseFloat(el.getAttribute("data-circumference")) || 0;
        var targetOffset = circumference - (percent / 100) * circumference;
        var startOffset = circumference;
        var duration = 1500;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var currentOffset = startOffset - (startOffset - targetOffset) * eased;
            el.style.strokeDashoffset = currentOffset;

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        el.style.strokeDashoffset = circumference;
        requestAnimationFrame(step);
    }

    function initCircleCounters() {
        var circles = document.querySelectorAll(".tb4-circle-progress[data-percent]");
        if ("IntersectionObserver" in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && !entry.target.classList.contains("animated")) {
                        entry.target.classList.add("animated");
                        animateCircle(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            circles.forEach(function(circle) {
                observer.observe(circle);
            });
        } else {
            circles.forEach(function(circle) {
                animateCircle(circle);
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initCircleCounters);
    } else {
        initCircleCounters();
    }
})();
</script>';
    }
}
