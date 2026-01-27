<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Countdown Timer Module
 * Displays countdown timer showing days, hours, minutes, seconds until target date
 */
class CountdownModule extends Module
{
    public function __construct()
    {
        $this->name = 'Countdown Timer';
        $this->slug = 'countdown';
        $this->icon = 'timer';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-countdown',
            'unit' => '.tb4-countdown-unit',
            'number' => '.tb4-countdown-number',
            'label' => '.tb4-countdown-label',
            'separator' => '.tb4-countdown-separator',
            'title' => '.tb4-countdown-title',
            'expired' => '.tb4-countdown-expired'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'target_date' => [
                'label' => 'Target Date',
                'type' => 'text',
                'default' => '2026-12-31',
                'description' => 'Format: YYYY-MM-DD'
            ],
            'target_time' => [
                'label' => 'Target Time',
                'type' => 'text',
                'default' => '00:00',
                'description' => 'Format: HH:MM (24h)'
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => ''
            ],
            'show_days' => [
                'label' => 'Show Days',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_hours' => [
                'label' => 'Show Hours',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_minutes' => [
                'label' => 'Show Minutes',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_seconds' => [
                'label' => 'Show Seconds',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'label_days' => [
                'label' => 'Days Label',
                'type' => 'text',
                'default' => 'Days'
            ],
            'label_hours' => [
                'label' => 'Hours Label',
                'type' => 'text',
                'default' => 'Hours'
            ],
            'label_minutes' => [
                'label' => 'Minutes Label',
                'type' => 'text',
                'default' => 'Minutes'
            ],
            'label_seconds' => [
                'label' => 'Seconds Label',
                'type' => 'text',
                'default' => 'Seconds'
            ],
            'expired_message' => [
                'label' => 'Expired Message',
                'type' => 'text',
                'default' => 'Event has started!'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'inline' => 'Inline',
                    'boxed' => 'Boxed',
                    'circle' => 'Circle'
                ],
                'default' => 'boxed'
            ],
            'number_color' => [
                'label' => 'Number Color',
                'type' => 'color',
                'default' => '#111827'
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
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
                ],
                'default' => '700'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'label_font_size' => [
                'label' => 'Label Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'box_background' => [
                'label' => 'Box Background',
                'type' => 'color',
                'default' => '#f3f4f6'
            ],
            'box_border_radius' => [
                'label' => 'Box Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'box_padding' => [
                'label' => 'Box Padding',
                'type' => 'text',
                'default' => '20px'
            ],
            'separator_color' => [
                'label' => 'Separator Color',
                'type' => 'color',
                'default' => '#d1d5db'
            ],
            'separator_style' => [
                'label' => 'Separator',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'colon' => 'Colon (:)',
                    'line' => 'Line (|)'
                ],
                'default' => 'colon'
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
            'gap' => [
                'label' => 'Gap Between Units',
                'type' => 'text',
                'default' => '16px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $settings): string
    {
        $targetDate = $settings['target_date'] ?? '2026-12-31';
        $targetTime = $settings['target_time'] ?? '00:00';
        $title = $settings['title'] ?? '';
        $showDays = ($settings['show_days'] ?? 'yes') === 'yes';
        $showHours = ($settings['show_hours'] ?? 'yes') === 'yes';
        $showMinutes = ($settings['show_minutes'] ?? 'yes') === 'yes';
        $showSeconds = ($settings['show_seconds'] ?? 'yes') === 'yes';
        $labelDays = $settings['label_days'] ?? 'Days';
        $labelHours = $settings['label_hours'] ?? 'Hours';
        $labelMinutes = $settings['label_minutes'] ?? 'Minutes';
        $labelSeconds = $settings['label_seconds'] ?? 'Seconds';
        $expiredMessage = $settings['expired_message'] ?? 'Event has started!';

        // Design settings
        $layout = $settings['layout'] ?? 'boxed';
        $numberColor = $settings['number_color'] ?? '#111827';
        $numberSize = $settings['number_font_size'] ?? '48px';
        $numberWeight = $settings['number_font_weight'] ?? '700';
        $labelColor = $settings['label_color'] ?? '#6b7280';
        $labelSize = $settings['label_font_size'] ?? '14px';
        $boxBackground = $settings['box_background'] ?? '#f3f4f6';
        $boxRadius = $settings['box_border_radius'] ?? '8px';
        $boxPadding = $settings['box_padding'] ?? '20px';
        $separatorColor = $settings['separator_color'] ?? '#d1d5db';
        $separatorStyle = $settings['separator_style'] ?? 'colon';
        $textAlign = $settings['text_align'] ?? 'center';
        $gap = $settings['gap'] ?? '16px';

        // Determine separator character
        $separatorChar = '';
        if ($separatorStyle === 'colon') {
            $separatorChar = ':';
        } elseif ($separatorStyle === 'line') {
            $separatorChar = '|';
        }

        // Generate unique ID
        $uniqueId = 'tb4-countdown-' . uniqid();

        // Build target datetime string
        $targetDateTime = esc_attr($targetDate) . 'T' . esc_attr($targetTime) . ':00';

        // Container styles
        $containerStyles = [
            'text-align:' . esc_attr($textAlign)
        ];

        // Units container styles
        $unitsContainerStyles = [
            'display:flex',
            'justify-content:' . ($textAlign === 'left' ? 'flex-start' : ($textAlign === 'right' ? 'flex-end' : 'center')),
            'align-items:center',
            'gap:' . esc_attr($gap),
            'flex-wrap:wrap'
        ];

        // Unit box styles based on layout
        $unitStyles = [
            'display:flex',
            'flex-direction:column',
            'align-items:center'
        ];

        if ($layout === 'boxed') {
            $unitStyles[] = 'background:' . esc_attr($boxBackground);
            $unitStyles[] = 'padding:' . esc_attr($boxPadding);
            $unitStyles[] = 'border-radius:' . esc_attr($boxRadius);
            $unitStyles[] = 'min-width:80px';
        } elseif ($layout === 'circle') {
            $unitStyles[] = 'background:' . esc_attr($boxBackground);
            $unitStyles[] = 'padding:' . esc_attr($boxPadding);
            $unitStyles[] = 'border-radius:50%';
            $unitStyles[] = 'width:100px';
            $unitStyles[] = 'height:100px';
            $unitStyles[] = 'justify-content:center';
        }

        // Number styles
        $numberStyles = [
            'font-size:' . esc_attr($numberSize),
            'font-weight:' . esc_attr($numberWeight),
            'color:' . esc_attr($numberColor),
            'line-height:1'
        ];

        // Label styles
        $labelStyles = [
            'font-size:' . esc_attr($labelSize),
            'color:' . esc_attr($labelColor),
            'margin-top:6px',
            'text-transform:uppercase',
            'letter-spacing:0.5px'
        ];

        // Separator styles
        $sepStyles = [
            'font-size:' . esc_attr($numberSize),
            'font-weight:' . esc_attr($numberWeight),
            'color:' . esc_attr($separatorColor),
            'align-self:flex-start',
            'margin-top:' . ($layout === 'inline' ? '0' : esc_attr($boxPadding))
        ];

        // Build HTML
        $html = '<div class="tb4-countdown" id="' . esc_attr($uniqueId) . '" style="' . implode(';', $containerStyles) . '" data-target="' . $targetDateTime . '" data-expired-message="' . esc_attr($expiredMessage) . '">';

        // Title
        if (!empty($title)) {
            $html .= '<div class="tb4-countdown-title" style="margin-bottom:16px;font-size:20px;font-weight:600;">' . esc_html($title) . '</div>';
        }

        // Units container
        $html .= '<div class="tb4-countdown-units" style="' . implode(';', $unitsContainerStyles) . '">';

        $units = [];
        if ($showDays) {
            $units[] = ['data-unit' => 'days', 'label' => $labelDays, 'value' => '00'];
        }
        if ($showHours) {
            $units[] = ['data-unit' => 'hours', 'label' => $labelHours, 'value' => '00'];
        }
        if ($showMinutes) {
            $units[] = ['data-unit' => 'minutes', 'label' => $labelMinutes, 'value' => '00'];
        }
        if ($showSeconds) {
            $units[] = ['data-unit' => 'seconds', 'label' => $labelSeconds, 'value' => '00'];
        }

        foreach ($units as $index => $unit) {
            // Add separator before each unit except the first
            if ($index > 0 && !empty($separatorChar)) {
                $html .= '<span class="tb4-countdown-separator" style="' . implode(';', $sepStyles) . '">' . esc_html($separatorChar) . '</span>';
            }

            $html .= '<div class="tb4-countdown-unit" style="' . implode(';', $unitStyles) . '">';
            $html .= '<span class="tb4-countdown-number" data-unit="' . esc_attr($unit['data-unit']) . '" style="' . implode(';', $numberStyles) . '">' . esc_html($unit['value']) . '</span>';
            $html .= '<span class="tb4-countdown-label" style="' . implode(';', $labelStyles) . '">' . esc_html($unit['label']) . '</span>';
            $html .= '</div>';
        }

        $html .= '</div>'; // End units container

        // Hidden expired message container
        $html .= '<div class="tb4-countdown-expired" style="display:none;font-size:24px;font-weight:600;color:' . esc_attr($numberColor) . ';">' . esc_html($expiredMessage) . '</div>';

        $html .= '</div>'; // End countdown container

        // Add countdown script
        $html .= $this->generate_countdown_script();

        return $html;
    }

    /**
     * Generate countdown JavaScript (only once per page)
     */
    private static bool $scriptIncluded = false;

    private function generate_countdown_script(): string
    {
        if (self::$scriptIncluded) {
            return '';
        }
        self::$scriptIncluded = true;

        return '<script>
(function() {
    function updateCountdown(container) {
        var targetStr = container.getAttribute("data-target");
        var expiredMsg = container.getAttribute("data-expired-message") || "Event has started!";
        var targetDate = new Date(targetStr);
        var now = new Date();
        var diff = targetDate - now;

        var unitsContainer = container.querySelector(".tb4-countdown-units");
        var expiredContainer = container.querySelector(".tb4-countdown-expired");

        if (diff <= 0) {
            if (unitsContainer) unitsContainer.style.display = "none";
            if (expiredContainer) {
                expiredContainer.style.display = "block";
                expiredContainer.textContent = expiredMsg;
            }
            return false;
        }

        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
        var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((diff % (1000 * 60)) / 1000);

        var daysEl = container.querySelector("[data-unit=\"days\"]");
        var hoursEl = container.querySelector("[data-unit=\"hours\"]");
        var minutesEl = container.querySelector("[data-unit=\"minutes\"]");
        var secondsEl = container.querySelector("[data-unit=\"seconds\"]");

        if (daysEl) daysEl.textContent = String(days).padStart(2, "0");
        if (hoursEl) hoursEl.textContent = String(hours).padStart(2, "0");
        if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, "0");
        if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, "0");

        return true;
    }

    function initCountdowns() {
        var countdowns = document.querySelectorAll(".tb4-countdown[data-target]");
        countdowns.forEach(function(container) {
            if (container.classList.contains("tb4-countdown-initialized")) return;
            container.classList.add("tb4-countdown-initialized");

            // Initial update
            var isActive = updateCountdown(container);

            // Update every second if still counting
            if (isActive) {
                setInterval(function() {
                    updateCountdown(container);
                }, 1000);
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initCountdowns);
    } else {
        initCountdowns();
    }
})();
</script>';
    }
}
