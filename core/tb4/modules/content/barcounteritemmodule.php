<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

/**
 * TB 4.0 Bar Counter Item Module
 * Individual progress bar item for use within Bar Counter parent module
 */
class BarCounterItemModule extends ChildModule
{
    protected string $name = 'Bar Counter Item';
    protected string $slug = 'bar_counter_item';
    protected string $icon = 'minus';
    protected string $category = 'content';
    protected string $type = 'child';
    protected ?string $parent_slug = 'bar_counter';

    public function get_content_fields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Progress'
            ],
            'percent' => [
                'label' => 'Percentage',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'default' => 75
            ],
            'bar_color' => [
                'label' => 'Bar Color',
                'type' => 'color',
                'default' => '#3b82f6'
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'show_percent' => [
                'label' => 'Show Percentage',
                'type' => 'toggle',
                'default' => 'yes'
            ],
            'bar_height' => [
                'label' => 'Bar Height (px)',
                'type' => 'number',
                'default' => 8
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $title = $data['content']['title'] ?? 'Progress';
        $percent = intval($data['content']['percent'] ?? 75);
        $barColor = $data['content']['bar_color'] ?? '#3b82f6';
        $bgColor = $data['content']['background_color'] ?? '#e5e7eb';
        $showPercent = ($data['content']['show_percent'] ?? 'yes') === 'yes';
        $height = intval($data['content']['bar_height'] ?? 8);

        $html = '<div class="tb4-bar-counter-item" style="margin-bottom:16px;">';
        $html .= '<div style="display:flex;justify-content:space-between;margin-bottom:6px;">';
        $html .= '<span style="font-size:14px;font-weight:500;color:#374151;">' . htmlspecialchars($title) . '</span>';
        if ($showPercent) {
            $html .= '<span style="font-size:14px;color:#6b7280;">' . $percent . '%</span>';
        }
        $html .= '</div>';
        $html .= '<div style="height:' . $height . 'px;background:' . htmlspecialchars($bgColor) . ';border-radius:' . ($height/2) . 'px;overflow:hidden;">';
        $html .= '<div style="width:' . $percent . '%;height:100%;background:' . htmlspecialchars($barColor) . ';border-radius:' . ($height/2) . 'px;transition:width 0.3s ease;"></div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
