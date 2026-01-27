<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Fullwidth Map Module
 *
 * Full-width Google Maps / OpenStreetMap embed with optional info overlay.
 * Supports address or coordinate-based location, multiple map styles,
 * and customizable info box with contact details.
 */
class FwMapModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Map';
        $this->slug = 'fw_map';
        $this->icon = 'map-pin';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-map',
            'container' => '.tb4-fw-map-container',
            'iframe' => '.tb4-fw-map-iframe',
            'info' => '.tb4-fw-map-info',
            'title' => '.tb4-fw-map-title',
            'item' => '.tb4-fw-map-item',
            'button' => '.tb4-fw-map-btn'
        ];

        // Content fields
        $this->content_fields = [
            'map_provider' => [
                'type' => 'select',
                'label' => 'Map Provider',
                'options' => ['google' => 'Google Maps', 'openstreetmap' => 'OpenStreetMap'],
                'default' => 'openstreetmap'
            ],
            'address' => [
                'type' => 'text',
                'label' => 'Address',
                'default' => 'New York, NY, USA'
            ],
            'latitude' => [
                'type' => 'text',
                'label' => 'Latitude',
                'default' => '40.7128'
            ],
            'longitude' => [
                'type' => 'text',
                'label' => 'Longitude',
                'default' => '-74.0060'
            ],
            'use_coordinates' => [
                'type' => 'select',
                'label' => 'Use Coordinates',
                'options' => ['no' => 'No (Use Address)', 'yes' => 'Yes (Use Lat/Lng)'],
                'default' => 'no'
            ],
            'zoom_level' => [
                'type' => 'select',
                'label' => 'Zoom Level',
                'options' => [
                    '10' => '10 - City',
                    '12' => '12 - District',
                    '14' => '14 - Neighborhood',
                    '16' => '16 - Street',
                    '18' => '18 - Building'
                ],
                'default' => '14'
            ],
            'google_api_key' => [
                'type' => 'text',
                'label' => 'Google API Key',
                'default' => ''
            ],
            'show_marker' => [
                'type' => 'select',
                'label' => 'Show Marker',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ],
            'show_info_box' => [
                'type' => 'select',
                'label' => 'Show Info Box',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'info_title' => [
                'type' => 'text',
                'label' => 'Info Box Title',
                'default' => 'Our Location'
            ],
            'info_address' => [
                'type' => 'textarea',
                'label' => 'Info Box Address',
                'default' => "123 Main Street\nNew York, NY 10001"
            ],
            'info_phone' => [
                'type' => 'text',
                'label' => 'Info Box Phone',
                'default' => '+1 (555) 123-4567'
            ],
            'info_email' => [
                'type' => 'text',
                'label' => 'Info Box Email',
                'default' => 'contact@example.com'
            ],
            'info_hours' => [
                'type' => 'textarea',
                'label' => 'Info Box Hours',
                'default' => "Mon-Fri: 9:00 AM - 6:00 PM\nSat-Sun: Closed"
            ],
            'show_directions_btn' => [
                'type' => 'select',
                'label' => 'Show Directions Button',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'yes'
            ],
            'directions_text' => [
                'type' => 'text',
                'label' => 'Directions Button Text',
                'default' => 'Get Directions'
            ]
        ];

        // Design fields
        $this->design_fields_custom = [
            'map_height' => [
                'type' => 'text',
                'label' => 'Map Height',
                'default' => '500px'
            ],
            'map_style' => [
                'type' => 'select',
                'label' => 'Map Style',
                'options' => ['default' => 'Default', 'grayscale' => 'Grayscale', 'dark' => 'Dark Mode'],
                'default' => 'default'
            ],
            'border_radius' => [
                'type' => 'text',
                'label' => 'Border Radius',
                'default' => '0px'
            ],
            'info_box_position' => [
                'type' => 'select',
                'label' => 'Info Box Position',
                'options' => [
                    'left' => 'Left',
                    'right' => 'Right',
                    'bottom-left' => 'Bottom Left',
                    'bottom-right' => 'Bottom Right'
                ],
                'default' => 'left'
            ],
            'info_box_width' => [
                'type' => 'text',
                'label' => 'Info Box Width',
                'default' => '350px'
            ],
            'info_box_bg' => [
                'type' => 'color',
                'label' => 'Info Box Background',
                'default' => '#ffffff'
            ],
            'info_box_padding' => [
                'type' => 'text',
                'label' => 'Info Box Padding',
                'default' => '32px'
            ],
            'info_box_radius' => [
                'type' => 'text',
                'label' => 'Info Box Radius',
                'default' => '12px'
            ],
            'info_box_shadow' => [
                'type' => 'select',
                'label' => 'Info Box Shadow',
                'options' => ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'],
                'default' => 'lg'
            ],
            'title_color' => [
                'type' => 'color',
                'label' => 'Title Color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'type' => 'text',
                'label' => 'Title Font Size',
                'default' => '24px'
            ],
            'text_color' => [
                'type' => 'color',
                'label' => 'Text Color',
                'default' => '#4b5563'
            ],
            'text_font_size' => [
                'type' => 'text',
                'label' => 'Text Font Size',
                'default' => '14px'
            ],
            'icon_color' => [
                'type' => 'color',
                'label' => 'Icon Color',
                'default' => '#2563eb'
            ],
            'button_bg_color' => [
                'type' => 'color',
                'label' => 'Button Background',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'type' => 'color',
                'label' => 'Button Text Color',
                'default' => '#ffffff'
            ],
            'button_border_radius' => [
                'type' => 'text',
                'label' => 'Button Border Radius',
                'default' => '8px'
            ],
            'divider_color' => [
                'type' => 'color',
                'label' => 'Divider Color',
                'default' => '#e5e7eb'
            ]
        ];

        // Advanced fields
        $this->advanced_fields = array_merge($this->advanced_fields, [
            'css_id' => [
                'type' => 'text',
                'label' => 'CSS ID',
                'default' => ''
            ],
            'css_class' => [
                'type' => 'text',
                'label' => 'CSS Class',
                'default' => ''
            ],
            'custom_css' => [
                'type' => 'textarea',
                'label' => 'Custom CSS',
                'default' => ''
            ]
        ]);
    }

    public function get_content_fields(): array
    {
        return $this->content_fields;
    }

    public function get_design_fields(): array
    {
        return array_merge(parent::get_design_fields(), $this->design_fields_custom);
    }

    public function render(array $attrs): string
    {
        // Content fields
        $mapProvider = $attrs['map_provider'] ?? 'openstreetmap';
        $address = $attrs['address'] ?? 'New York, NY, USA';
        $latitude = $attrs['latitude'] ?? '40.7128';
        $longitude = $attrs['longitude'] ?? '-74.0060';
        $useCoordinates = ($attrs['use_coordinates'] ?? 'no') === 'yes';
        $zoomLevel = $attrs['zoom_level'] ?? '14';
        $googleApiKey = $attrs['google_api_key'] ?? '';
        $showMarker = ($attrs['show_marker'] ?? 'yes') === 'yes';
        $showInfoBox = ($attrs['show_info_box'] ?? 'no') === 'yes';
        $infoTitle = $attrs['info_title'] ?? 'Our Location';
        $infoAddress = $attrs['info_address'] ?? "123 Main Street\nNew York, NY 10001";
        $infoPhone = $attrs['info_phone'] ?? '+1 (555) 123-4567';
        $infoEmail = $attrs['info_email'] ?? 'contact@example.com';
        $infoHours = $attrs['info_hours'] ?? "Mon-Fri: 9:00 AM - 6:00 PM\nSat-Sun: Closed";
        $showDirectionsBtn = ($attrs['show_directions_btn'] ?? 'yes') === 'yes';
        $directionsText = $attrs['directions_text'] ?? 'Get Directions';

        // Design fields
        $mapHeight = $attrs['map_height'] ?? '500px';
        $mapStyle = $attrs['map_style'] ?? 'default';
        $borderRadius = $attrs['border_radius'] ?? '0px';
        $infoBoxPosition = $attrs['info_box_position'] ?? 'left';
        $infoBoxWidth = $attrs['info_box_width'] ?? '350px';
        $infoBoxBg = $attrs['info_box_bg'] ?? '#ffffff';
        $infoBoxPadding = $attrs['info_box_padding'] ?? '32px';
        $infoBoxRadius = $attrs['info_box_radius'] ?? '12px';
        $infoBoxShadow = $attrs['info_box_shadow'] ?? 'lg';
        $titleColor = $attrs['title_color'] ?? '#111827';
        $titleFontSize = $attrs['title_font_size'] ?? '24px';
        $textColor = $attrs['text_color'] ?? '#4b5563';
        $textFontSize = $attrs['text_font_size'] ?? '14px';
        $iconColor = $attrs['icon_color'] ?? '#2563eb';
        $buttonBgColor = $attrs['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $attrs['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $attrs['button_border_radius'] ?? '8px';
        $dividerColor = $attrs['divider_color'] ?? '#e5e7eb';

        // Advanced fields
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        // Shadow map
        $shadowMap = [
            'none' => 'none',
            'sm' => '0 2px 8px rgba(0,0,0,0.1)',
            'md' => '0 4px 16px rgba(0,0,0,0.12)',
            'lg' => '0 10px 40px rgba(0,0,0,0.15)'
        ];
        $shadowStyle = $shadowMap[$infoBoxShadow] ?? $shadowMap['lg'];

        // Info box position styles
        $positionStyles = [
            'left' => 'top:50%;left:32px;transform:translateY(-50%);',
            'right' => 'top:50%;right:32px;transform:translateY(-50%);',
            'bottom-left' => 'bottom:32px;left:32px;',
            'bottom-right' => 'bottom:32px;right:32px;'
        ];
        $infoBoxPosStyle = $positionStyles[$infoBoxPosition] ?? $positionStyles['left'];

        // Map filter for styles
        $mapFilter = '';
        if ($mapStyle === 'grayscale') {
            $mapFilter = 'filter:grayscale(100%);';
        } elseif ($mapStyle === 'dark') {
            $mapFilter = 'filter:invert(90%) hue-rotate(180deg);';
        }

        // Build map URL
        $lat = (float)$latitude;
        $lng = (float)$longitude;
        $encodedAddress = urlencode($address);

        if ($mapProvider === 'google' && $googleApiKey) {
            if ($useCoordinates) {
                $mapUrl = "https://www.google.com/maps/embed/v1/view?key=" . esc_attr($googleApiKey) . "&center={$lat},{$lng}&zoom={$zoomLevel}";
            } else {
                $mapUrl = "https://www.google.com/maps/embed/v1/place?key=" . esc_attr($googleApiKey) . "&q={$encodedAddress}&zoom={$zoomLevel}";
            }
        } else {
            // OpenStreetMap embed
            $bbox = ($lng - 0.01) . ',' . ($lat - 0.01) . ',' . ($lng + 0.01) . ',' . ($lat + 0.01);
            $markerParam = $showMarker ? "&marker={$lat},{$lng}" : '';
            $mapUrl = "https://www.openstreetmap.org/export/embed.html?bbox={$bbox}&layer=mapnik{$markerParam}";
        }

        // Directions URL
        $directionsUrl = "https://www.google.com/maps/dir/?api=1&destination=" . ($useCoordinates ? "{$lat},{$lng}" : $encodedAddress);

        // Container ID/Class
        $idAttr = $cssId ? ' id="' . esc_attr($cssId) . '"' : '';
        $classAttr = 'tb4-fw-map' . ($cssClass ? ' ' . esc_attr($cssClass) : '');

        // Build HTML
        $html = '<div' . $idAttr . ' class="' . $classAttr . '">';
        $html .= '<div class="tb4-fw-map-container" style="position:relative;width:100%;overflow:hidden;border-radius:' . esc_attr($borderRadius) . ';">';

        // Map iframe
        $html .= '<iframe class="tb4-fw-map-iframe" src="' . esc_attr($mapUrl) . '" style="width:100%;height:' . esc_attr($mapHeight) . ';border:0;display:block;' . $mapFilter . '" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';

        // Info box
        if ($showInfoBox) {
            $html .= '<div class="tb4-fw-map-info" style="position:absolute;' . $infoBoxPosStyle . 'background:' . esc_attr($infoBoxBg) . ';padding:' . esc_attr($infoBoxPadding) . ';border-radius:' . esc_attr($infoBoxRadius) . ';box-shadow:' . $shadowStyle . ';width:' . esc_attr($infoBoxWidth) . ';max-width:calc(100% - 64px);box-sizing:border-box;z-index:10;">';

            // Title
            $html .= '<h3 class="tb4-fw-map-title" style="font-size:' . esc_attr($titleFontSize) . ';font-weight:700;color:' . esc_attr($titleColor) . ';margin:0 0 20px 0;">' . esc_html($infoTitle) . '</h3>';

            // Address
            $html .= '<div class="tb4-fw-map-item" style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;">';
            $html .= '<span class="tb4-fw-map-item-icon" style="flex-shrink:0;color:' . esc_attr($iconColor) . ';margin-top:2px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>';
            $html .= '<span class="tb4-fw-map-item-content" style="font-size:' . esc_attr($textFontSize) . ';color:' . esc_attr($textColor) . ';line-height:1.5;">' . nl2br(esc_html($infoAddress)) . '</span>';
            $html .= '</div>';

            // Phone
            if ($infoPhone) {
                $html .= '<div class="tb4-fw-map-item" style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;">';
                $html .= '<span class="tb4-fw-map-item-icon" style="flex-shrink:0;color:' . esc_attr($iconColor) . ';margin-top:2px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>';
                $html .= '<span class="tb4-fw-map-item-content" style="font-size:' . esc_attr($textFontSize) . ';color:' . esc_attr($textColor) . ';line-height:1.5;">' . esc_html($infoPhone) . '</span>';
                $html .= '</div>';
            }

            // Email
            if ($infoEmail) {
                $html .= '<div class="tb4-fw-map-item" style="display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;">';
                $html .= '<span class="tb4-fw-map-item-icon" style="flex-shrink:0;color:' . esc_attr($iconColor) . ';margin-top:2px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>';
                $html .= '<span class="tb4-fw-map-item-content" style="font-size:' . esc_attr($textFontSize) . ';color:' . esc_attr($textColor) . ';line-height:1.5;">' . esc_html($infoEmail) . '</span>';
                $html .= '</div>';
            }

            // Divider and Hours
            if ($infoHours) {
                $html .= '<div class="tb4-fw-map-divider" style="height:1px;background:' . esc_attr($dividerColor) . ';margin:20px 0;"></div>';

                $html .= '<div class="tb4-fw-map-item" style="display:flex;align-items:flex-start;gap:12px;">';
                $html .= '<span class="tb4-fw-map-item-icon" style="flex-shrink:0;color:' . esc_attr($iconColor) . ';margin-top:2px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>';
                $html .= '<span class="tb4-fw-map-item-content" style="font-size:' . esc_attr($textFontSize) . ';color:' . esc_attr($textColor) . ';line-height:1.5;">' . nl2br(esc_html($infoHours)) . '</span>';
                $html .= '</div>';
            }

            // Directions button
            if ($showDirectionsBtn) {
                $html .= '<a href="' . esc_attr($directionsUrl) . '" target="_blank" rel="noopener noreferrer" class="tb4-fw-map-btn" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:' . esc_attr($buttonBgColor) . ';color:' . esc_attr($buttonTextColor) . ';text-decoration:none;border-radius:' . esc_attr($buttonBorderRadius) . ';font-size:14px;font-weight:600;margin-top:20px;transition:all 0.2s;">';
                $html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 19-9-9 19-2-8-8-2z"/></svg>';
                $html .= esc_html($directionsText) . '</a>';
            }

            $html .= '</div>'; // end info box
        }

        $html .= '</div></div>'; // end container and main

        return $html;
    }
}
