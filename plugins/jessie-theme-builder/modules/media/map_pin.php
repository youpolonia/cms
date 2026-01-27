<?php
/**
 * Map Pin Module (Child)
 * Single marker/pin on a map
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_MapPin extends JTB_Element
{
    public string $icon = 'map-pin';
    public string $category = 'media';
    public bool $is_child = true;

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = false;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'map_pin';
    }

    public function getName(): string
    {
        return 'Map Pin';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Pin Title',
                'type' => 'text',
                'default' => 'Location'
            ],
            'content' => [
                'label' => 'Info Window Content',
                'type' => 'textarea',
                'default' => 'Click for more info'
            ],
            'pin_address' => [
                'label' => 'Address',
                'type' => 'text',
                'default' => ''
            ],
            'pin_latitude' => [
                'label' => 'Latitude',
                'type' => 'text',
                'default' => '40.7128'
            ],
            'pin_longitude' => [
                'label' => 'Longitude',
                'type' => 'text',
                'default' => '-74.0060'
            ],
            'use_custom_icon' => [
                'label' => 'Use Custom Icon',
                'type' => 'toggle',
                'default' => false
            ],
            'custom_icon' => [
                'label' => 'Custom Marker Icon',
                'type' => 'upload',
                'default' => '',
                'show_if' => ['use_custom_icon' => true]
            ],
            'pin_color' => [
                'label' => 'Pin Color',
                'type' => 'color',
                'default' => '#ea4335',
                'show_if' => ['use_custom_icon' => false]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $title = $this->esc($attrs['title'] ?? 'Location');
        $infoContent = $attrs['content'] ?? '';
        $address = $this->esc($attrs['pin_address'] ?? '');
        $lat = floatval($attrs['pin_latitude'] ?? 40.7128);
        $lng = floatval($attrs['pin_longitude'] ?? -74.0060);
        $useCustomIcon = !empty($attrs['use_custom_icon']);
        $customIcon = $attrs['custom_icon'] ?? '';
        $pinColor = $attrs['pin_color'] ?? '#ea4335';

        // This renders as data for the parent map module to use
        $html = '<div class="jtb-map-pin" ';
        $html .= 'data-lat="' . $lat . '" ';
        $html .= 'data-lng="' . $lng . '" ';
        $html .= 'data-title="' . $title . '" ';
        $html .= 'data-address="' . $address . '" ';
        $html .= 'data-color="' . $this->esc($pinColor) . '" ';
        if ($useCustomIcon && $customIcon) {
            $html .= 'data-icon="' . $this->esc($customIcon) . '" ';
        }
        $html .= '>';

        // Info window content (hidden, used by JS)
        if (!empty($infoContent)) {
            $html .= '<div class="jtb-map-pin-content" style="display:none;">';
            $html .= '<strong>' . $title . '</strong>';
            if ($address) {
                $html .= '<br><small>' . $address . '</small>';
            }
            $html .= '<p>' . $infoContent . '</p>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        // Map pins don't need CSS - they're rendered by map library
        return '';
    }
}

JTB_Registry::register('map_pin', JTB_Module_MapPin::class);
