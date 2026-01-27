<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Number Counter Module
 * Displays animated number counters that count up from 0 to target number
 */
class NumberModule extends Module
{
    public function __construct()
    {
        $this->name = 'Number Counter';
        $this->slug = 'number';
        $this->icon = 'hash';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-number-counter',
            'value' => '.tb4-number-value',
            'prefix' => '.tb4-number-prefix',
            'number' => '.tb4-number-num',
            'suffix' => '.tb4-number-suffix',
            'title' => '.tb4-number-title'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'number' => [
                'label' => 'Number',
                'type' => 'text',
                'default' => '100'
            ],
            'prefix' => [
                'label' => 'Prefix',
                'type' => 'text',
                'default' => '',
                'description' => 'Text before number (e.g. $)'
            ],
            'suffix' => [
                'label' => 'Suffix',
                'type' => 'text',
                'default' => '',
                'description' => 'Text after number (e.g. %, +, k)'
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Happy Clients'
            ],
            'separator' => [
                'label' => 'Thousands Separator',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'comma' => 'Comma (1,000)',
                    'space' => 'Space (1 000)',
                    'dot' => 'Dot (1.000)'
                ],
                'default' => 'comma'
            ],
            'animation_duration' => [
                'label' => 'Animation Duration',
                'type' => 'select',
                'options' => [
                    '1000' => '1 second',
                    '2000' => '2 seconds',
                    '3000' => '3 seconds'
                ],
                'default' => '2000'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'number_color' => [
                'label' => 'Number Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'number_font_size' => [
                'label' => 'Number Font Size',
                'type' => 'text',
                'default' => '48px'
            ],
            'number_font_weight' => [
                'label' => 'Number Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
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
                'default' => '16px'
            ],
            'title_font_weight' => [
                'label' => 'Title Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '500'
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
            ],
            'prefix_suffix_color' => [
                'label' => 'Prefix/Suffix Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'prefix_suffix_size' => [
                'label' => 'Prefix/Suffix Size',
                'type' => 'text',
                'default' => '36px'
            ],
            'spacing' => [
                'label' => 'Spacing Between Number & Title',
                'type' => 'text',
                'default' => '8px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Format number with thousands separator
     */
    private function format_number(string $number, string $separator): string
    {
        // Extract just the numeric part
        $numericValue = preg_replace('/[^0-9.]/', '', $number);

        if (!is_numeric($numericValue)) {
            return $number;
        }

        $floatVal = (float) $numericValue;
        $hasDecimals = strpos($numericValue, '.') !== false;

        switch ($separator) {
            case 'comma':
                return $hasDecimals
                    ? number_format($floatVal, 2, '.', ',')
                    : number_format($floatVal, 0, '.', ',');
            case 'space':
                return $hasDecimals
                    ? number_format($floatVal, 2, '.', ' ')
                    : number_format($floatVal, 0, '.', ' ');
            case 'dot':
                return $hasDecimals
                    ? number_format($floatVal, 2, ',', '.')
                    : number_format($floatVal, 0, ',', '.');
            case 'none':
            default:
                return $numericValue;
        }
    }

    public function render(array $settings): string
    {
        $number = $settings['number'] ?? '100';
        $prefix = $settings['prefix'] ?? '';
        $suffix = $settings['suffix'] ?? '';
        $title = $settings['title'] ?? 'Happy Clients';
        $separator = $settings['separator'] ?? 'comma';
        $duration = $settings['animation_duration'] ?? '2000';

        // Design settings
        $numberColor = $settings['number_color'] ?? '#2563eb';
        $numberSize = $settings['number_font_size'] ?? '48px';
        $numberWeight = $settings['number_font_weight'] ?? '700';
        $titleColor = $settings['title_color'] ?? '#6b7280';
        $titleSize = $settings['title_font_size'] ?? '16px';
        $titleWeight = $settings['title_font_weight'] ?? '500';
        $textAlign = $settings['text_align'] ?? 'center';
        $prefixSuffixColor = $settings['prefix_suffix_color'] ?? '#2563eb';
        $prefixSuffixSize = $settings['prefix_suffix_size'] ?? '36px';
        $spacing = $settings['spacing'] ?? '8px';

        // Format the display number
        $displayNumber = $this->format_number($number, $separator);

        // Generate unique ID for this instance
        $uniqueId = 'tb4-number-' . uniqid();

        // Build container styles
        $containerStyles = [
            'text-align:' . esc_attr($textAlign)
        ];

        // Build number value styles
        $valueStyles = [
            'font-size:' . esc_attr($numberSize),
            'font-weight:' . esc_attr($numberWeight),
            'color:' . esc_attr($numberColor),
            'line-height:1.2'
        ];

        // Build prefix/suffix styles
        $affixStyles = [
            'font-size:' . esc_attr($prefixSuffixSize),
            'color:' . esc_attr($prefixSuffixColor)
        ];

        // Build title styles
        $titleStyles = [
            'font-size:' . esc_attr($titleSize),
            'font-weight:' . esc_attr($titleWeight),
            'color:' . esc_attr($titleColor),
            'margin-top:' . esc_attr($spacing)
        ];

        // Build HTML
        $html = '<div class="tb4-number-counter" id="' . esc_attr($uniqueId) . '" style="' . implode(';', $containerStyles) . '">';

        // Number value container
        $html .= '<div class="tb4-number-value" style="' . implode(';', $valueStyles) . '">';

        // Prefix
        if (!empty($prefix)) {
            $html .= '<span class="tb4-number-prefix" style="' . implode(';', $affixStyles) . '">' . esc_html($prefix) . '</span>';
        }

        // Number with data attributes for animation
        $html .= '<span class="tb4-number-num" data-target="' . esc_attr($number) . '" data-separator="' . esc_attr($separator) . '" data-duration="' . esc_attr($duration) . '">';
        $html .= esc_html($displayNumber);
        $html .= '</span>';

        // Suffix
        if (!empty($suffix)) {
            $html .= '<span class="tb4-number-suffix" style="' . implode(';', $affixStyles) . '">' . esc_html($suffix) . '</span>';
        }

        $html .= '</div>'; // End number value

        // Title
        if (!empty($title)) {
            $html .= '<div class="tb4-number-title" style="' . implode(';', $titleStyles) . '">' . esc_html($title) . '</div>';
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
    function formatNumber(num, separator) {
        var str = Math.floor(num).toString();
        switch (separator) {
            case "comma":
                return str.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            case "space":
                return str.replace(/\B(?=(\d{3})+(?!\d))/g, " ");
            case "dot":
                return str.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            default:
                return str;
        }
    }

    function animateNumber(el) {
        var target = parseFloat(el.getAttribute("data-target")) || 0;
        var separator = el.getAttribute("data-separator") || "comma";
        var duration = parseInt(el.getAttribute("data-duration")) || 2000;
        var startTime = null;
        var startValue = 0;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = startValue + (target - startValue) * eased;
            el.textContent = formatNumber(current, separator);

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = formatNumber(target, separator);
            }
        }

        requestAnimationFrame(step);
    }

    function initNumberCounters() {
        var counters = document.querySelectorAll(".tb4-number-num[data-target]");
        if ("IntersectionObserver" in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && !entry.target.classList.contains("animated")) {
                        entry.target.classList.add("animated");
                        animateNumber(entry.target);
                    }
                });
            }, { threshold: 0.2 });

            counters.forEach(function(counter) {
                observer.observe(counter);
            });
        } else {
            counters.forEach(function(counter) {
                animateNumber(counter);
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initNumberCounters);
    } else {
        initNumberCounters();
    }
})();
</script>';
    }
}
