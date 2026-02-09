<?php
/**
 * Map Module
 * Google Maps or OpenStreetMap embed
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Map extends JTB_Element
{
    public string $icon = 'map-pin';
    public string $category = 'media';
    public string $child_slug = 'map_pin';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'map';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'map_height' => [
            'property' => 'height',
            'selector' => '.jtb-map-container',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'map';
    }

    public function getName(): string
    {
        return 'Map';
    }

    public function getFields(): array
    {
        return [
            'address' => [
                'label' => 'Map Address',
                'type' => 'text',
                'default' => 'New York, NY, USA',
                'description' => 'Enter an address or place name'
            ],
            'zoom' => [
                'label' => 'Zoom Level',
                'type' => 'range',
                'min' => 1,
                'max' => 22,
                'default' => 14
            ],
            'map_height' => [
                'label' => 'Map Height',
                'type' => 'range',
                'min' => 100,
                'max' => 1000,
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
                'label' => 'Mouse Wheel Zoom',
                'type' => 'toggle',
                'default' => true
            ],
            'mobile_dragging' => [
                'label' => 'Mobile Dragging',
                'type' => 'toggle',
                'default' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $address = $attrs['address'] ?? 'New York, NY, USA';
        $zoom = $attrs['zoom'] ?? 14;
        $height = $attrs['map_height'] ?? 400;

        // Use OpenStreetMap embed (no API key needed)
        $encodedAddress = urlencode($address);

        $innerHtml = '<div class="jtb-map-container" style="height: ' . $height . 'px;">';
        $innerHtml .= '<iframe ';
        $innerHtml .= 'class="jtb-map-iframe" ';
        $innerHtml .= 'width="100%" ';
        $innerHtml .= 'height="100%" ';
        $innerHtml .= 'frameborder="0" ';
        $innerHtml .= 'style="border:0" ';
        $innerHtml .= 'src="https://www.openstreetmap.org/export/embed.html?bbox=-74.0060%2C40.7128%2C-73.9960%2C40.7228&layer=mapnik&marker=40.7178%2C-74.0010" ';
        $innerHtml .= 'allowfullscreen>';
        $innerHtml .= '</iframe>';
        $innerHtml .= '<small class="jtb-map-attribution"><a href="https://www.openstreetmap.org/?mlat=40.7178&mlon=-74.0010#map=' . $zoom . '/40.7178/-74.0010" target="_blank">View Larger Map</a></small>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Map module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $css .= $selector . ' .jtb-map-container { position: relative; overflow: hidden; }' . "\n";
        $css .= $selector . ' .jtb-map-iframe { width: 100%; height: 100%; }' . "\n";
        $css .= $selector . ' .jtb-map-attribution { position: absolute; bottom: 5px; right: 10px; font-size: 11px; }' . "\n";

        // Grayscale filter
        if (!empty($attrs['grayscale'])) {
            $css .= $selector . ' .jtb-map-iframe { filter: grayscale(100%); }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('map', JTB_Module_Map::class);
