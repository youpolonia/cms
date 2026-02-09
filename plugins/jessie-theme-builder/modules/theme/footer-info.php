<?php
/**
 * Footer Info Module
 * Displays contact info, address, hours etc. with icons
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Footer_Info extends JTB_Element
{
    public string $slug = 'footer_info';
    public string $name = 'Footer Info';
    public string $icon = 'info';
    public string $category = 'footer';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'footer_info';

    protected array $style_config = [
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-footer-info-title'
        ],
        'text_color' => [
            'property' => 'color',
            'selector' => '.jtb-info-text'
        ],
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-info-icon'
        ],
        'link_color' => [
            'property' => 'color',
            'selector' => '.jtb-info-link',
            'hover' => true
        ]
    ];

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Contact Info'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true
            ],
            'info_type' => [
                'label' => 'Info Type',
                'type' => 'select',
                'options' => [
                    'contact' => 'Contact Details',
                    'address' => 'Address',
                    'hours' => 'Business Hours',
                    'custom' => 'Custom'
                ],
                'default' => 'contact'
            ],
            // Contact Details
            'phone' => [
                'label' => 'Phone',
                'type' => 'text',
                'default' => '+1 (555) 123-4567',
                'condition' => ['info_type' => 'contact']
            ],
            'email' => [
                'label' => 'Email',
                'type' => 'text',
                'default' => 'info@example.com',
                'condition' => ['info_type' => 'contact']
            ],
            'fax' => [
                'label' => 'Fax',
                'type' => 'text',
                'default' => '',
                'condition' => ['info_type' => 'contact']
            ],
            // Address
            'address_line1' => [
                'label' => 'Address Line 1',
                'type' => 'text',
                'default' => '123 Main Street',
                'condition' => ['info_type' => 'address']
            ],
            'address_line2' => [
                'label' => 'Address Line 2',
                'type' => 'text',
                'default' => 'Suite 100',
                'condition' => ['info_type' => 'address']
            ],
            'city_state_zip' => [
                'label' => 'City, State ZIP',
                'type' => 'text',
                'default' => 'New York, NY 10001',
                'condition' => ['info_type' => 'address']
            ],
            'country' => [
                'label' => 'Country',
                'type' => 'text',
                'default' => 'United States',
                'condition' => ['info_type' => 'address']
            ],
            'map_url' => [
                'label' => 'Google Maps URL',
                'type' => 'text',
                'default' => '',
                'condition' => ['info_type' => 'address']
            ],
            // Business Hours
            'hours_format' => [
                'label' => 'Hours Format',
                'type' => 'select',
                'options' => [
                    'simple' => 'Simple (Mon-Fri: 9-5)',
                    'detailed' => 'Detailed (each day)'
                ],
                'default' => 'simple',
                'condition' => ['info_type' => 'hours']
            ],
            'weekday_hours' => [
                'label' => 'Weekday Hours',
                'type' => 'text',
                'default' => 'Mon - Fri: 9:00 AM - 5:00 PM',
                'condition' => ['info_type' => 'hours']
            ],
            'weekend_hours' => [
                'label' => 'Weekend Hours',
                'type' => 'text',
                'default' => 'Sat - Sun: Closed',
                'condition' => ['info_type' => 'hours']
            ],
            // Custom
            'custom_content' => [
                'label' => 'Custom Content',
                'type' => 'textarea',
                'description' => 'Enter custom HTML or text',
                'default' => '',
                'condition' => ['info_type' => 'custom']
            ],
            // Styling
            'show_icons' => [
                'label' => 'Show Icons',
                'type' => 'toggle',
                'default' => true
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#cccccc'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'range',
                'min' => 14,
                'max' => 28,
                'step' => 1,
                'default' => 18,
                'unit' => 'px'
            ],
            'item_spacing' => [
                'label' => 'Item Spacing',
                'type' => 'range',
                'min' => 8,
                'max' => 24,
                'step' => 2,
                'default' => 12,
                'unit' => 'px'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'footer_info_' . uniqid();
        $title = $attrs['title'] ?? 'Contact Info';
        $showTitle = $attrs['show_title'] ?? true;
        $infoType = $attrs['info_type'] ?? 'contact';
        $showIcons = $attrs['show_icons'] ?? true;

        // SVG icons
        $icons = [
            'phone' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>',
            'email' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
            'fax' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>',
            'location' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
            'clock' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>'
        ];

        $html = '<div id="' . $this->esc($id) . '" class="jtb-footer-info jtb-info-type-' . $this->esc($infoType) . '">';

        if ($showTitle && $title) {
            $html .= '<h4 class="jtb-footer-info-title">' . $this->esc($title) . '</h4>';
        }

        $html .= '<ul class="jtb-info-list">';

        if ($infoType === 'contact') {
            // Phone
            if (!empty($attrs['phone'])) {
                $phone = $attrs['phone'];
                $phoneClean = preg_replace('/[^0-9+]/', '', $phone);
                $html .= '<li class="jtb-info-item">';
                if ($showIcons) $html .= '<span class="jtb-info-icon">' . $icons['phone'] . '</span>';
                $html .= '<a href="tel:' . $this->esc($phoneClean) . '" class="jtb-info-link">' . $this->esc($phone) . '</a>';
                $html .= '</li>';
            }
            // Email
            if (!empty($attrs['email'])) {
                $html .= '<li class="jtb-info-item">';
                if ($showIcons) $html .= '<span class="jtb-info-icon">' . $icons['email'] . '</span>';
                $html .= '<a href="mailto:' . $this->esc($attrs['email']) . '" class="jtb-info-link">' . $this->esc($attrs['email']) . '</a>';
                $html .= '</li>';
            }
            // Fax
            if (!empty($attrs['fax'])) {
                $html .= '<li class="jtb-info-item">';
                if ($showIcons) $html .= '<span class="jtb-info-icon">' . $icons['fax'] . '</span>';
                $html .= '<span class="jtb-info-text">' . $this->esc($attrs['fax']) . '</span>';
                $html .= '</li>';
            }
        } elseif ($infoType === 'address') {
            $html .= '<li class="jtb-info-item jtb-address-item">';
            if ($showIcons) $html .= '<span class="jtb-info-icon">' . $icons['location'] . '</span>';
            $html .= '<address class="jtb-info-address">';

            $addressParts = [];
            if (!empty($attrs['address_line1'])) $addressParts[] = $this->esc($attrs['address_line1']);
            if (!empty($attrs['address_line2'])) $addressParts[] = $this->esc($attrs['address_line2']);
            if (!empty($attrs['city_state_zip'])) $addressParts[] = $this->esc($attrs['city_state_zip']);
            if (!empty($attrs['country'])) $addressParts[] = $this->esc($attrs['country']);

            $html .= implode('<br>', $addressParts);

            if (!empty($attrs['map_url'])) {
                $html .= '<br><a href="' . $this->esc($attrs['map_url']) . '" class="jtb-info-link jtb-map-link" target="_blank" rel="noopener noreferrer">View on Map →</a>';
            }

            $html .= '</address>';
            $html .= '</li>';
        } elseif ($infoType === 'hours') {
            if (!empty($attrs['weekday_hours'])) {
                $html .= '<li class="jtb-info-item">';
                if ($showIcons) $html .= '<span class="jtb-info-icon">' . $icons['clock'] . '</span>';
                $html .= '<span class="jtb-info-text">' . $this->esc($attrs['weekday_hours']) . '</span>';
                $html .= '</li>';
            }
            if (!empty($attrs['weekend_hours'])) {
                $html .= '<li class="jtb-info-item">';
                if ($showIcons) $html .= '<span class="jtb-info-icon"></span>';
                $html .= '<span class="jtb-info-text">' . $this->esc($attrs['weekend_hours']) . '</span>';
                $html .= '</li>';
            }
        } elseif ($infoType === 'custom') {
            $html .= '<li class="jtb-info-item jtb-custom-content">';
            $html .= $attrs['custom_content'] ?? '';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $textColor = $attrs['text_color'] ?? '#cccccc';
        $iconColor = $attrs['icon_color'] ?? '#2ea3f2';
        $linkColor = $attrs['link_color'] ?? '#2ea3f2';
        $linkHoverColor = $attrs['link_color__hover'] ?? '#ffffff';
        $iconSize = $attrs['icon_size'] ?? 18;
        $itemSpacing = $attrs['item_spacing'] ?? 12;

        // Title
        $css .= $selector . ' .jtb-footer-info-title { ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= 'font-size: 18px; ';
        $css .= 'font-weight: 600; ';
        $css .= 'margin: 0 0 16px 0; ';
        $css .= '}' . "\n";

        // List
        $css .= $selector . ' .jtb-info-list { ';
        $css .= 'list-style: none; ';
        $css .= 'margin: 0; ';
        $css .= 'padding: 0; ';
        $css .= 'display: flex; ';
        $css .= 'flex-direction: column; ';
        $css .= 'gap: ' . intval($itemSpacing) . 'px; ';
        $css .= '}' . "\n";

        // Item
        $css .= $selector . ' .jtb-info-item { ';
        $css .= 'display: flex; ';
        $css .= 'align-items: flex-start; ';
        $css .= 'gap: 10px; ';
        $css .= '}' . "\n";

        // Icon
        $css .= $selector . ' .jtb-info-icon { ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'flex-shrink: 0; ';
        $css .= 'width: ' . intval($iconSize) . 'px; ';
        $css .= 'height: ' . intval($iconSize) . 'px; ';
        $css .= 'margin-top: 2px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-info-icon svg { ';
        $css .= 'width: 100%; ';
        $css .= 'height: 100%; ';
        $css .= '}' . "\n";

        // Text
        $css .= $selector . ' .jtb-info-text { ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'font-size: 14px; ';
        $css .= 'line-height: 1.5; ';
        $css .= '}' . "\n";

        // Links
        $css .= $selector . ' .jtb-info-link { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'font-size: 14px; ';
        $css .= 'line-height: 1.5; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-info-link:hover { color: ' . $linkHoverColor . '; }' . "\n";

        // Address
        $css .= $selector . ' .jtb-info-address { ';
        $css .= 'font-style: normal; ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'font-size: 14px; ';
        $css .= 'line-height: 1.6; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-map-link { margin-top: 8px; display: inline-block; }' . "\n";

        return $css;
    }
}

JTB_Registry::register('footer_info', JTB_Module_Footer_Info::class);
