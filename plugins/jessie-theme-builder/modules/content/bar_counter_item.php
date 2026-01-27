<?php
/**
 * Bar Counter Item Module (Child)
 * Single bar in a bar counters group
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_BarCounterItem extends JTB_Element
{
    public string $icon = 'minus';
    public string $category = 'content';
    public bool $is_child = true;

    public bool $use_typography = true;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'bar_counter_item';
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
                'label' => 'Bar Background',
                'type' => 'color',
                'default' => '#dddddd'
            ],
            'bar_color' => [
                'label' => 'Bar Fill Color',
                'type' => 'color',
                'default' => '#7c3aed'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'percent_color' => [
                'label' => 'Percent Color',
                'type' => 'color',
                'default' => '#666666'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $title = $this->esc($attrs['content'] ?? 'Progress Bar');
        $percent = intval($attrs['percent'] ?? 50);

        $html = '<div class="jtb-bar-counter-item">';
        $html .= '<div class="jtb-bar-counter-header">';
        $html .= '<span class="jtb-bar-counter-title">' . $title . '</span>';
        $html .= '<span class="jtb-bar-counter-percent" data-target="' . $percent . '">0%</span>';
        $html .= '</div>';
        $html .= '<div class="jtb-bar-counter-track">';
        $html .= '<div class="jtb-bar-counter-fill" data-percent="' . $percent . '" style="width: 0%"></div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $barBg = $attrs['bar_background_color'] ?? '#dddddd';
        $barColor = $attrs['bar_color'] ?? '#7c3aed';
        $labelColor = $attrs['label_color'] ?? '#333333';
        $percentColor = $attrs['percent_color'] ?? '#666666';

        // Item container
        $css .= $selector . ' { margin-bottom: 20px; }' . "\n";
        $css .= $selector . ':last-child { margin-bottom: 0; }' . "\n";

        // Header
        $css .= $selector . ' .jtb-bar-counter-header { ';
        $css .= 'display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; ';
        $css .= '}' . "\n";

        // Title
        $css .= $selector . ' .jtb-bar-counter-title { ';
        $css .= 'font-weight: 600; color: ' . $labelColor . '; ';
        $css .= '}' . "\n";

        // Percent
        $css .= $selector . ' .jtb-bar-counter-percent { ';
        $css .= 'font-size: 14px; color: ' . $percentColor . '; ';
        $css .= '}' . "\n";

        // Track
        $css .= $selector . ' .jtb-bar-counter-track { ';
        $css .= 'background-color: ' . $barBg . '; height: 16px; border-radius: 8px; overflow: hidden; ';
        $css .= '}' . "\n";

        // Fill
        $css .= $selector . ' .jtb-bar-counter-fill { ';
        $css .= 'background-color: ' . $barColor . '; height: 100%; border-radius: 8px; ';
        $css .= 'transition: width 1.5s ease-in-out; ';
        $css .= '}' . "\n";

        return $css;
    }
}

JTB_Registry::register('bar_counter_item', JTB_Module_BarCounterItem::class);
