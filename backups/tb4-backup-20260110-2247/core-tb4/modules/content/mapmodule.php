<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Map Module (Parent)
 * Interactive map display with multiple provider support
 * Child module: map_item (map pins/markers)
 */
class MapModule extends Module
{
    protected string $name = 'Map';
    protected string $slug = 'map';
    protected string $icon = 'map-pin';
    protected string $category = 'content';
    protected string $type = 'parent';
    protected ?string $child_slug = 'map_item';

    public function get_content_fields(): array
    {
        return [
            'map_provider' => [
                'label' => 'Map Provider',
                'type' => 'select',
                'options' => [
                    'google' => 'Google Maps',
                    'openstreetmap' => 'OpenStreetMap',
                    'mapbox' => 'Mapbox'
                ],
                'default' => 'google'
            ],
            'api_key' => [
                'label' => 'API Key',
                'type' => 'text',
                'default' => '',
                'description' => 'Required for Google Maps and Mapbox'
            ],
            'map_center_lat' => [
                'label' => 'Center Latitude',
                'type' => 'text',
                'default' => '51.5074'
            ],
            'map_center_lng' => [
                'label' => 'Center Longitude',
                'type' => 'text',
                'default' => '-0.1278'
            ],
            'zoom_level' => [
                'label' => 'Zoom Level',
                'type' => 'range',
                'min' => 1,
                'max' => 20,
                'default' => 12
            ],
            'map_height' => [
                'label' => 'Map Height (px)',
                'type' => 'number',
                'default' => 400
            ],
            'map_style' => [
                'label' => 'Map Style',
                'type' => 'select',
                'options' => [
                    'roadmap' => 'Roadmap',
                    'satellite' => 'Satellite',
                    'terrain' => 'Terrain',
                    'hybrid' => 'Hybrid'
                ],
                'default' => 'roadmap'
            ],
            'draggable' => [
                'label' => 'Draggable',
                'type' => 'toggle',
                'default' => 'yes'
            ],
            'scroll_zoom' => [
                'label' => 'Scroll to Zoom',
                'type' => 'toggle',
                'default' => 'yes'
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $content = $data['content'] ?? $data;
        $provider = $content['map_provider'] ?? 'google';
        $height = intval($content['map_height'] ?? 400);
        $lat = $content['map_center_lat'] ?? '51.5074';
        $lng = $content['map_center_lng'] ?? '-0.1278';
        $zoom = intval($content['zoom_level'] ?? 12);

        $html = '<div class="tb4-map" style="position:relative;height:' . $height . 'px;background:#e5e7eb;border-radius:8px;overflow:hidden;">';
        $html .= '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#6b7280;">';
        $html .= '<i data-lucide="map-pin" style="width:48px;height:48px;margin-bottom:12px;"></i>';
        $html .= '<div style="font-weight:500;">' . ucfirst(htmlspecialchars($provider)) . ' Map</div>';
        $html .= '<div style="font-size:12px;margin-top:4px;">Lat: ' . htmlspecialchars($lat) . ', Lng: ' . htmlspecialchars($lng) . '</div>';
        $html .= '<div style="font-size:12px;">Zoom: ' . $zoom . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
