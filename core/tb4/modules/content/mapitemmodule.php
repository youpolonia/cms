<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

/**
 * TB 4.0 Map Item Module (Child Module)
 * Individual map pin/marker for use within parent Map module.
 */
class MapItemModule extends ChildModule
{
    protected string $name = 'Map Pin';
    protected string $slug = 'map_item';
    protected string $icon = 'map-pin';
    protected string $category = 'content';
    protected string $type = 'child';
    protected ?string $parent_slug = 'map';
    protected ?string $child_title_var = 'pin_title';

    public function get_content_fields(): array
    {
        return [
            'pin_title' => [
                'label' => 'Pin Title',
                'type' => 'text',
                'default' => 'Location'
            ],
            'pin_address' => [
                'label' => 'Address',
                'type' => 'textarea',
                'default' => ''
            ],
            'pin_lat' => [
                'label' => 'Latitude',
                'type' => 'text',
                'default' => '51.5074'
            ],
            'pin_lng' => [
                'label' => 'Longitude',
                'type' => 'text',
                'default' => '-0.1278'
            ],
            'pin_color' => [
                'label' => 'Pin Color',
                'type' => 'color',
                'default' => '#ef4444'
            ],
            'info_window' => [
                'label' => 'Show Info Window',
                'type' => 'toggle',
                'default' => 'yes'
            ],
            'info_content' => [
                'label' => 'Info Window Content',
                'type' => 'wysiwyg',
                'default' => ''
            ],
            'pin_icon' => [
                'label' => 'Custom Icon URL',
                'type' => 'image',
                'default' => '',
                'description' => 'Leave empty for default pin'
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $content = $data['content'] ?? $data;
        $title = $content['pin_title'] ?? 'Location';
        $address = $content['pin_address'] ?? '';
        $lat = $content['pin_lat'] ?? '51.5074';
        $lng = $content['pin_lng'] ?? '-0.1278';
        $color = $content['pin_color'] ?? '#ef4444';

        $html = '<div class="tb4-map-item" style="display:flex;align-items:flex-start;gap:12px;padding:12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">';
        $html .= '<div style="width:36px;height:36px;border-radius:50%;background:' . htmlspecialchars($color) . ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">';
        $html .= '<i data-lucide="map-pin" style="width:20px;height:20px;color:white;"></i>';
        $html .= '</div>';
        $html .= '<div style="flex:1;min-width:0;">';
        $html .= '<div style="font-weight:600;color:#111827;">' . htmlspecialchars($title) . '</div>';
        if ($address) {
            $html .= '<div style="font-size:13px;color:#6b7280;margin-top:2px;">' . htmlspecialchars($address) . '</div>';
        }
        $html .= '<div style="font-size:11px;color:#9ca3af;margin-top:4px;">Lat: ' . htmlspecialchars($lat) . ', Lng: ' . htmlspecialchars($lng) . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
