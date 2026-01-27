<?php
/**
 * Base Element Class
 * Abstract class for all JTB modules with field generators
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

abstract class JTB_Element
{
    // ========================================
    // Public Properties
    // ========================================

    public string $slug = '';
    public string $name = '';
    public string $icon = 'text';
    public string $category = 'content';

    // Feature Flags
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_typography = false;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = true;
    public bool $use_sizing = false;
    public bool $use_dividers = false;

    // Parent-Child
    public bool $is_child = false;
    public string $child_slug = '';

    // ========================================
    // Abstract Methods
    // ========================================

    abstract public function getSlug(): string;
    abstract public function getName(): string;
    abstract public function getFields(): array;
    abstract public function render(array $attrs, string $content = ''): string;

    // ========================================
    // Public Methods
    // ========================================

    public function getContentFields(): array
    {
        return $this->getFields();
    }

    public function getDesignFields(): array
    {
        $fields = [];

        if ($this->use_typography) {
            $fields['typography'] = [
                'label' => 'Typography',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getTypographyFields()
            ];
        }

        if ($this->use_spacing) {
            $fields['spacing'] = [
                'label' => 'Spacing',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getSpacingFields()
            ];
        }

        if ($this->use_border) {
            $fields['border'] = [
                'label' => 'Border',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getBorderFields()
            ];
        }

        if ($this->use_box_shadow) {
            $fields['box_shadow'] = [
                'label' => 'Box Shadow',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getBoxShadowFields()
            ];
        }

        if ($this->use_background) {
            $fields['background'] = [
                'label' => 'Background',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getBackgroundFields()
            ];
        }

        if ($this->use_filters) {
            $fields['filters'] = [
                'label' => 'Filters',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getFilterFields()
            ];
        }

        if ($this->use_transform) {
            $fields['transform'] = [
                'label' => 'Transform',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getTransformFields()
            ];
        }

        if ($this->use_animation) {
            $fields['animation'] = [
                'label' => 'Animation',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getAnimationFields()
            ];
        }

        if ($this->use_sizing) {
            $fields['sizing'] = [
                'label' => 'Sizing',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getSizingFields()
            ];
        }

        if ($this->use_dividers) {
            $fields['dividers'] = [
                'label' => 'Dividers',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getDividerFields()
            ];
        }

        return $fields;
    }

    public function getAdvancedFields(): array
    {
        $fields = [];

        // CSS ID & Classes
        $fields['css_id_classes'] = [
            'label' => 'CSS ID & Classes',
            'type' => 'group',
            'toggle' => true,
            'fields' => [
                'css_id' => [
                    'label' => 'CSS ID',
                    'type' => 'text',
                    'description' => 'Unique ID for this element'
                ],
                'css_class' => [
                    'label' => 'CSS Class',
                    'type' => 'text',
                    'description' => 'Additional CSS classes'
                ]
            ]
        ];

        // Custom CSS
        $fields['custom_css'] = [
            'label' => 'Custom CSS',
            'type' => 'group',
            'toggle' => true,
            'fields' => $this->getCustomCssFields()
        ];

        // Visibility
        $fields['visibility'] = [
            'label' => 'Visibility',
            'type' => 'group',
            'toggle' => true,
            'fields' => [
                'disable_on_desktop' => [
                    'label' => 'Disable on Desktop',
                    'type' => 'toggle',
                    'default' => false
                ],
                'disable_on_tablet' => [
                    'label' => 'Disable on Tablet',
                    'type' => 'toggle',
                    'default' => false
                ],
                'disable_on_phone' => [
                    'label' => 'Disable on Phone',
                    'type' => 'toggle',
                    'default' => false
                ]
            ]
        ];

        // Position (if enabled)
        if ($this->use_position) {
            $fields['position'] = [
                'label' => 'Position',
                'type' => 'group',
                'toggle' => true,
                'fields' => $this->getPositionFields()
            ];
        }

        return $fields;
    }

    // ========================================
    // Protected Field Generator Methods
    // ========================================

    protected function getTypographyFields(): array
    {
        return [
            'font_family' => [
                'label' => 'Font Family',
                'type' => 'select',
                'options' => $this->getFontOptions(),
                'responsive' => true
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 1,
                'max' => 100,
                'unit' => 'px',
                'responsive' => true
            ],
            'font_weight' => [
                'label' => 'Font Weight',
                'type' => 'select',
                'options' => [
                    '100' => 'Thin (100)',
                    '200' => 'Extra Light (200)',
                    '300' => 'Light (300)',
                    '400' => 'Regular (400)',
                    '500' => 'Medium (500)',
                    '600' => 'Semi Bold (600)',
                    '700' => 'Bold (700)',
                    '800' => 'Extra Bold (800)',
                    '900' => 'Black (900)'
                ],
                'responsive' => true
            ],
            'font_style' => [
                'label' => 'Font Style',
                'type' => 'select',
                'options' => [
                    'normal' => 'Normal',
                    'italic' => 'Italic'
                ]
            ],
            'text_transform' => [
                'label' => 'Text Transform',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'uppercase' => 'Uppercase',
                    'lowercase' => 'Lowercase',
                    'capitalize' => 'Capitalize'
                ]
            ],
            'text_decoration' => [
                'label' => 'Text Decoration',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'underline' => 'Underline',
                    'line-through' => 'Line Through'
                ]
            ],
            'line_height' => [
                'label' => 'Line Height',
                'type' => 'range',
                'min' => 0.5,
                'max' => 3,
                'step' => 0.1,
                'unit' => 'em',
                'responsive' => true
            ],
            'letter_spacing' => [
                'label' => 'Letter Spacing',
                'type' => 'range',
                'min' => -5,
                'max' => 20,
                'unit' => 'px',
                'responsive' => true
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'hover' => true
            ],
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justify' => 'Justify'
                ],
                'responsive' => true
            ],
            'text_shadow_style' => [
                'label' => 'Text Shadow',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'preset1' => 'Subtle',
                    'preset2' => 'Medium',
                    'preset3' => 'Strong',
                    'custom' => 'Custom'
                ]
            ],
            'text_shadow_horizontal' => [
                'label' => 'Horizontal Offset',
                'type' => 'range',
                'min' => -50,
                'max' => 50,
                'unit' => 'px',
                'default' => 0,
                'show_if' => ['text_shadow_style' => 'custom']
            ],
            'text_shadow_vertical' => [
                'label' => 'Vertical Offset',
                'type' => 'range',
                'min' => -50,
                'max' => 50,
                'unit' => 'px',
                'default' => 2,
                'show_if' => ['text_shadow_style' => 'custom']
            ],
            'text_shadow_blur' => [
                'label' => 'Blur',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 4,
                'show_if' => ['text_shadow_style' => 'custom']
            ],
            'text_shadow_color' => [
                'label' => 'Shadow Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)',
                'show_if' => ['text_shadow_style' => 'custom']
            ]
        ];
    }

    protected function getSpacingFields(): array
    {
        return [
            'margin' => [
                'label' => 'Margin',
                'type' => 'spacing',
                'responsive' => true,
                'sides' => ['top', 'right', 'bottom', 'left'],
                'unit' => 'px'
            ],
            'padding' => [
                'label' => 'Padding',
                'type' => 'spacing',
                'responsive' => true,
                'sides' => ['top', 'right', 'bottom', 'left'],
                'unit' => 'px'
            ]
        ];
    }

    protected function getBorderFields(): array
    {
        return [
            'border_width' => [
                'label' => 'Border Width',
                'type' => 'spacing',
                'sides' => ['top', 'right', 'bottom', 'left'],
                'unit' => 'px'
            ],
            'border_style' => [
                'label' => 'Border Style',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'solid' => 'Solid',
                    'dashed' => 'Dashed',
                    'dotted' => 'Dotted',
                    'double' => 'Double'
                ]
            ],
            'border_color' => [
                'label' => 'Border Color',
                'type' => 'color',
                'hover' => true
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'spacing',
                'sides' => ['top_left', 'top_right', 'bottom_right', 'bottom_left'],
                'unit' => 'px'
            ]
        ];
    }

    protected function getBoxShadowFields(): array
    {
        return [
            'box_shadow_style' => [
                'label' => 'Box Shadow',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'preset1' => 'Preset 1 (Light)',
                    'preset2' => 'Preset 2 (Medium)',
                    'preset3' => 'Preset 3 (Heavy)',
                    'custom' => 'Custom'
                ]
            ],
            'box_shadow_horizontal' => [
                'label' => 'Horizontal Offset',
                'type' => 'range',
                'min' => -100,
                'max' => 100,
                'unit' => 'px',
                'show_if' => ['box_shadow_style' => 'custom']
            ],
            'box_shadow_vertical' => [
                'label' => 'Vertical Offset',
                'type' => 'range',
                'min' => -100,
                'max' => 100,
                'unit' => 'px',
                'show_if' => ['box_shadow_style' => 'custom']
            ],
            'box_shadow_blur' => [
                'label' => 'Blur',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => 'px',
                'show_if' => ['box_shadow_style' => 'custom']
            ],
            'box_shadow_spread' => [
                'label' => 'Spread',
                'type' => 'range',
                'min' => -100,
                'max' => 100,
                'unit' => 'px',
                'show_if' => ['box_shadow_style' => 'custom']
            ],
            'box_shadow_color' => [
                'label' => 'Shadow Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)',
                'show_if' => ['box_shadow_style' => 'custom']
            ]
        ];
    }

    protected function getBackgroundFields(): array
    {
        return [
            'background_type' => [
                'label' => 'Background Type',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'color' => 'Color',
                    'gradient' => 'Gradient',
                    'image' => 'Image',
                    'video' => 'Video'
                ]
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'hover' => true,
                'show_if' => ['background_type' => 'color']
            ],
            'background_gradient_type' => [
                'label' => 'Gradient Type',
                'type' => 'select',
                'options' => [
                    'linear' => 'Linear',
                    'radial' => 'Radial',
                    'conic' => 'Conic'
                ],
                'show_if' => ['background_type' => 'gradient']
            ],
            'background_gradient_direction' => [
                'label' => 'Gradient Direction',
                'type' => 'range',
                'min' => 0,
                'max' => 360,
                'unit' => 'deg',
                'show_if' => ['background_type' => 'gradient']
            ],
            'background_gradient_stops' => [
                'label' => 'Gradient Stops',
                'type' => 'gradient',
                'default' => [
                    ['color' => '#ffffff', 'position' => 0],
                    ['color' => '#000000', 'position' => 100]
                ],
                'show_if' => ['background_type' => 'gradient']
            ],
            'background_image' => [
                'label' => 'Background Image',
                'type' => 'upload',
                'show_if' => ['background_type' => 'image']
            ],
            'background_size' => [
                'label' => 'Background Size',
                'type' => 'select',
                'options' => [
                    'cover' => 'Cover',
                    'contain' => 'Contain',
                    'auto' => 'Auto'
                ],
                'show_if' => ['background_type' => 'image']
            ],
            'background_position' => [
                'label' => 'Background Position',
                'type' => 'select',
                'options' => [
                    'top left' => 'Top Left',
                    'top center' => 'Top Center',
                    'top right' => 'Top Right',
                    'center left' => 'Center Left',
                    'center center' => 'Center',
                    'center right' => 'Center Right',
                    'bottom left' => 'Bottom Left',
                    'bottom center' => 'Bottom Center',
                    'bottom right' => 'Bottom Right'
                ],
                'show_if' => ['background_type' => 'image']
            ],
            'background_repeat' => [
                'label' => 'Background Repeat',
                'type' => 'select',
                'options' => [
                    'no-repeat' => 'No Repeat',
                    'repeat' => 'Repeat',
                    'repeat-x' => 'Repeat X',
                    'repeat-y' => 'Repeat Y'
                ],
                'show_if' => ['background_type' => 'image']
            ],
            'parallax' => [
                'label' => 'Parallax Effect',
                'type' => 'toggle',
                'default' => false,
                'show_if' => ['background_type' => 'image']
            ],
            'background_image_overlay' => [
                'label' => 'Image Overlay Color',
                'type' => 'color',
                'description' => 'Semi-transparent overlay on background image',
                'show_if' => ['background_type' => 'image']
            ],
            // Video Background
            'background_video_mp4' => [
                'label' => 'Video MP4 URL',
                'type' => 'url',
                'description' => 'URL to MP4 video file',
                'show_if' => ['background_type' => 'video']
            ],
            'background_video_webm' => [
                'label' => 'Video WebM URL',
                'type' => 'url',
                'description' => 'URL to WebM video file (optional)',
                'show_if' => ['background_type' => 'video']
            ],
            'background_video_poster' => [
                'label' => 'Video Poster Image',
                'type' => 'upload',
                'description' => 'Image shown before video loads',
                'show_if' => ['background_type' => 'video']
            ],
            'background_video_loop' => [
                'label' => 'Loop Video',
                'type' => 'toggle',
                'default' => true,
                'show_if' => ['background_type' => 'video']
            ],
            'background_video_muted' => [
                'label' => 'Mute Video',
                'type' => 'toggle',
                'default' => true,
                'show_if' => ['background_type' => 'video']
            ],
            'background_video_overlay' => [
                'label' => 'Video Overlay Color',
                'type' => 'color',
                'description' => 'Semi-transparent overlay on video',
                'show_if' => ['background_type' => 'video']
            ]
        ];
    }

    protected function getFilterFields(): array
    {
        return [
            'filter_hue_rotate' => [
                'label' => 'Hue Rotate',
                'type' => 'range',
                'min' => 0,
                'max' => 360,
                'unit' => 'deg',
                'default' => 0,
                'hover' => true
            ],
            'filter_saturate' => [
                'label' => 'Saturation',
                'type' => 'range',
                'min' => 0,
                'max' => 200,
                'unit' => '%',
                'default' => 100,
                'hover' => true
            ],
            'filter_brightness' => [
                'label' => 'Brightness',
                'type' => 'range',
                'min' => 0,
                'max' => 200,
                'unit' => '%',
                'default' => 100,
                'hover' => true
            ],
            'filter_contrast' => [
                'label' => 'Contrast',
                'type' => 'range',
                'min' => 0,
                'max' => 200,
                'unit' => '%',
                'default' => 100,
                'hover' => true
            ],
            'filter_invert' => [
                'label' => 'Invert',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 0,
                'hover' => true
            ],
            'filter_sepia' => [
                'label' => 'Sepia',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 0,
                'hover' => true
            ],
            'filter_blur' => [
                'label' => 'Blur',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 0,
                'hover' => true
            ]
        ];
    }

    protected function getTransformFields(): array
    {
        return [
            'transform_scale' => [
                'label' => 'Scale',
                'type' => 'range',
                'min' => 0,
                'max' => 200,
                'unit' => '%',
                'default' => 100,
                'hover' => true
            ],
            'transform_rotate' => [
                'label' => 'Rotate',
                'type' => 'range',
                'min' => -360,
                'max' => 360,
                'unit' => 'deg',
                'default' => 0,
                'hover' => true
            ],
            'transform_skew_x' => [
                'label' => 'Skew X',
                'type' => 'range',
                'min' => -60,
                'max' => 60,
                'unit' => 'deg',
                'default' => 0,
                'hover' => true
            ],
            'transform_skew_y' => [
                'label' => 'Skew Y',
                'type' => 'range',
                'min' => -60,
                'max' => 60,
                'unit' => 'deg',
                'default' => 0,
                'hover' => true
            ],
            'transform_translate_x' => [
                'label' => 'Translate X',
                'type' => 'range',
                'min' => -500,
                'max' => 500,
                'unit' => 'px',
                'responsive' => true,
                'hover' => true
            ],
            'transform_translate_y' => [
                'label' => 'Translate Y',
                'type' => 'range',
                'min' => -500,
                'max' => 500,
                'unit' => 'px',
                'responsive' => true,
                'hover' => true
            ],
            'transform_origin' => [
                'label' => 'Transform Origin',
                'type' => 'select',
                'options' => [
                    'top left' => 'Top Left',
                    'top center' => 'Top Center',
                    'top right' => 'Top Right',
                    'center left' => 'Center Left',
                    'center center' => 'Center',
                    'center right' => 'Center Right',
                    'bottom left' => 'Bottom Left',
                    'bottom center' => 'Bottom Center',
                    'bottom right' => 'Bottom Right'
                ]
            ]
        ];
    }

    protected function getAnimationFields(): array
    {
        return [
            'animation_style' => [
                'label' => 'Animation Style',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    // Fade animations
                    'fade' => 'Fade In',
                    'fadeUp' => 'Fade In Up',
                    'fadeDown' => 'Fade In Down',
                    'fadeLeft' => 'Fade In Left',
                    'fadeRight' => 'Fade In Right',
                    // Slide animations
                    'slideUp' => 'Slide Up',
                    'slideDown' => 'Slide Down',
                    'slideLeft' => 'Slide Left',
                    'slideRight' => 'Slide Right',
                    // Zoom animations
                    'zoomIn' => 'Zoom In',
                    'zoomOut' => 'Zoom Out',
                    'zoomInUp' => 'Zoom In Up',
                    'zoomInDown' => 'Zoom In Down',
                    // Bounce animations
                    'bounce' => 'Bounce',
                    'bounceIn' => 'Bounce In',
                    'bounceInUp' => 'Bounce In Up',
                    'bounceInDown' => 'Bounce In Down',
                    'bounceInLeft' => 'Bounce In Left',
                    'bounceInRight' => 'Bounce In Right',
                    // Flip animations
                    'flipInX' => 'Flip In X',
                    'flipInY' => 'Flip In Y',
                    // Rotate animations
                    'rotateIn' => 'Rotate In',
                    'rotateInUpLeft' => 'Rotate In Up Left',
                    'rotateInUpRight' => 'Rotate In Up Right',
                    'rotateInDownLeft' => 'Rotate In Down Left',
                    'rotateInDownRight' => 'Rotate In Down Right',
                    // Special animations
                    'roll' => 'Roll In',
                    'lightSpeedIn' => 'Light Speed In',
                    'pulse' => 'Pulse',
                    'shake' => 'Shake',
                    'swing' => 'Swing',
                    'tada' => 'Tada',
                    'wobble' => 'Wobble',
                    'heartBeat' => 'Heart Beat'
                ]
            ],
            'animation_direction' => [
                'label' => 'Animation Direction',
                'type' => 'select',
                'options' => [
                    'center' => 'Center',
                    'left' => 'Left',
                    'right' => 'Right',
                    'top' => 'Top',
                    'bottom' => 'Bottom'
                ],
                'show_if_not' => ['animation_style' => 'none']
            ],
            'animation_duration' => [
                'label' => 'Animation Duration',
                'type' => 'range',
                'min' => 0,
                'max' => 3000,
                'unit' => 'ms',
                'default' => 500,
                'show_if_not' => ['animation_style' => 'none']
            ],
            'animation_delay' => [
                'label' => 'Animation Delay',
                'type' => 'range',
                'min' => 0,
                'max' => 3000,
                'unit' => 'ms',
                'default' => 0,
                'show_if_not' => ['animation_style' => 'none']
            ],
            'animation_intensity' => [
                'label' => 'Animation Intensity',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 50,
                'show_if_not' => ['animation_style' => 'none']
            ],
            'animation_starting_opacity' => [
                'label' => 'Starting Opacity',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 0,
                'show_if_not' => ['animation_style' => 'none']
            ],
            'animation_repeat' => [
                'label' => 'Repeat Animation',
                'type' => 'toggle',
                'default' => false,
                'show_if_not' => ['animation_style' => 'none']
            ]
        ];
    }

    protected function getPositionFields(): array
    {
        return [
            'position_type' => [
                'label' => 'Position Type',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'relative' => 'Relative',
                    'absolute' => 'Absolute',
                    'fixed' => 'Fixed',
                    'sticky' => 'Sticky'
                ]
            ],
            // Sticky options
            'sticky_position' => [
                'label' => 'Stick To',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'bottom' => 'Bottom'
                ],
                'default' => 'top',
                'show_if' => ['position_type' => 'sticky']
            ],
            'sticky_offset' => [
                'label' => 'Sticky Offset',
                'type' => 'range',
                'min' => 0,
                'max' => 200,
                'unit' => 'px',
                'default' => 0,
                'show_if' => ['position_type' => 'sticky']
            ],
            'sticky_limit' => [
                'label' => 'Sticky Limit',
                'type' => 'select',
                'options' => [
                    'none' => 'None (Stick to end)',
                    'section' => 'Section',
                    'row' => 'Row',
                    'column' => 'Column'
                ],
                'default' => 'none',
                'show_if' => ['position_type' => 'sticky']
            ],
            'position_origin' => [
                'label' => 'Position Origin',
                'type' => 'select',
                'options' => [
                    'top left' => 'Top Left',
                    'top center' => 'Top Center',
                    'top right' => 'Top Right',
                    'center left' => 'Center Left',
                    'center center' => 'Center',
                    'center right' => 'Center Right',
                    'bottom left' => 'Bottom Left',
                    'bottom center' => 'Bottom Center',
                    'bottom right' => 'Bottom Right'
                ],
                'show_if_not' => ['position_type' => ['default', 'relative']]
            ],
            'position_vertical_offset' => [
                'label' => 'Vertical Offset',
                'type' => 'range',
                'min' => -1000,
                'max' => 1000,
                'unit' => 'px',
                'responsive' => true,
                'show_if_not' => ['position_type' => 'default']
            ],
            'position_horizontal_offset' => [
                'label' => 'Horizontal Offset',
                'type' => 'range',
                'min' => -1000,
                'max' => 1000,
                'unit' => 'px',
                'responsive' => true,
                'show_if_not' => ['position_type' => 'default']
            ],
            'z_index' => [
                'label' => 'Z-Index',
                'type' => 'range',
                'min' => -100,
                'max' => 1000,
                'responsive' => true
            ]
        ];
    }

    protected function getSizingFields(): array
    {
        return [
            'width' => [
                'label' => 'Width',
                'type' => 'text',
                'description' => 'e.g. 100%, 500px, auto',
                'responsive' => true
            ],
            'max_width' => [
                'label' => 'Max Width',
                'type' => 'text',
                'description' => 'e.g. 1200px, 100%',
                'responsive' => true
            ],
            'min_width' => [
                'label' => 'Min Width',
                'type' => 'text',
                'description' => 'e.g. 300px',
                'responsive' => true
            ],
            'height' => [
                'label' => 'Height',
                'type' => 'text',
                'description' => 'e.g. 400px, auto, 100vh',
                'responsive' => true
            ],
            'min_height' => [
                'label' => 'Min Height',
                'type' => 'text',
                'description' => 'e.g. 200px',
                'responsive' => true
            ],
            'max_height' => [
                'label' => 'Max Height',
                'type' => 'text',
                'description' => 'e.g. 500px',
                'responsive' => true
            ],
            'sizing_alignment' => [
                'label' => 'Module Alignment',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'responsive' => true
            ],
            'overflow_x' => [
                'label' => 'Overflow X',
                'type' => 'select',
                'options' => [
                    'visible' => 'Visible',
                    'hidden' => 'Hidden',
                    'scroll' => 'Scroll',
                    'auto' => 'Auto'
                ]
            ],
            'overflow_y' => [
                'label' => 'Overflow Y',
                'type' => 'select',
                'options' => [
                    'visible' => 'Visible',
                    'hidden' => 'Hidden',
                    'scroll' => 'Scroll',
                    'auto' => 'Auto'
                ]
            ]
        ];
    }

    protected function getDividerFields(): array
    {
        $dividerShapes = [
            'none' => 'None',
            'slant' => 'Slant',
            'slant2' => 'Slant 2',
            'arrow' => 'Arrow',
            'arrow2' => 'Arrow 2',
            'wave' => 'Wave',
            'wave2' => 'Wave 2',
            'waves' => 'Waves',
            'waves2' => 'Waves 2',
            'curve' => 'Curve',
            'curve2' => 'Curve 2',
            'asymmetric' => 'Asymmetric',
            'asymmetric2' => 'Asymmetric 2',
            'triangle' => 'Triangle',
            'triangle2' => 'Triangle 2',
            'mountains' => 'Mountains',
            'mountains2' => 'Mountains 2',
            'clouds' => 'Clouds',
            'clouds2' => 'Clouds 2',
            'zigzag' => 'Zigzag',
            'pyramids' => 'Pyramids',
            'ramp' => 'Ramp',
            'ramp2' => 'Ramp 2',
            'grass' => 'Grass',
            'drops' => 'Drops',
            'graph' => 'Graph'
        ];

        return [
            // Top Divider
            'divider_top_style' => [
                'label' => 'Top Divider Style',
                'type' => 'select',
                'options' => $dividerShapes
            ],
            'divider_top_color' => [
                'label' => 'Top Divider Color',
                'type' => 'color',
                'default' => '#ffffff',
                'show_if_not' => ['divider_top_style' => 'none']
            ],
            'divider_top_height' => [
                'label' => 'Top Divider Height',
                'type' => 'range',
                'min' => 0,
                'max' => 500,
                'unit' => 'px',
                'default' => 100,
                'responsive' => true,
                'show_if_not' => ['divider_top_style' => 'none']
            ],
            'divider_top_flip' => [
                'label' => 'Flip Top Divider',
                'type' => 'toggle',
                'default' => false,
                'show_if_not' => ['divider_top_style' => 'none']
            ],
            'divider_top_arrangement' => [
                'label' => 'Top Divider Arrangement',
                'type' => 'select',
                'options' => [
                    'above' => 'Above Content',
                    'below' => 'Below Content'
                ],
                'default' => 'below',
                'show_if_not' => ['divider_top_style' => 'none']
            ],
            // Bottom Divider
            'divider_bottom_style' => [
                'label' => 'Bottom Divider Style',
                'type' => 'select',
                'options' => $dividerShapes
            ],
            'divider_bottom_color' => [
                'label' => 'Bottom Divider Color',
                'type' => 'color',
                'default' => '#ffffff',
                'show_if_not' => ['divider_bottom_style' => 'none']
            ],
            'divider_bottom_height' => [
                'label' => 'Bottom Divider Height',
                'type' => 'range',
                'min' => 0,
                'max' => 500,
                'unit' => 'px',
                'default' => 100,
                'responsive' => true,
                'show_if_not' => ['divider_bottom_style' => 'none']
            ],
            'divider_bottom_flip' => [
                'label' => 'Flip Bottom Divider',
                'type' => 'toggle',
                'default' => false,
                'show_if_not' => ['divider_bottom_style' => 'none']
            ],
            'divider_bottom_arrangement' => [
                'label' => 'Bottom Divider Arrangement',
                'type' => 'select',
                'options' => [
                    'above' => 'Above Content',
                    'below' => 'Below Content'
                ],
                'default' => 'above',
                'show_if_not' => ['divider_bottom_style' => 'none']
            ]
        ];
    }

    protected function getCustomCssFields(): array
    {
        return [
            'before_module' => [
                'label' => 'Before Module',
                'type' => 'textarea',
                'rows' => 3,
                'description' => 'CSS applied before the module'
            ],
            'main_element' => [
                'label' => 'Main Element',
                'type' => 'textarea',
                'rows' => 3,
                'description' => 'CSS applied to the main element'
            ],
            'after_module' => [
                'label' => 'After Module',
                'type' => 'textarea',
                'rows' => 3,
                'description' => 'CSS applied after the module'
            ]
        ];
    }

    protected function getFontOptions(): array
    {
        return [
            // System Fonts
            '' => '-- System Fonts --',
            'inherit' => 'Inherit',
            'Arial, sans-serif' => 'Arial',
            'Helvetica, sans-serif' => 'Helvetica',
            'Georgia, serif' => 'Georgia',
            '"Times New Roman", serif' => 'Times New Roman',
            'Verdana, sans-serif' => 'Verdana',
            '"Courier New", monospace' => 'Courier New',
            'system-ui, sans-serif' => 'System UI',

            // Google Fonts - Sans Serif
            '_google_sans' => '-- Google Sans Serif --',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
            'Poppins' => 'Poppins',
            'Raleway' => 'Raleway',
            'Oswald' => 'Oswald',
            'Source Sans Pro' => 'Source Sans Pro',
            'Nunito' => 'Nunito',
            'Nunito Sans' => 'Nunito Sans',
            'Ubuntu' => 'Ubuntu',
            'Rubik' => 'Rubik',
            'Work Sans' => 'Work Sans',
            'Quicksand' => 'Quicksand',
            'Cabin' => 'Cabin',
            'Karla' => 'Karla',
            'Archivo' => 'Archivo',
            'Manrope' => 'Manrope',
            'Inter' => 'Inter',
            'DM Sans' => 'DM Sans',
            'Outfit' => 'Outfit',
            'Space Grotesk' => 'Space Grotesk',
            'Barlow' => 'Barlow',
            'Barlow Condensed' => 'Barlow Condensed',
            'Josefin Sans' => 'Josefin Sans',
            'Exo 2' => 'Exo 2',
            'Mukta' => 'Mukta',
            'Heebo' => 'Heebo',
            'Noto Sans' => 'Noto Sans',
            'Fira Sans' => 'Fira Sans',
            'PT Sans' => 'PT Sans',
            'Mulish' => 'Mulish',
            'Titillium Web' => 'Titillium Web',
            'Bebas Neue' => 'Bebas Neue',
            'Comfortaa' => 'Comfortaa',
            'Kanit' => 'Kanit',
            'Asap' => 'Asap',
            'Dosis' => 'Dosis',
            'Overpass' => 'Overpass',
            'Catamaran' => 'Catamaran',
            'Signika' => 'Signika',
            'Maven Pro' => 'Maven Pro',
            'Prompt' => 'Prompt',
            'Lexend' => 'Lexend',
            'Figtree' => 'Figtree',
            'Plus Jakarta Sans' => 'Plus Jakarta Sans',
            'Sora' => 'Sora',

            // Google Fonts - Serif
            '_google_serif' => '-- Google Serif --',
            'Playfair Display' => 'Playfair Display',
            'Merriweather' => 'Merriweather',
            'Lora' => 'Lora',
            'PT Serif' => 'PT Serif',
            'Noto Serif' => 'Noto Serif',
            'Libre Baskerville' => 'Libre Baskerville',
            'Crimson Text' => 'Crimson Text',
            'Cormorant Garamond' => 'Cormorant Garamond',
            'EB Garamond' => 'EB Garamond',
            'Source Serif Pro' => 'Source Serif Pro',
            'Bitter' => 'Bitter',
            'Arvo' => 'Arvo',
            'Vollkorn' => 'Vollkorn',
            'Cardo' => 'Cardo',
            'Frank Ruhl Libre' => 'Frank Ruhl Libre',
            'Spectral' => 'Spectral',
            'DM Serif Display' => 'DM Serif Display',
            'DM Serif Text' => 'DM Serif Text',
            'Fraunces' => 'Fraunces',

            // Google Fonts - Display / Decorative
            '_google_display' => '-- Google Display --',
            'Abril Fatface' => 'Abril Fatface',
            'Alfa Slab One' => 'Alfa Slab One',
            'Archivo Black' => 'Archivo Black',
            'Bangers' => 'Bangers',
            'Black Ops One' => 'Black Ops One',
            'Bungee' => 'Bungee',
            'Cinzel' => 'Cinzel',
            'Concert One' => 'Concert One',
            'Fredoka One' => 'Fredoka One',
            'Lobster' => 'Lobster',
            'Pacifico' => 'Pacifico',
            'Righteous' => 'Righteous',
            'Russo One' => 'Russo One',
            'Shadows Into Light' => 'Shadows Into Light',
            'Special Elite' => 'Special Elite',
            'Staatliches' => 'Staatliches',
            'Teko' => 'Teko',
            'Titan One' => 'Titan One',
            'Permanent Marker' => 'Permanent Marker',

            // Google Fonts - Handwriting
            '_google_handwriting' => '-- Google Handwriting --',
            'Dancing Script' => 'Dancing Script',
            'Great Vibes' => 'Great Vibes',
            'Satisfy' => 'Satisfy',
            'Sacramento' => 'Sacramento',
            'Caveat' => 'Caveat',
            'Indie Flower' => 'Indie Flower',
            'Kaushan Script' => 'Kaushan Script',
            'Courgette' => 'Courgette',
            'Handlee' => 'Handlee',
            'Patrick Hand' => 'Patrick Hand',

            // Google Fonts - Monospace
            '_google_mono' => '-- Google Monospace --',
            'Roboto Mono' => 'Roboto Mono',
            'Source Code Pro' => 'Source Code Pro',
            'Fira Code' => 'Fira Code',
            'JetBrains Mono' => 'JetBrains Mono',
            'IBM Plex Mono' => 'IBM Plex Mono',
            'Space Mono' => 'Space Mono',
            'Inconsolata' => 'Inconsolata',
            'Ubuntu Mono' => 'Ubuntu Mono'
        ];
    }

    /**
     * Get list of Google Fonts from font family value
     */
    public static function getGoogleFontsFromValue(string $fontFamily): ?string
    {
        $systemFonts = ['Arial', 'Helvetica', 'Georgia', 'Times New Roman', 'Verdana', 'Courier New', 'system-ui', 'inherit', ''];

        // Remove CSS fallbacks
        $fontName = explode(',', $fontFamily)[0];
        $fontName = trim($fontName, '"\'');

        if (empty($fontName) || in_array($fontName, $systemFonts) || strpos($fontName, '_') === 0) {
            return null;
        }

        return $fontName;
    }

    /**
     * Get Google Fonts URL for given fonts array
     */
    public static function getGoogleFontsUrl(array $fonts): string
    {
        if (empty($fonts)) {
            return '';
        }

        $families = [];
        foreach ($fonts as $font) {
            // Include all weights for flexibility
            $families[] = urlencode($font) . ':wght@100;200;300;400;500;600;700;800;900';
        }

        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
    }

    // ========================================
    // Helper Methods
    // ========================================

    protected function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected function renderWrapper(string $content, array $attrs): string
    {
        $classes = ['jtb-module', 'jtb-module-' . $this->getSlug()];

        // Add custom CSS class
        if (!empty($attrs['css_class'])) {
            $classes[] = $this->esc($attrs['css_class']);
        }

        // Add visibility classes
        if (!empty($attrs['disable_on_desktop'])) {
            $classes[] = 'jtb-hide-desktop';
        }
        if (!empty($attrs['disable_on_tablet'])) {
            $classes[] = 'jtb-hide-tablet';
        }
        if (!empty($attrs['disable_on_phone'])) {
            $classes[] = 'jtb-hide-phone';
        }

        // Add animation classes
        if (!empty($attrs['animation_style']) && $attrs['animation_style'] !== 'none') {
            $classes[] = 'jtb-animated';
            $classes[] = 'jtb-animation-' . $this->esc($attrs['animation_style']);

            if (!empty($attrs['animation_direction'])) {
                $classes[] = 'jtb-animation-' . $this->esc($attrs['animation_direction']);
            }
        }

        $classStr = implode(' ', $classes);
        $idAttr = !empty($attrs['css_id']) ? ' id="' . $this->esc($attrs['css_id']) . '"' : '';

        // Animation data attributes
        $dataAttrs = '';
        if (!empty($attrs['animation_style']) && $attrs['animation_style'] !== 'none') {
            $dataAttrs .= ' data-animation-duration="' . ($attrs['animation_duration'] ?? 500) . '"';
            $dataAttrs .= ' data-animation-delay="' . ($attrs['animation_delay'] ?? 0) . '"';
            $dataAttrs .= ' data-animation-intensity="' . ($attrs['animation_intensity'] ?? 50) . '"';
            $dataAttrs .= ' data-animation-starting-opacity="' . ($attrs['animation_starting_opacity'] ?? 0) . '"';
            if (!empty($attrs['animation_repeat'])) {
                $dataAttrs .= ' data-animation-repeat="true"';
            }
        }

        return '<div class="' . $classStr . '"' . $idAttr . $dataAttrs . '>' . $content . '</div>';
    }

    public function generateId(): string
    {
        return $this->getSlug() . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Background
        $css .= $this->generateBackgroundCss($attrs, $selector);

        // Spacing
        $css .= $this->generateSpacingCss($attrs, $selector);

        // Border
        $css .= $this->generateBorderCss($attrs, $selector);

        // Box Shadow
        $css .= $this->generateBoxShadowCss($attrs, $selector);

        // Typography (if enabled)
        if ($this->use_typography) {
            $css .= $this->generateTypographyCss($attrs, $selector);
        }

        // Filters
        $css .= $this->generateFiltersCss($attrs, $selector);

        // Transform
        $css .= $this->generateTransformCss($attrs, $selector);

        // Position
        if ($this->use_position) {
            $css .= $this->generatePositionCss($attrs, $selector);
        }

        // Sizing
        if ($this->use_sizing) {
            $css .= $this->generateSizingCss($attrs, $selector);
        }

        // Dividers
        if ($this->use_dividers) {
            $css .= $this->generateDividerCss($attrs, $selector);
        }

        // Custom CSS
        $css .= $this->generateCustomCss($attrs, $selector);

        // Responsive CSS
        $css .= $this->generateResponsiveCss($attrs, $selector, 'tablet', 980);
        $css .= $this->generateResponsiveCss($attrs, $selector, 'phone', 767);

        return $css;
    }

    protected function generateBackgroundCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        $bgType = $attrs['background_type'] ?? 'none';

        if ($bgType === 'color' && !empty($attrs['background_color'])) {
            $rules[] = 'background-color: ' . $attrs['background_color'];
        } elseif ($bgType === 'gradient') {
            $type = $attrs['background_gradient_type'] ?? 'linear';
            $direction = $attrs['background_gradient_direction'] ?? 180;

            // Check for multi-stop gradient
            if (!empty($attrs['background_gradient_stops']) && is_array($attrs['background_gradient_stops'])) {
                $stops = $attrs['background_gradient_stops'];
                $stopStrings = [];
                foreach ($stops as $stop) {
                    $color = $stop['color'] ?? '#ffffff';
                    $position = $stop['position'] ?? 0;
                    $stopStrings[] = "{$color} {$position}%";
                }
                $stopsStr = implode(', ', $stopStrings);

                if ($type === 'linear') {
                    $rules[] = "background: linear-gradient({$direction}deg, {$stopsStr})";
                } elseif ($type === 'radial') {
                    $rules[] = "background: radial-gradient(circle, {$stopsStr})";
                } elseif ($type === 'conic') {
                    $rules[] = "background: conic-gradient(from {$direction}deg, {$stopsStr})";
                }
            } else {
                // Fallback to simple two-color gradient
                $start = $attrs['background_gradient_start'] ?? '#ffffff';
                $end = $attrs['background_gradient_end'] ?? '#000000';

                if ($type === 'linear') {
                    $rules[] = "background: linear-gradient({$direction}deg, {$start}, {$end})";
                } elseif ($type === 'radial') {
                    $rules[] = "background: radial-gradient(circle, {$start}, {$end})";
                } elseif ($type === 'conic') {
                    $rules[] = "background: conic-gradient(from {$direction}deg, {$start}, {$end})";
                }
            }
        } elseif ($bgType === 'image' && !empty($attrs['background_image'])) {
            $rules[] = 'background-image: url(' . $attrs['background_image'] . ')';
            $rules[] = 'background-size: ' . ($attrs['background_size'] ?? 'cover');
            $rules[] = 'background-position: ' . ($attrs['background_position'] ?? 'center center');
            $rules[] = 'background-repeat: ' . ($attrs['background_repeat'] ?? 'no-repeat');

            if (!empty($attrs['parallax'])) {
                $rules[] = 'background-attachment: fixed';
            }

            // Image overlay needs position relative
            if (!empty($attrs['background_image_overlay'])) {
                $rules[] = 'position: relative';
            }
        } elseif ($bgType === 'video') {
            // Video background requires position relative for overlay
            $rules[] = 'position: relative';
            $rules[] = 'overflow: hidden';
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        // Video background styling
        if ($bgType === 'video') {
            $css .= $selector . ' > .jtb-video-background { position: absolute; top: 50%; left: 50%; min-width: 100%; min-height: 100%; width: auto; height: auto; transform: translate(-50%, -50%); z-index: 0; object-fit: cover; }' . "\n";

            // Video overlay
            if (!empty($attrs['background_video_overlay'])) {
                $css .= $selector . ' > .jtb-video-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: ' . $attrs['background_video_overlay'] . '; z-index: 1; }' . "\n";
            }

            // Content above video
            $css .= $selector . ' > .jtb-content { position: relative; z-index: 2; }' . "\n";
        }

        // Image background overlay using ::before pseudo-element
        if ($bgType === 'image' && !empty($attrs['background_image_overlay'])) {
            $css .= $selector . '::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: ' . $attrs['background_image_overlay'] . '; z-index: 1; pointer-events: none; }' . "\n";
            $css .= $selector . ' > .jtb-section-inner, ' . $selector . ' > .jtb-content { position: relative; z-index: 2; }' . "\n";
        }

        // Hover state
        if ($bgType === 'color' && !empty($attrs['background_color__hover'])) {
            $css .= $selector . ':hover { background-color: ' . $attrs['background_color__hover'] . '; }' . "\n";
        }

        // Gradient hover state
        if ($bgType === 'gradient') {
            $hasHoverGradient = !empty($attrs['background_gradient_start__hover']) || !empty($attrs['background_gradient_end__hover']);
            if ($hasHoverGradient) {
                $type = $attrs['background_gradient_type'] ?? 'linear';
                $direction = $attrs['background_gradient_direction__hover'] ?? $attrs['background_gradient_direction'] ?? 180;
                $start = $attrs['background_gradient_start__hover'] ?? $attrs['background_gradient_start'] ?? '#ffffff';
                $end = $attrs['background_gradient_end__hover'] ?? $attrs['background_gradient_end'] ?? '#000000';

                if ($type === 'linear') {
                    $css .= $selector . ":hover { background: linear-gradient({$direction}deg, {$start}, {$end}); }\n";
                } elseif ($type === 'radial') {
                    $css .= $selector . ":hover { background: radial-gradient(circle, {$start}, {$end}); }\n";
                }
            }
        }

        return $css;
    }

    protected function generateSpacingCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        // Margin
        if (!empty($attrs['margin'])) {
            $margin = $attrs['margin'];
            if (is_array($margin)) {
                $rules[] = 'margin: ' . ($margin['top'] ?? 0) . 'px ' . ($margin['right'] ?? 0) . 'px ' . ($margin['bottom'] ?? 0) . 'px ' . ($margin['left'] ?? 0) . 'px';
            }
        }

        // Padding
        if (!empty($attrs['padding'])) {
            $padding = $attrs['padding'];
            if (is_array($padding)) {
                $rules[] = 'padding: ' . ($padding['top'] ?? 0) . 'px ' . ($padding['right'] ?? 0) . 'px ' . ($padding['bottom'] ?? 0) . 'px ' . ($padding['left'] ?? 0) . 'px';
            }
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        return $css;
    }

    protected function generateBorderCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        // Border width
        if (!empty($attrs['border_width'])) {
            $width = $attrs['border_width'];
            if (is_array($width)) {
                $rules[] = 'border-width: ' . ($width['top'] ?? 0) . 'px ' . ($width['right'] ?? 0) . 'px ' . ($width['bottom'] ?? 0) . 'px ' . ($width['left'] ?? 0) . 'px';
            }
        }

        // Border style
        if (!empty($attrs['border_style']) && $attrs['border_style'] !== 'none') {
            $rules[] = 'border-style: ' . $attrs['border_style'];
        }

        // Border color
        if (!empty($attrs['border_color'])) {
            $rules[] = 'border-color: ' . $attrs['border_color'];
        }

        // Border radius
        if (!empty($attrs['border_radius'])) {
            $radius = $attrs['border_radius'];
            if (is_array($radius)) {
                $rules[] = 'border-radius: ' . ($radius['top_left'] ?? 0) . 'px ' . ($radius['top_right'] ?? 0) . 'px ' . ($radius['bottom_right'] ?? 0) . 'px ' . ($radius['bottom_left'] ?? 0) . 'px';
            }
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        // Hover state
        if (!empty($attrs['border_color__hover'])) {
            $css .= $selector . ':hover { border-color: ' . $attrs['border_color__hover'] . '; }' . "\n";
        }

        return $css;
    }

    protected function generateBoxShadowCss(array $attrs, string $selector): string
    {
        $css = '';
        $style = $attrs['box_shadow_style'] ?? 'none';

        if ($style === 'none') {
            return $css;
        }

        $shadow = '';

        if ($style === 'preset1') {
            $shadow = '0 2px 4px rgba(0,0,0,0.1)';
        } elseif ($style === 'preset2') {
            $shadow = '0 4px 12px rgba(0,0,0,0.15)';
        } elseif ($style === 'preset3') {
            $shadow = '0 8px 24px rgba(0,0,0,0.2)';
        } elseif ($style === 'custom') {
            $h = $attrs['box_shadow_horizontal'] ?? 0;
            $v = $attrs['box_shadow_vertical'] ?? 0;
            $blur = $attrs['box_shadow_blur'] ?? 0;
            $spread = $attrs['box_shadow_spread'] ?? 0;
            $color = $attrs['box_shadow_color'] ?? 'rgba(0,0,0,0.3)';
            $shadow = "{$h}px {$v}px {$blur}px {$spread}px {$color}";
        }

        if (!empty($shadow)) {
            $css .= $selector . ' { box-shadow: ' . $shadow . '; }' . "\n";
        }

        return $css;
    }

    protected function generateTypographyCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        if (!empty($attrs['font_family'])) {
            $rules[] = "font-family: '{$attrs['font_family']}', sans-serif";
        }

        if (!empty($attrs['font_size'])) {
            $rules[] = 'font-size: ' . $attrs['font_size'] . 'px';
        }

        if (!empty($attrs['font_weight'])) {
            $rules[] = 'font-weight: ' . $attrs['font_weight'];
        }

        if (!empty($attrs['font_style'])) {
            $rules[] = 'font-style: ' . $attrs['font_style'];
        }

        if (!empty($attrs['text_transform'])) {
            $rules[] = 'text-transform: ' . $attrs['text_transform'];
        }

        if (!empty($attrs['text_decoration'])) {
            $rules[] = 'text-decoration: ' . $attrs['text_decoration'];
        }

        if (!empty($attrs['line_height'])) {
            $rules[] = 'line-height: ' . $attrs['line_height'] . 'em';
        }

        if (!empty($attrs['letter_spacing'])) {
            $rules[] = 'letter-spacing: ' . $attrs['letter_spacing'] . 'px';
        }

        if (!empty($attrs['text_color'])) {
            $rules[] = 'color: ' . $attrs['text_color'];
        }

        if (!empty($attrs['text_align'])) {
            $rules[] = 'text-align: ' . $attrs['text_align'];
        }

        // Text Shadow
        $textShadowStyle = $attrs['text_shadow_style'] ?? 'none';
        if ($textShadowStyle !== 'none') {
            $textShadow = '';
            if ($textShadowStyle === 'preset1') {
                $textShadow = '0 1px 2px rgba(0,0,0,0.15)';
            } elseif ($textShadowStyle === 'preset2') {
                $textShadow = '0 2px 4px rgba(0,0,0,0.2)';
            } elseif ($textShadowStyle === 'preset3') {
                $textShadow = '0 4px 8px rgba(0,0,0,0.3)';
            } elseif ($textShadowStyle === 'custom') {
                $h = $attrs['text_shadow_horizontal'] ?? 0;
                $v = $attrs['text_shadow_vertical'] ?? 2;
                $blur = $attrs['text_shadow_blur'] ?? 4;
                $color = $attrs['text_shadow_color'] ?? 'rgba(0,0,0,0.3)';
                $textShadow = "{$h}px {$v}px {$blur}px {$color}";
            }
            if (!empty($textShadow)) {
                $rules[] = 'text-shadow: ' . $textShadow;
            }
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        // Hover state
        if (!empty($attrs['text_color__hover'])) {
            $css .= $selector . ':hover { color: ' . $attrs['text_color__hover'] . '; }' . "\n";
        }

        return $css;
    }

    protected function generateFiltersCss(array $attrs, string $selector): string
    {
        $css = '';
        $filters = [];
        $hoverFilters = [];

        $filterProps = ['hue_rotate', 'saturate', 'brightness', 'contrast', 'invert', 'sepia', 'blur'];
        $defaults = [0, 100, 100, 100, 0, 0, 0];

        foreach ($filterProps as $i => $prop) {
            $key = 'filter_' . $prop;
            $hoverKey = $key . '__hover';

            if (isset($attrs[$key]) && $attrs[$key] != $defaults[$i]) {
                $value = $attrs[$key];
                $unit = ($prop === 'hue_rotate') ? 'deg' : (($prop === 'blur') ? 'px' : '%');
                $funcName = str_replace('_', '-', $prop);
                if ($funcName === 'hue-rotate') {
                    $funcName = 'hue-rotate';
                }
                $filters[] = "{$funcName}({$value}{$unit})";
            }

            if (isset($attrs[$hoverKey])) {
                $value = $attrs[$hoverKey];
                $unit = ($prop === 'hue_rotate') ? 'deg' : (($prop === 'blur') ? 'px' : '%');
                $funcName = str_replace('_', '-', $prop);
                if ($funcName === 'hue-rotate') {
                    $funcName = 'hue-rotate';
                }
                $hoverFilters[] = "{$funcName}({$value}{$unit})";
            }
        }

        if (!empty($filters)) {
            $css .= $selector . ' { filter: ' . implode(' ', $filters) . '; }' . "\n";
        }

        if (!empty($hoverFilters)) {
            $css .= $selector . ':hover { filter: ' . implode(' ', $hoverFilters) . '; }' . "\n";
        }

        return $css;
    }

    protected function generateTransformCss(array $attrs, string $selector): string
    {
        $css = '';
        $transforms = [];
        $hoverTransforms = [];

        // Scale
        if (isset($attrs['transform_scale']) && $attrs['transform_scale'] != 100) {
            $transforms[] = 'scale(' . ($attrs['transform_scale'] / 100) . ')';
        }
        if (isset($attrs['transform_scale__hover'])) {
            $hoverTransforms[] = 'scale(' . ($attrs['transform_scale__hover'] / 100) . ')';
        }

        // Rotate
        if (isset($attrs['transform_rotate']) && $attrs['transform_rotate'] != 0) {
            $transforms[] = 'rotate(' . $attrs['transform_rotate'] . 'deg)';
        }
        if (isset($attrs['transform_rotate__hover'])) {
            $hoverTransforms[] = 'rotate(' . $attrs['transform_rotate__hover'] . 'deg)';
        }

        // Skew
        if (isset($attrs['transform_skew_x']) && $attrs['transform_skew_x'] != 0) {
            $transforms[] = 'skewX(' . $attrs['transform_skew_x'] . 'deg)';
        }
        if (isset($attrs['transform_skew_y']) && $attrs['transform_skew_y'] != 0) {
            $transforms[] = 'skewY(' . $attrs['transform_skew_y'] . 'deg)';
        }

        // Translate
        if (isset($attrs['transform_translate_x']) && $attrs['transform_translate_x'] != 0) {
            $transforms[] = 'translateX(' . $attrs['transform_translate_x'] . 'px)';
        }
        if (isset($attrs['transform_translate_y']) && $attrs['transform_translate_y'] != 0) {
            $transforms[] = 'translateY(' . $attrs['transform_translate_y'] . 'px)';
        }

        $rules = [];

        if (!empty($transforms)) {
            $rules[] = 'transform: ' . implode(' ', $transforms);
        }

        if (!empty($attrs['transform_origin'])) {
            $rules[] = 'transform-origin: ' . $attrs['transform_origin'];
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        if (!empty($hoverTransforms)) {
            $css .= $selector . ':hover { transform: ' . implode(' ', $hoverTransforms) . '; }' . "\n";
        }

        return $css;
    }

    protected function generatePositionCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        $posType = $attrs['position_type'] ?? 'default';

        if ($posType !== 'default') {
            $rules[] = 'position: ' . $posType;

            if (isset($attrs['z_index'])) {
                $rules[] = 'z-index: ' . $attrs['z_index'];
            }

            // Sticky position handling
            if ($posType === 'sticky') {
                $stickyPos = $attrs['sticky_position'] ?? 'top';
                $stickyOffset = $attrs['sticky_offset'] ?? 0;
                $rules[] = $stickyPos . ': ' . $stickyOffset . 'px';
            } elseif ($posType !== 'relative') {
                $origin = $attrs['position_origin'] ?? 'top left';
                $vOffset = $attrs['position_vertical_offset'] ?? 0;
                $hOffset = $attrs['position_horizontal_offset'] ?? 0;

                $originParts = explode(' ', $origin);
                $vertical = $originParts[0] ?? 'top';
                $horizontal = $originParts[1] ?? 'left';

                $rules[] = $vertical . ': ' . $vOffset . 'px';
                $rules[] = $horizontal . ': ' . $hOffset . 'px';
            } else {
                if (isset($attrs['position_vertical_offset'])) {
                    $rules[] = 'top: ' . $attrs['position_vertical_offset'] . 'px';
                }
                if (isset($attrs['position_horizontal_offset'])) {
                    $rules[] = 'left: ' . $attrs['position_horizontal_offset'] . 'px';
                }
            }
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        return $css;
    }

    protected function generateSizingCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];

        // Width
        if (!empty($attrs['width'])) {
            $rules[] = 'width: ' . $attrs['width'];
        }
        if (!empty($attrs['max_width'])) {
            $rules[] = 'max-width: ' . $attrs['max_width'];
        }
        if (!empty($attrs['min_width'])) {
            $rules[] = 'min-width: ' . $attrs['min_width'];
        }

        // Height
        if (!empty($attrs['height'])) {
            $rules[] = 'height: ' . $attrs['height'];
        }
        if (!empty($attrs['min_height'])) {
            $rules[] = 'min-height: ' . $attrs['min_height'];
        }
        if (!empty($attrs['max_height'])) {
            $rules[] = 'max-height: ' . $attrs['max_height'];
        }

        // Alignment (uses margin auto)
        $alignment = $attrs['sizing_alignment'] ?? 'default';
        if ($alignment === 'center') {
            $rules[] = 'margin-left: auto';
            $rules[] = 'margin-right: auto';
        } elseif ($alignment === 'right') {
            $rules[] = 'margin-left: auto';
            $rules[] = 'margin-right: 0';
        } elseif ($alignment === 'left') {
            $rules[] = 'margin-left: 0';
            $rules[] = 'margin-right: auto';
        }

        // Overflow
        if (!empty($attrs['overflow_x']) && $attrs['overflow_x'] !== 'visible') {
            $rules[] = 'overflow-x: ' . $attrs['overflow_x'];
        }
        if (!empty($attrs['overflow_y']) && $attrs['overflow_y'] !== 'visible') {
            $rules[] = 'overflow-y: ' . $attrs['overflow_y'];
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        return $css;
    }

    protected function generateDividerCss(array $attrs, string $selector): string
    {
        $css = '';

        // Get SVG paths for dividers
        $dividerSvgs = $this->getDividerSvgPaths();

        // Top divider
        $topStyle = $attrs['divider_top_style'] ?? 'none';
        if ($topStyle !== 'none' && isset($dividerSvgs[$topStyle])) {
            $topColor = $attrs['divider_top_color'] ?? '#ffffff';
            $topHeight = $attrs['divider_top_height'] ?? 100;
            $topFlip = !empty($attrs['divider_top_flip']);
            $topArrangement = $attrs['divider_top_arrangement'] ?? 'below';
            $topZIndex = $topArrangement === 'above' ? 2 : 1;

            $svgPath = $dividerSvgs[$topStyle];
            $svgData = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path fill="' . $topColor . '" d="' . $svgPath . '"/></svg>');

            $css .= $selector . '::before { ';
            $css .= 'content: ""; ';
            $css .= 'position: absolute; ';
            $css .= 'top: 0; left: 0; right: 0; ';
            $css .= 'height: ' . $topHeight . 'px; ';
            $css .= 'background-image: url("' . $svgData . '"); ';
            $css .= 'background-size: 100% 100%; ';
            $css .= 'background-repeat: no-repeat; ';
            $css .= 'z-index: ' . $topZIndex . '; ';
            $css .= 'pointer-events: none; ';
            if ($topFlip) {
                $css .= 'transform: scaleX(-1); ';
            }
            $css .= '}' . "\n";
        }

        // Bottom divider
        $bottomStyle = $attrs['divider_bottom_style'] ?? 'none';
        if ($bottomStyle !== 'none' && isset($dividerSvgs[$bottomStyle])) {
            $bottomColor = $attrs['divider_bottom_color'] ?? '#ffffff';
            $bottomHeight = $attrs['divider_bottom_height'] ?? 100;
            $bottomFlip = !empty($attrs['divider_bottom_flip']);
            $bottomArrangement = $attrs['divider_bottom_arrangement'] ?? 'above';
            $bottomZIndex = $bottomArrangement === 'above' ? 2 : 1;

            $svgPath = $dividerSvgs[$bottomStyle];
            $svgData = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path fill="' . $bottomColor . '" d="' . $svgPath . '"/></svg>');

            $css .= $selector . '::after { ';
            $css .= 'content: ""; ';
            $css .= 'position: absolute; ';
            $css .= 'bottom: 0; left: 0; right: 0; ';
            $css .= 'height: ' . $bottomHeight . 'px; ';
            $css .= 'background-image: url("' . $svgData . '"); ';
            $css .= 'background-size: 100% 100%; ';
            $css .= 'background-repeat: no-repeat; ';
            $css .= 'transform: scaleY(-1)' . ($bottomFlip ? ' scaleX(-1)' : '') . '; ';
            $css .= 'z-index: ' . $bottomZIndex . '; ';
            $css .= 'pointer-events: none; ';
            $css .= '}' . "\n";
        }

        // If dividers are present, ensure relative positioning
        if ($topStyle !== 'none' || $bottomStyle !== 'none') {
            $css = $selector . ' { position: relative; overflow: hidden; }' . "\n" . $css;
        }

        return $css;
    }

    protected function getDividerSvgPaths(): array
    {
        return [
            'slant' => 'M0,0 L1200,120 L1200,0 Z',
            'slant2' => 'M0,120 L1200,0 L0,0 Z',
            'arrow' => 'M0,0 L600,120 L1200,0 L1200,0 L0,0 Z',
            'arrow2' => 'M0,120 L600,0 L1200,120 L1200,120 L0,120 Z',
            'wave' => 'M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z',
            'wave2' => 'M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z',
            'waves' => 'M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z',
            'waves2' => 'M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z',
            'curve' => 'M0,0V7.23C0,65.52,268.63,112.77,600,112.77S1200,65.52,1200,7.23V0Z',
            'curve2' => 'M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z',
            'asymmetric' => 'M0,0V60c30,15,60,0,90,15s60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15,60,0,90,15V0Z',
            'asymmetric2' => 'M0,120V60C30,45,60,60,90,45s60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15,60,30,90,15V120Z',
            'triangle' => 'M0,0 L0,120 L600,0 L1200,120 L1200,0 Z',
            'triangle2' => 'M600,120 L0,0 L1200,0 Z',
            'mountains' => 'M0,0V100L150,60L300,100L450,40L600,100L750,50L900,100L1050,30L1200,100V0Z',
            'mountains2' => 'M0,100L200,40L400,90L600,20L800,80L1000,30L1200,100V120H0Z',
            'clouds' => 'M0,0V75c20,0,40-20,60-20s40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20V0Z',
            'clouds2' => 'M0,120V45c20,0,40,20,60,20s40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20,40-20,60-20,40,20,60,20V120Z',
            'zigzag' => 'M0,0 L50,40 L100,0 L150,40 L200,0 L250,40 L300,0 L350,40 L400,0 L450,40 L500,0 L550,40 L600,0 L650,40 L700,0 L750,40 L800,0 L850,40 L900,0 L950,40 L1000,0 L1050,40 L1100,0 L1150,40 L1200,0 L1200,0 L0,0 Z',
            'pyramids' => 'M0,0 L100,100 L200,0 L300,100 L400,0 L500,100 L600,0 L700,100 L800,0 L900,100 L1000,0 L1100,100 L1200,0 L1200,0 L0,0 Z',
            'ramp' => 'M0,120 L1200,0 L1200,120 Z',
            'ramp2' => 'M0,0 L1200,120 L0,120 Z',
            'grass' => 'M0,120 L0,80 C20,60 40,80 60,60 S80,80 100,60 S120,80 140,60 S160,80 180,60 S200,80 220,60 S240,80 260,60 S280,80 300,60 S320,80 340,60 S360,80 380,60 S400,80 420,60 S440,80 460,60 S480,80 500,60 S520,80 540,60 S560,80 580,60 S600,80 620,60 S640,80 660,60 S680,80 700,60 S720,80 740,60 S760,80 780,60 S800,80 820,60 S840,80 860,60 S880,80 900,60 S920,80 940,60 S960,80 980,60 S1000,80 1020,60 S1040,80 1060,60 S1080,80 1100,60 S1120,80 1140,60 S1160,80 1180,60 S1200,80 1200,60 L1200,120 Z',
            'drops' => 'M0,0 C50,80 100,0 150,80 S200,0 250,80 S300,0 350,80 S400,0 450,80 S500,0 550,80 S600,0 650,80 S700,0 750,80 S800,0 850,80 S900,0 950,80 S1000,0 1050,80 S1100,0 1150,80 S1200,0 1200,0 L1200,0 L0,0 Z',
            'graph' => 'M0,0 L0,60 L100,40 L200,80 L300,20 L400,60 L500,30 L600,70 L700,10 L800,50 L900,25 L1000,65 L1100,15 L1200,55 L1200,0 Z'
        ];
    }

    protected function generateCustomCss(array $attrs, string $selector): string
    {
        $css = '';

        if (!empty($attrs['before_module'])) {
            $css .= $selector . '::before { ' . $attrs['before_module'] . ' }' . "\n";
        }

        if (!empty($attrs['main_element'])) {
            $css .= $selector . ' { ' . $attrs['main_element'] . ' }' . "\n";
        }

        if (!empty($attrs['after_module'])) {
            $css .= $selector . '::after { ' . $attrs['after_module'] . ' }' . "\n";
        }

        return $css;
    }

    protected function generateResponsiveCss(array $attrs, string $selector, string $device, int $breakpoint): string
    {
        $css = '';
        $rules = [];

        $suffix = '__' . $device;

        // Check for responsive values
        $responsiveFields = [
            'font_size' => 'px',
            'line_height' => 'em',
            'letter_spacing' => 'px',
            'text_align' => '',
            'transform_translate_x' => 'px',
            'transform_translate_y' => 'px',
            'position_vertical_offset' => 'px',
            'position_horizontal_offset' => 'px'
        ];

        foreach ($responsiveFields as $field => $unit) {
            $key = $field . $suffix;
            if (isset($attrs[$key])) {
                $property = str_replace('_', '-', $field);
                $value = $attrs[$key] . $unit;
                $rules[] = "{$property}: {$value}";
            }
        }

        // Responsive margin
        if (!empty($attrs['margin' . $suffix])) {
            $margin = $attrs['margin' . $suffix];
            if (is_array($margin)) {
                $rules[] = 'margin: ' . ($margin['top'] ?? 0) . 'px ' . ($margin['right'] ?? 0) . 'px ' . ($margin['bottom'] ?? 0) . 'px ' . ($margin['left'] ?? 0) . 'px';
            }
        }

        // Responsive padding
        if (!empty($attrs['padding' . $suffix])) {
            $padding = $attrs['padding' . $suffix];
            if (is_array($padding)) {
                $rules[] = 'padding: ' . ($padding['top'] ?? 0) . 'px ' . ($padding['right'] ?? 0) . 'px ' . ($padding['bottom'] ?? 0) . 'px ' . ($padding['left'] ?? 0) . 'px';
            }
        }

        if (!empty($rules)) {
            $css .= '@media (max-width: ' . $breakpoint . 'px) {' . "\n";
            $css .= '  ' . $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
            $css .= '}' . "\n";
        }

        return $css;
    }
}
