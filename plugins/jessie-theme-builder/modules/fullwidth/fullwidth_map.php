<?php
/**
 * Fullwidth Map Module
 * Full-width map embed
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthMap extends JTB_Element
{
    public string $icon = 'map-fullwidth';
    public string $category = 'fullwidth';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    public function getSlug(): string
    {
        return 'fullwidth_map';
    }

    public function getName(): string
    {
        return 'Fullwidth Map';
    }

    public function getFields(): array
    {
        return [
            'address' => [
                'label' => 'Address',
                'type' => 'text',
                'default' => 'New York, NY'
            ],
            'zoom' => [
                'label' => 'Zoom Level',
                'type' => 'range',
                'min' => 1,
                'max' => 20,
                'default' => 14
            ],
            'map_height' => [
                'label' => 'Map Height',
                'type' => 'range',
                'min' => 200,
                'max' => 800,
                'unit' => 'px',
                'default' => 400,
                'responsive' => true
            ],
            'grayscale' => [
                'label' => 'Grayscale Filter',
                'type' => 'toggle',
                'default' => false
            ],
            'mouse_wheel' => [
                'label' => 'Enable Mouse Wheel Zoom',
                'type' => 'toggle',
                'default' => false
            ],
            'draggable' => [
                'label' => 'Enable Dragging',
                'type' => 'toggle',
                'default' => true
            ],
            'show_info_window' => [
                'label' => 'Show Info Window',
                'type' => 'toggle',
                'default' => false
            ],
            'info_window_content' => [
                'label' => 'Info Window Content',
                'type' => 'richtext',
                'show_if' => ['show_info_window' => true]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $address = $this->esc($attrs['address'] ?? 'New York, NY');
        $zoom = $attrs['zoom'] ?? 14;
        $grayscale = !empty($attrs['grayscale']);

        $mapId = 'jtb-fullwidth-map-' . $this->generateId();

        // Using OpenStreetMap embed
        $encodedAddress = urlencode($address);
        $bbox = $this->calculateBbox(40.7128, -74.0060, $zoom); // Default to NYC coordinates

        $containerClass = 'jtb-fullwidth-map-container';
        if ($grayscale) {
            $containerClass .= ' jtb-map-grayscale';
        }

        $innerHtml = '<div class="' . $containerClass . '" id="' . $mapId . '">';
        $innerHtml .= '<iframe ';
        $innerHtml .= 'class="jtb-map-iframe" ';
        $innerHtml .= 'src="https://www.openstreetmap.org/export/embed.html?bbox=' . $bbox . '&layer=mapnik&marker=40.7128,-74.0060" ';
        $innerHtml .= 'style="border: 0;" ';
        $innerHtml .= 'allowfullscreen="" ';
        $innerHtml .= 'loading="lazy" ';
        $innerHtml .= 'referrerpolicy="no-referrer-when-downgrade">';
        $innerHtml .= '</iframe>';

        // Address overlay
        $mapPinIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
        $innerHtml .= '<div class="jtb-map-address-overlay">';
        $innerHtml .= '<span class="jtb-map-pin">' . $mapPinIcon . '</span>';
        $innerHtml .= '<span class="jtb-map-address-text">' . $address . '</span>';
        $innerHtml .= '</div>';

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    private function calculateBbox(float $lat, float $lng, int $zoom): string
    {
        // Simplified bbox calculation based on zoom
        $delta = 0.1 / ($zoom / 10);
        $bbox = ($lng - $delta) . ',' . ($lat - $delta) . ',' . ($lng + $delta) . ',' . ($lat + $delta);
        return $bbox;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $mapHeight = $attrs['map_height'] ?? 400;
        $grayscale = !empty($attrs['grayscale']);

        // Container
        $css .= $selector . ' .jtb-fullwidth-map-container { position: relative; width: 100%; height: ' . $mapHeight . 'px; }' . "\n";

        // Iframe
        $css .= $selector . ' .jtb-map-iframe { width: 100%; height: 100%; border: 0; }' . "\n";

        // Grayscale
        if ($grayscale) {
            $css .= $selector . ' .jtb-map-grayscale .jtb-map-iframe { filter: grayscale(100%); }' . "\n";
        }

        // Address overlay
        $css .= $selector . ' .jtb-map-address-overlay { '
            . 'position: absolute; '
            . 'bottom: 20px; '
            . 'left: 20px; '
            . 'background: rgba(255,255,255,0.95); '
            . 'padding: 15px 20px; '
            . 'display: flex; '
            . 'align-items: center; '
            . 'gap: 10px; '
            . 'box-shadow: 0 2px 10px rgba(0,0,0,0.1); '
            . 'z-index: 10; '
            . '}' . "\n";

        $css .= $selector . ' .jtb-map-pin { display: flex; align-items: center; color: #e74c3c; }' . "\n";
        $css .= $selector . ' .jtb-map-pin svg { width: 20px; height: 20px; }' . "\n";
        $css .= $selector . ' .jtb-map-address-text { font-size: 14px; color: #333; }' . "\n";

        // Responsive
        if (!empty($attrs['map_height__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-fullwidth-map-container { height: ' . $attrs['map_height__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['map_height__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-fullwidth-map-container { height: ' . $attrs['map_height__phone'] . 'px; } }' . "\n";
        }

        $css .= '@media (max-width: 767px) {' . "\n";
        $css .= '  ' . $selector . ' .jtb-map-address-overlay { left: 10px; right: 10px; bottom: 10px; }' . "\n";
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_map', JTB_Module_FullwidthMap::class);
