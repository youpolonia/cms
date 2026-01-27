<?php
/**
 * Bar Counter Module
 * Animated horizontal progress bar
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_BarCounter extends JTB_Element
{
    public string $icon = 'bar-chart-2';
    public string $category = 'content';
    public string $child_slug = 'bar_counter_item';

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
        return 'bar_counter';
    }

    public function getName(): string
    {
        return 'Bar Counter';
    }

    public function getFields(): array
    {
        return [
            'content' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Progress Bar'
            ],
            'percent' => [
                'label' => 'Percent',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 50
            ],
            'bar_background_color' => [
                'label' => 'Bar Background Color',
                'type' => 'color',
                'default' => '#dddddd'
            ],
            'bar_color' => [
                'label' => 'Bar Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'use_percentages' => [
                'label' => 'Show Percent',
                'type' => 'toggle',
                'default' => true
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'percent_color' => [
                'label' => 'Percent Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'bar_height' => [
                'label' => 'Bar Height',
                'type' => 'range',
                'min' => 5,
                'max' => 100,
                'unit' => 'px',
                'default' => 20
            ],
            'bar_border_radius' => [
                'label' => 'Bar Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 0
            ],
            'use_stripes' => [
                'label' => 'Use Stripes',
                'type' => 'toggle',
                'default' => false
            ],
            'stripe_animate' => [
                'label' => 'Animate Stripes',
                'type' => 'toggle',
                'default' => false,
                'show_if' => ['use_stripes' => true]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $title = $this->esc($attrs['content'] ?? 'Progress Bar');
        $percent = intval($attrs['percent'] ?? 50);
        $showPercent = $attrs['use_percentages'] ?? true;

        $innerHtml = '<div class="jtb-bar-counter-container">';
        $innerHtml .= '<div class="jtb-bar-counter-header">';
        $innerHtml .= '<span class="jtb-bar-counter-title">' . $title . '</span>';
        if ($showPercent) {
            $innerHtml .= '<span class="jtb-bar-counter-percent" data-target="' . $percent . '">0%</span>';
        }
        $innerHtml .= '</div>';
        $innerHtml .= '<div class="jtb-bar-counter-bar">';
        $innerHtml .= '<div class="jtb-bar-counter-progress" data-percent="' . $percent . '" style="width: 0%"></div>';
        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Header styles
        $css .= $selector . ' .jtb-bar-counter-header { display: flex; justify-content: space-between; margin-bottom: 8px; }' . "\n";

        // Label color
        if (!empty($attrs['label_color'])) {
            $css .= $selector . ' .jtb-bar-counter-title { color: ' . $attrs['label_color'] . '; }' . "\n";
        }

        // Percent color
        if (!empty($attrs['percent_color'])) {
            $css .= $selector . ' .jtb-bar-counter-percent { color: ' . $attrs['percent_color'] . '; }' . "\n";
        }

        // Bar container
        $barBg = $attrs['bar_background_color'] ?? '#dddddd';
        $barHeight = $attrs['bar_height'] ?? 20;
        $barRadius = $attrs['bar_border_radius'] ?? 0;

        $css .= $selector . ' .jtb-bar-counter-bar { ';
        $css .= 'background-color: ' . $barBg . '; ';
        $css .= 'height: ' . $barHeight . 'px; ';
        $css .= 'border-radius: ' . $barRadius . 'px; ';
        $css .= 'overflow: hidden; ';
        $css .= '}' . "\n";

        // Progress bar
        $barColor = $attrs['bar_color'] ?? '#2ea3f2';

        $css .= $selector . ' .jtb-bar-counter-progress { ';
        $css .= 'background-color: ' . $barColor . '; ';
        $css .= 'height: 100%; ';
        $css .= 'border-radius: ' . $barRadius . 'px; ';
        $css .= 'transition: width 1.5s ease-in-out; ';
        $css .= '}' . "\n";

        // Stripes
        if (!empty($attrs['use_stripes'])) {
            $css .= $selector . ' .jtb-bar-counter-progress { ';
            $css .= 'background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent); ';
            $css .= 'background-size: 40px 40px; ';
            $css .= '}' . "\n";

            if (!empty($attrs['stripe_animate'])) {
                $css .= '@keyframes jtb-progress-stripes { from { background-position: 40px 0; } to { background-position: 0 0; } }' . "\n";
                $css .= $selector . ' .jtb-bar-counter-progress { animation: jtb-progress-stripes 1s linear infinite; }' . "\n";
            }
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('bar_counter', JTB_Module_BarCounter::class);
