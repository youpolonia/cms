<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Icon Module
 * Displays decorative and functional icons using inline Lucide SVGs
 */
class IconModule extends Module
{
    /**
     * Lucide icon SVG paths (24x24 viewBox)
     * Each icon is an array with 'path' (d attribute) and optional 'elements' for complex icons
     */
    private array $lucide_icons = [];

    public function __construct()
    {
        $this->name = 'Icon';
        $this->slug = 'icon';
        $this->icon = 'Shapes';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-icon',
            'wrapper' => '.tb4-icon__wrapper',
            'svg' => '.tb4-icon__svg',
            'background' => '.tb4-icon__bg'
        ];

        // Initialize Lucide icon library
        $this->init_lucide_icons();
    }

    /**
     * Initialize Lucide icon SVG paths
     */
    private function init_lucide_icons(): void
    {
        $this->lucide_icons = [
            // Essential UI
            'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
            'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'minus' => '<path d="M5 12h14"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'menu' => '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>',
            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
            'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',

            // Arrows
            'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
            'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
            'arrow-up' => '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
            'arrow-down' => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
            'arrow-up-right' => '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>',
            'arrow-down-left' => '<path d="M17 7 7 17"/><path d="M17 17H7V7"/>',

            // Chevrons
            'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
            'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
            'chevron-up' => '<path d="m18 15-6-6-6 6"/>',
            'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
            'chevrons-right' => '<path d="m6 17 5-5-5-5"/><path d="m13 17 5-5-5-5"/>',
            'chevrons-left' => '<path d="m18 17-5-5 5-5"/><path d="m11 17-5-5 5-5"/>',

            // Communication
            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
            'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
            'message-circle' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',

            // Location
            'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
            'navigation' => '<polygon points="3 11 22 2 13 21 11 13 3 11"/>',
            'globe' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',

            // Actions
            'edit' => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
            'trash' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>',
            'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/>',
            'copy' => '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',

            // Links & External
            'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
            'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/>',

            // View & Display
            'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
            'eye-off' => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>',

            // Time & Calendar
            'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',

            // Organization
            'tag' => '<path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/>',
            'folder' => '<path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>',
            'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',

            // Media
            'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'video' => '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
            'music' => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
            'play' => '<polygon points="5 3 19 12 5 21 5 3"/>',
            'pause' => '<rect width="4" height="16" x="6" y="4"/><rect width="4" height="16" x="14" y="4"/>',

            // Notifications
            'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
            'bell-off' => '<path d="M8.7 3A6 6 0 0 1 18 8a21.3 21.3 0 0 0 .6 5"/><path d="M17 17H3s3-2 3-9a4.67 4.67 0 0 1 .3-1.7"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><line x1="2" x2="22" y1="2" y2="22"/>',

            // Security
            'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'unlock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',

            // Status
            'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            'x-circle' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
            'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
            'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
            'help-circle' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',

            // Social hints
            'thumbs-up' => '<path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"/>',
            'thumbs-down' => '<path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22h0a3.13 3.13 0 0 1-3-3.88Z"/>',

            // Misc
            'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
            'award' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
            'gift' => '<polyline points="20 12 20 22 4 22 4 12"/><rect width="20" height="5" x="2" y="7"/><line x1="12" x2="12" y1="22" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
            'coffee' => '<path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" x2="6" y1="2" y2="4"/><line x1="10" x2="10" y1="2" y2="4"/><line x1="14" x2="14" y1="2" y2="4"/>',
            'smile' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/>',
            'sparkles' => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>',
            'rocket' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
            'flame' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
            'lightbulb' => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
            'target' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'icon_name' => [
                'label' => 'Icon',
                'type' => 'select',
                'options' => $this->get_icon_options(),
                'default' => 'star'
            ],
            'link_url' => [
                'label' => 'Link URL',
                'type' => 'text',
                'default' => '',
                'description' => 'Optional - makes icon clickable'
            ],
            'link_target' => [
                'label' => 'Link Target',
                'type' => 'select',
                'options' => [
                    '_self' => 'Same Window',
                    '_blank' => 'New Window'
                ],
                'default' => '_self'
            ],
            'title' => [
                'label' => 'Title / Tooltip',
                'type' => 'text',
                'default' => '',
                'description' => 'Shown on hover, also used for accessibility'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'select',
                'options' => [
                    '16px' => '16px (Extra Small)',
                    '24px' => '24px (Small)',
                    '32px' => '32px (Medium)',
                    '48px' => '48px (Large)',
                    '64px' => '64px (Extra Large)',
                    '96px' => '96px (Huge)',
                    'custom' => 'Custom'
                ],
                'default' => '32px'
            ],
            'custom_size' => [
                'label' => 'Custom Size',
                'type' => 'text',
                'default' => '',
                'description' => 'e.g., 40px or 2.5rem'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'icon_color_hover' => [
                'label' => 'Icon Color (Hover)',
                'type' => 'color',
                'default' => ''
            ],
            'background_enabled' => [
                'label' => 'Show Background',
                'type' => 'toggle',
                'default' => false
            ],
            'background_shape' => [
                'label' => 'Background Shape',
                'type' => 'select',
                'options' => [
                    'circle' => 'Circle',
                    'square' => 'Square',
                    'rounded' => 'Rounded Square'
                ],
                'default' => 'circle'
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#f3f4f6'
            ],
            'background_color_hover' => [
                'label' => 'Background Color (Hover)',
                'type' => 'color',
                'default' => ''
            ],
            'background_size' => [
                'label' => 'Background Size',
                'type' => 'select',
                'options' => [
                    '1.5' => '1.5x Icon Size',
                    '2' => '2x Icon Size',
                    '2.5' => '2.5x Icon Size',
                    '3' => '3x Icon Size'
                ],
                'default' => '2'
            ],
            'border_enabled' => [
                'label' => 'Show Border',
                'type' => 'toggle',
                'default' => false
            ],
            'border_width' => [
                'label' => 'Border Width',
                'type' => 'text',
                'default' => '2px'
            ],
            'border_color' => [
                'label' => 'Border Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'border_color_hover' => [
                'label' => 'Border Color (Hover)',
                'type' => 'color',
                'default' => ''
            ],
            'alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'animation' => [
                'label' => 'Animation',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'spin' => 'Spin',
                    'pulse' => 'Pulse',
                    'bounce' => 'Bounce',
                    'shake' => 'Shake'
                ],
                'default' => 'none'
            ],
            'animation_on' => [
                'label' => 'Animate On',
                'type' => 'select',
                'options' => [
                    'always' => 'Always',
                    'hover' => 'On Hover'
                ],
                'default' => 'always'
            ],
            'rotate' => [
                'label' => 'Rotate',
                'type' => 'select',
                'options' => [
                    '0' => '0°',
                    '45' => '45°',
                    '90' => '90°',
                    '180' => '180°',
                    '270' => '270°'
                ],
                'default' => '0'
            ],
            'opacity' => [
                'label' => 'Opacity',
                'type' => 'select',
                'options' => [
                    '1' => '100%',
                    '0.9' => '90%',
                    '0.8' => '80%',
                    '0.7' => '70%',
                    '0.6' => '60%',
                    '0.5' => '50%',
                    '0.4' => '40%',
                    '0.3' => '30%',
                    '0.2' => '20%',
                    '0.1' => '10%'
                ],
                'default' => '1'
            ],
            'transition_duration' => [
                'label' => 'Transition Speed',
                'type' => 'select',
                'options' => [
                    '0.15s' => 'Fast (0.15s)',
                    '0.2s' => 'Normal (0.2s)',
                    '0.3s' => 'Slow (0.3s)',
                    '0.5s' => 'Very Slow (0.5s)'
                ],
                'default' => '0.2s'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Get icon options for select field
     */
    private function get_icon_options(): array
    {
        $options = [];
        foreach (array_keys($this->lucide_icons) as $name) {
            $label = ucwords(str_replace('-', ' ', $name));
            $options[$name] = $label;
        }
        return $options;
    }

    /**
     * Get SVG markup for an icon
     */
    private function get_icon_svg(string $name, string $size, string $color): string
    {
        $path = $this->lucide_icons[$name] ?? $this->lucide_icons['star'];

        return sprintf(
            '<svg class="tb4-icon__svg" xmlns="http://www.w3.org/2000/svg" width="%s" height="%s" viewBox="0 0 24 24" fill="none" stroke="%s" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
            esc_attr($size),
            esc_attr($size),
            esc_attr($color),
            $path
        );
    }

    public function render(array $settings): string
    {
        $iconName = $settings['icon_name'] ?? 'star';
        $linkUrl = $settings['link_url'] ?? '';
        $linkTarget = $settings['link_target'] ?? '_self';
        $title = $settings['title'] ?? '';

        // Size
        $iconSize = $settings['icon_size'] ?? '32px';
        if ($iconSize === 'custom') {
            $iconSize = $settings['custom_size'] ?? '32px';
        }

        // Colors
        $iconColor = $settings['icon_color'] ?? '#333333';
        $iconColorHover = $settings['icon_color_hover'] ?? '';

        // Background
        $bgEnabled = $settings['background_enabled'] ?? false;
        $bgShape = $settings['background_shape'] ?? 'circle';
        $bgColor = $settings['background_color'] ?? '#f3f4f6';
        $bgColorHover = $settings['background_color_hover'] ?? '';
        $bgSize = $settings['background_size'] ?? '2';

        // Border
        $borderEnabled = $settings['border_enabled'] ?? false;
        $borderWidth = $settings['border_width'] ?? '2px';
        $borderColor = $settings['border_color'] ?? '#e5e7eb';
        $borderColorHover = $settings['border_color_hover'] ?? '';

        // Layout & Animation
        $alignment = $settings['alignment'] ?? 'center';
        $animation = $settings['animation'] ?? 'none';
        $animationOn = $settings['animation_on'] ?? 'always';
        $rotate = $settings['rotate'] ?? '0';
        $opacity = $settings['opacity'] ?? '1';
        $transition = $settings['transition_duration'] ?? '0.2s';

        // Generate unique ID
        $uniqueId = 'tb4-icon-' . uniqid();

        // Calculate background size
        $bgSizeCalc = 'calc(' . $iconSize . ' * ' . $bgSize . ')';

        // Build wrapper styles
        $wrapperStyles = [
            'display:inline-flex',
            'align-items:center',
            'justify-content:center',
            'opacity:' . $opacity,
            'transition:all ' . $transition . ' ease'
        ];

        if ($bgEnabled) {
            $wrapperStyles[] = 'width:' . $bgSizeCalc;
            $wrapperStyles[] = 'height:' . $bgSizeCalc;
            $wrapperStyles[] = 'background-color:' . esc_attr($bgColor);

            // Border radius based on shape
            $borderRadius = match($bgShape) {
                'circle' => '50%',
                'rounded' => '12px',
                default => '0'
            };
            $wrapperStyles[] = 'border-radius:' . $borderRadius;

            if ($borderEnabled) {
                $wrapperStyles[] = 'border:' . esc_attr($borderWidth) . ' solid ' . esc_attr($borderColor);
            }
        }

        // Icon styles
        $iconStyles = [];
        if ($rotate !== '0') {
            $iconStyles[] = 'transform:rotate(' . $rotate . 'deg)';
        }

        // Container alignment
        $containerStyles = [
            'display:flex',
            'justify-content:' . match($alignment) {
                'left' => 'flex-start',
                'right' => 'flex-end',
                default => 'center'
            }
        ];

        // Animation class
        $animationClass = '';
        if ($animation !== 'none') {
            $animationClass = ' tb4-icon--' . $animation;
            if ($animationOn === 'hover') {
                $animationClass .= '-hover';
            }
        }

        // Build the SVG
        $svg = $this->get_icon_svg($iconName, $iconSize, $iconColor);

        // Wrap SVG with icon styles if needed
        if (!empty($iconStyles)) {
            $svg = '<span class="tb4-icon__inner" style="' . implode(';', $iconStyles) . '">' . $svg . '</span>';
        }

        // Build icon wrapper
        $wrapperAttrs = 'class="tb4-icon__wrapper' . $animationClass . '" style="' . implode(';', $wrapperStyles) . '"';
        if ($title) {
            $wrapperAttrs .= ' title="' . esc_attr($title) . '" aria-label="' . esc_attr($title) . '"';
        }

        $iconHtml = '<span ' . $wrapperAttrs . '>' . $svg . '</span>';

        // Wrap in link if URL provided
        if ($linkUrl) {
            $target = $linkTarget === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
            $iconHtml = '<a href="' . esc_attr($linkUrl) . '" class="tb4-icon__link"' . $target . '>' . $iconHtml . '</a>';
        }

        // Build final output
        $html = '<div id="' . esc_attr($uniqueId) . '" class="tb4-icon" style="' . implode(';', $containerStyles) . '">';
        $html .= $iconHtml;
        $html .= '</div>';

        // Add scoped CSS for hover states and animations
        $html .= $this->generate_scoped_css($uniqueId, $settings);

        // Add animation keyframes (once per page)
        $html .= $this->generate_animation_css();

        return $html;
    }

    /**
     * Generate scoped CSS for hover states
     */
    private function generate_scoped_css(string $uniqueId, array $settings): string
    {
        $css = [];
        $selector = '#' . $uniqueId;

        $iconColorHover = $settings['icon_color_hover'] ?? '';
        $bgColorHover = $settings['background_color_hover'] ?? '';
        $borderColorHover = $settings['border_color_hover'] ?? '';
        $bgEnabled = $settings['background_enabled'] ?? false;
        $borderEnabled = $settings['border_enabled'] ?? false;

        $hoverRules = [];

        if ($iconColorHover) {
            $hoverRules[] = 'stroke:' . esc_attr($iconColorHover);
        }

        if (!empty($hoverRules)) {
            $css[] = $selector . ':hover .tb4-icon__svg,' . $selector . ' a:hover .tb4-icon__svg { ' . implode(';', $hoverRules) . '; }';
        }

        $wrapperHoverRules = [];
        if ($bgEnabled && $bgColorHover) {
            $wrapperHoverRules[] = 'background-color:' . esc_attr($bgColorHover);
        }
        if ($borderEnabled && $borderColorHover) {
            $wrapperHoverRules[] = 'border-color:' . esc_attr($borderColorHover);
        }

        if (!empty($wrapperHoverRules)) {
            $css[] = $selector . ':hover .tb4-icon__wrapper,' . $selector . ' a:hover .tb4-icon__wrapper { ' . implode(';', $wrapperHoverRules) . '; }';
        }

        // Link reset styles
        $css[] = $selector . ' .tb4-icon__link { text-decoration:none;color:inherit;display:inline-flex; }';

        if (empty($css)) {
            return '';
        }

        return '<style>' . implode("\n", $css) . '</style>';
    }

    /**
     * Generate animation keyframes (only once per page)
     */
    private static bool $animationCssIncluded = false;

    private function generate_animation_css(): string
    {
        if (self::$animationCssIncluded) {
            return '';
        }
        self::$animationCssIncluded = true;

        return '<style>
/* TB4 Icon Animations */
@keyframes tb4-icon-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
@keyframes tb4-icon-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(0.95); }
}
@keyframes tb4-icon-bounce {
    0%, 100% { transform: translateY(0); }
    25% { transform: translateY(-6px); }
    75% { transform: translateY(2px); }
}
@keyframes tb4-icon-shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-3px); }
    40%, 80% { transform: translateX(3px); }
}

/* Always animate */
.tb4-icon--spin .tb4-icon__svg {
    animation: tb4-icon-spin 1s linear infinite;
}
.tb4-icon--pulse .tb4-icon__wrapper {
    animation: tb4-icon-pulse 1.5s ease-in-out infinite;
}
.tb4-icon--bounce .tb4-icon__wrapper {
    animation: tb4-icon-bounce 1s ease-in-out infinite;
}
.tb4-icon--shake .tb4-icon__wrapper {
    animation: tb4-icon-shake 0.5s ease-in-out infinite;
}

/* Animate on hover only */
.tb4-icon--spin-hover:hover .tb4-icon__svg,
.tb4-icon a:hover .tb4-icon--spin-hover .tb4-icon__svg {
    animation: tb4-icon-spin 1s linear infinite;
}
.tb4-icon--pulse-hover:hover,
.tb4-icon a:hover .tb4-icon--pulse-hover .tb4-icon__wrapper {
    animation: tb4-icon-pulse 1.5s ease-in-out infinite;
}
.tb4-icon--bounce-hover:hover .tb4-icon__wrapper,
.tb4-icon a:hover .tb4-icon--bounce-hover .tb4-icon__wrapper {
    animation: tb4-icon-bounce 1s ease-in-out infinite;
}
.tb4-icon--shake-hover:hover .tb4-icon__wrapper,
.tb4-icon a:hover .tb4-icon--shake-hover .tb4-icon__wrapper {
    animation: tb4-icon-shake 0.5s ease-in-out infinite;
}

/* Icon link focus styles */
.tb4-icon__link:focus {
    outline: 2px solid currentColor;
    outline-offset: 4px;
    border-radius: 4px;
}
.tb4-icon__link:focus-visible {
    outline: 2px solid #2563eb;
    outline-offset: 4px;
}
</style>';
    }
}
