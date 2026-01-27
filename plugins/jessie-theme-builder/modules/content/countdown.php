<?php
/**
 * Countdown Timer Module
 * Countdown to a specific date/time
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Countdown extends JTB_Element
{
    public string $icon = 'clock';
    public string $category = 'content';

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
        return 'countdown';
    }

    public function getName(): string
    {
        return 'Countdown Timer';
    }

    public function getFields(): array
    {
        return [
            'date' => [
                'label' => 'End Date',
                'type' => 'date',
                'default' => date('Y-m-d', strtotime('+30 days'))
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Countdown to Event'
            ],
            'show_labels' => [
                'label' => 'Show Labels',
                'type' => 'toggle',
                'default' => true
            ],
            'show_separator' => [
                'label' => 'Show Separator',
                'type' => 'toggle',
                'default' => true
            ],
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'inline' => 'Inline',
                    'block' => 'Block',
                    'circle' => 'Circle'
                ],
                'default' => 'inline'
            ],
            // Colors
            'number_color' => [
                'label' => 'Number Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'separator_color' => [
                'label' => 'Separator Color',
                'type' => 'color',
                'default' => '#cccccc'
            ],
            'box_bg_color' => [
                'label' => 'Box Background',
                'type' => 'color',
                'default' => '#f9f9f9'
            ],
            // Sizing
            'number_font_size' => [
                'label' => 'Number Size',
                'type' => 'range',
                'min' => 20,
                'max' => 100,
                'unit' => 'px',
                'default' => 48,
                'responsive' => true
            ],
            'label_font_size' => [
                'label' => 'Label Size',
                'type' => 'range',
                'min' => 10,
                'max' => 24,
                'unit' => 'px',
                'default' => 14,
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $date = $attrs['date'] ?? date('Y-m-d', strtotime('+30 days'));
        $title = $this->esc($attrs['title'] ?? '');
        $showLabels = $attrs['show_labels'] ?? true;
        $showSeparator = $attrs['show_separator'] ?? true;
        $layout = $attrs['layout'] ?? 'inline';

        $countdownId = 'jtb-countdown-' . $this->generateId();

        $containerClass = 'jtb-countdown-container jtb-countdown-layout-' . $layout;

        $innerHtml = '<div class="' . $containerClass . '" id="' . $countdownId . '" data-date="' . $this->esc($date) . '">';

        if (!empty($title)) {
            $innerHtml .= '<h3 class="jtb-countdown-title">' . $title . '</h3>';
        }

        $innerHtml .= '<div class="jtb-countdown-timer">';

        // Days
        $innerHtml .= '<div class="jtb-countdown-unit jtb-countdown-days">';
        $innerHtml .= '<span class="jtb-countdown-number" data-unit="days">00</span>';
        if ($showLabels) {
            $innerHtml .= '<span class="jtb-countdown-label">Days</span>';
        }
        $innerHtml .= '</div>';

        if ($showSeparator) {
            $innerHtml .= '<span class="jtb-countdown-separator">:</span>';
        }

        // Hours
        $innerHtml .= '<div class="jtb-countdown-unit jtb-countdown-hours">';
        $innerHtml .= '<span class="jtb-countdown-number" data-unit="hours">00</span>';
        if ($showLabels) {
            $innerHtml .= '<span class="jtb-countdown-label">Hours</span>';
        }
        $innerHtml .= '</div>';

        if ($showSeparator) {
            $innerHtml .= '<span class="jtb-countdown-separator">:</span>';
        }

        // Minutes
        $innerHtml .= '<div class="jtb-countdown-unit jtb-countdown-minutes">';
        $innerHtml .= '<span class="jtb-countdown-number" data-unit="minutes">00</span>';
        if ($showLabels) {
            $innerHtml .= '<span class="jtb-countdown-label">Minutes</span>';
        }
        $innerHtml .= '</div>';

        if ($showSeparator) {
            $innerHtml .= '<span class="jtb-countdown-separator">:</span>';
        }

        // Seconds
        $innerHtml .= '<div class="jtb-countdown-unit jtb-countdown-seconds">';
        $innerHtml .= '<span class="jtb-countdown-number" data-unit="seconds">00</span>';
        if ($showLabels) {
            $innerHtml .= '<span class="jtb-countdown-label">Seconds</span>';
        }
        $innerHtml .= '</div>';

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $layout = $attrs['layout'] ?? 'inline';
        $numberColor = $attrs['number_color'] ?? '#333333';
        $labelColor = $attrs['label_color'] ?? '#666666';
        $separatorColor = $attrs['separator_color'] ?? '#cccccc';
        $boxBg = $attrs['box_bg_color'] ?? '#f9f9f9';
        $numberSize = $attrs['number_font_size'] ?? 48;
        $labelSize = $attrs['label_font_size'] ?? 14;

        // Container
        $css .= $selector . ' .jtb-countdown-container { text-align: center; }' . "\n";
        $css .= $selector . ' .jtb-countdown-title { margin-bottom: 20px; }' . "\n";

        // Timer
        $css .= $selector . ' .jtb-countdown-timer { display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap; }' . "\n";

        // Unit
        if ($layout === 'block' || $layout === 'circle') {
            $css .= $selector . ' .jtb-countdown-unit { '
                . 'background: ' . $boxBg . '; '
                . 'padding: 20px 25px; '
                . 'min-width: 80px; '
                . 'text-align: center; '
                . '}' . "\n";

            if ($layout === 'circle') {
                $css .= $selector . ' .jtb-countdown-unit { border-radius: 50%; width: 100px; height: 100px; display: flex; flex-direction: column; align-items: center; justify-content: center; }' . "\n";
            }
        } else {
            $css .= $selector . ' .jtb-countdown-unit { text-align: center; }' . "\n";
        }

        // Number
        $css .= $selector . ' .jtb-countdown-number { '
            . 'display: block; '
            . 'font-size: ' . $numberSize . 'px; '
            . 'font-weight: bold; '
            . 'color: ' . $numberColor . '; '
            . 'line-height: 1; '
            . '}' . "\n";

        // Label
        $css .= $selector . ' .jtb-countdown-label { '
            . 'display: block; '
            . 'font-size: ' . $labelSize . 'px; '
            . 'color: ' . $labelColor . '; '
            . 'text-transform: uppercase; '
            . 'letter-spacing: 1px; '
            . 'margin-top: 5px; '
            . '}' . "\n";

        // Separator
        $css .= $selector . ' .jtb-countdown-separator { '
            . 'font-size: ' . $numberSize . 'px; '
            . 'color: ' . $separatorColor . '; '
            . 'font-weight: bold; '
            . '}' . "\n";

        if ($layout !== 'inline') {
            $css .= $selector . ' .jtb-countdown-separator { display: none; }' . "\n";
        }

        // Responsive
        if (!empty($attrs['number_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-countdown-number, ' . $selector . ' .jtb-countdown-separator { font-size: ' . $attrs['number_font_size__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['number_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-countdown-number, ' . $selector . ' .jtb-countdown-separator { font-size: ' . $attrs['number_font_size__phone'] . 'px; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('countdown', JTB_Module_Countdown::class);
