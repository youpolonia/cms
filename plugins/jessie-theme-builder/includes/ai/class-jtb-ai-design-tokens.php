<?php
/**
 * JTB AI Design Tokens
 *
 * Centralized design system tokens for AI-generated layouts.
 * Provides consistent typography, spacing, colors, and component presets.
 *
 * @package JessieThemeBuilder
 * @since 1.0.0
 */

namespace JessieThemeBuilder;

class JTB_AI_Design_Tokens
{
    /**
     * Typography scale - font sizes and weights for different contexts
     */
    const TYPOGRAPHY = [
        'hero_h1' => [
            'font_size' => 56,
            'font_size__tablet' => 44,
            'font_size__phone' => 32,
            'font_weight' => '700',
            'line_height' => '1.1'
        ],
        'section_h2' => [
            'font_size' => 42,
            'font_size__tablet' => 36,
            'font_size__phone' => 28,
            'font_weight' => '700',
            'line_height' => '1.2'
        ],
        'card_h3' => [
            'font_size' => 24,
            'font_size__tablet' => 22,
            'font_size__phone' => 20,
            'font_weight' => '600',
            'line_height' => '1.3'
        ],
        'card_h4' => [
            'font_size' => 20,
            'font_size__tablet' => 18,
            'font_size__phone' => 16,
            'font_weight' => '600',
            'line_height' => '1.4'
        ],
        'body_lg' => [
            'font_size' => 18,
            'font_size__tablet' => 16,
            'font_size__phone' => 15,
            'line_height' => '1.7'
        ],
        'body' => [
            'font_size' => 16,
            'font_size__tablet' => 15,
            'font_size__phone' => 14,
            'line_height' => '1.6'
        ],
        'small' => [
            'font_size' => 14,
            'font_size__tablet' => 13,
            'font_size__phone' => 12,
            'line_height' => '1.5'
        ],
        'counter_number' => [
            'font_size' => 48,
            'font_size__tablet' => 40,
            'font_size__phone' => 32,
            'font_weight' => '700',
            'line_height' => '1.1'
        ],
    ];

    /**
     * Spacing scale (8px base)
     */
    const SPACING = [
        'none' => 0,
        'xs' => 8,
        'sm' => 16,
        'md' => 24,
        'lg' => 32,
        'xl' => 48,
        '2xl' => 64,
        '3xl' => 96,
        '4xl' => 128,
    ];

    /**
     * Section padding presets
     */
    const SECTION_PADDING = [
        'hero' => ['top' => 120, 'right' => 0, 'bottom' => 120, 'left' => 0],
        'hero__tablet' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
        'hero__phone' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0],
        'regular' => ['top' => 100, 'right' => 0, 'bottom' => 100, 'left' => 0],
        'regular__tablet' => ['top' => 70, 'right' => 0, 'bottom' => 70, 'left' => 0],
        'regular__phone' => ['top' => 50, 'right' => 0, 'bottom' => 50, 'left' => 0],
        'compact' => ['top' => 60, 'right' => 0, 'bottom' => 60, 'left' => 0],
        'compact__tablet' => ['top' => 40, 'right' => 0, 'bottom' => 40, 'left' => 0],
        'compact__phone' => ['top' => 30, 'right' => 0, 'bottom' => 30, 'left' => 0],
    ];

    /**
     * Border radius scale
     */
    const BORDER_RADIUS = [
        'none' => ['top_left' => 0, 'top_right' => 0, 'bottom_right' => 0, 'bottom_left' => 0],
        'sm' => ['top_left' => 4, 'top_right' => 4, 'bottom_right' => 4, 'bottom_left' => 4],
        'md' => ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8],
        'lg' => ['top_left' => 12, 'top_right' => 12, 'bottom_right' => 12, 'bottom_left' => 12],
        'xl' => ['top_left' => 16, 'top_right' => 16, 'bottom_right' => 16, 'bottom_left' => 16],
        '2xl' => ['top_left' => 24, 'top_right' => 24, 'bottom_right' => 24, 'bottom_left' => 24],
        'full' => ['top_left' => 9999, 'top_right' => 9999, 'bottom_right' => 9999, 'bottom_left' => 9999],
    ];

    /**
     * Box shadow presets
     */
    const BOX_SHADOW = [
        'none' => 'none',
        'sm' => '0 1px 2px rgba(0,0,0,0.05)',
        'md' => '0 4px 6px rgba(0,0,0,0.07)',
        'lg' => '0 10px 15px rgba(0,0,0,0.1)',
        'xl' => '0 20px 25px rgba(0,0,0,0.15)',
        'card' => '0 4px 20px rgba(0,0,0,0.08)',
        'elevated' => '0 10px 40px rgba(0,0,0,0.12)',
    ];

    /**
     * Button presets
     */
    const BUTTON_PRESETS = [
        'primary' => [
            'padding' => ['top' => 16, 'right' => 32, 'bottom' => 16, 'left' => 32],
            'border_radius' => ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8],
            'font_size' => 16,
            'font_weight' => '600',
            'box_shadow' => '0 4px 6px rgba(0,0,0,0.1)',
        ],
        'secondary' => [
            'padding' => ['top' => 12, 'right' => 24, 'bottom' => 12, 'left' => 24],
            'border_radius' => ['top_left' => 6, 'top_right' => 6, 'bottom_right' => 6, 'bottom_left' => 6],
            'font_size' => 14,
            'font_weight' => '500',
        ],
        'large' => [
            'padding' => ['top' => 20, 'right' => 40, 'bottom' => 20, 'left' => 40],
            'border_radius' => ['top_left' => 10, 'top_right' => 10, 'bottom_right' => 10, 'bottom_left' => 10],
            'font_size' => 18,
            'font_weight' => '600',
            'box_shadow' => '0 6px 12px rgba(0,0,0,0.12)',
        ],
        'pill' => [
            'padding' => ['top' => 14, 'right' => 28, 'bottom' => 14, 'left' => 28],
            'border_radius' => ['top_left' => 50, 'top_right' => 50, 'bottom_right' => 50, 'bottom_left' => 50],
            'font_size' => 15,
            'font_weight' => '600',
        ],
    ];

    /**
     * Card/blurb presets
     */
    const CARD_PRESETS = [
        'elevated' => [
            'padding' => ['top' => 32, 'right' => 32, 'bottom' => 32, 'left' => 32],
            'border_radius' => ['top_left' => 12, 'top_right' => 12, 'bottom_right' => 12, 'bottom_left' => 12],
            'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)',
            'background_color' => '#ffffff',
        ],
        'flat' => [
            'padding' => ['top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24],
            'border_radius' => ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8],
            'background_color' => '#f9fafb',
        ],
        'bordered' => [
            'padding' => ['top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24],
            'border_radius' => ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8],
            'border_width' => ['top' => 1, 'right' => 1, 'bottom' => 1, 'left' => 1],
            'border_style' => 'solid',
            'border_color' => '#e5e7eb',
            'background_color' => '#ffffff',
        ],
        'glass' => [
            'padding' => ['top' => 28, 'right' => 28, 'bottom' => 28, 'left' => 28],
            'border_radius' => ['top_left' => 16, 'top_right' => 16, 'bottom_right' => 16, 'bottom_left' => 16],
            'background_color' => 'rgba(255,255,255,0.8)',
            'box_shadow' => '0 8px 32px rgba(0,0,0,0.1)',
        ],
    ];

    /**
     * Testimonial presets
     */
    const TESTIMONIAL_PRESETS = [
        'card' => [
            'padding' => ['top' => 32, 'right' => 32, 'bottom' => 32, 'left' => 32],
            'border_radius' => ['top_left' => 16, 'top_right' => 16, 'bottom_right' => 16, 'bottom_left' => 16],
            'box_shadow' => '0 4px 20px rgba(0,0,0,0.08)',
            'background_color' => '#ffffff',
            'portrait_size' => 64,
            'portrait_border_radius' => 9999,
        ],
        'minimal' => [
            'padding' => ['top' => 24, 'right' => 0, 'bottom' => 24, 'left' => 0],
            'portrait_size' => 48,
            'portrait_border_radius' => 9999,
        ],
    ];

    /**
     * Icon sizes
     */
    const ICON_SIZES = [
        'sm' => 24,
        'md' => 32,
        'lg' => 48,
        'xl' => 64,
        '2xl' => 80,
    ];

    /**
     * Image border radius presets
     */
    const IMAGE_PRESETS = [
        'sharp' => [
            'border_radius' => ['top_left' => 0, 'top_right' => 0, 'bottom_right' => 0, 'bottom_left' => 0],
        ],
        'rounded' => [
            'border_radius' => ['top_left' => 12, 'top_right' => 12, 'bottom_right' => 12, 'bottom_left' => 12],
            'box_shadow' => '0 10px 30px rgba(0,0,0,0.1)',
        ],
        'soft' => [
            'border_radius' => ['top_left' => 20, 'top_right' => 20, 'bottom_right' => 20, 'bottom_left' => 20],
            'box_shadow' => '0 20px 40px rgba(0,0,0,0.15)',
        ],
    ];

    /**
     * Margin presets for content elements
     */
    const MARGIN_PRESETS = [
        'heading_after' => ['top' => 0, 'right' => 0, 'bottom' => 24, 'left' => 0],
        'heading_section' => ['top' => 0, 'right' => 0, 'bottom' => 48, 'left' => 0],
        'text_after' => ['top' => 0, 'right' => 0, 'bottom' => 24, 'left' => 0],
        'text_before_button' => ['top' => 0, 'right' => 0, 'bottom' => 32, 'left' => 0],
        'none' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0],
    ];

    /**
     * Get design tokens formatted for AI prompt
     * Target: ~1500 characters
     *
     * @return string Formatted tokens for system prompt
     */
    public static function getTokensForPrompt(): string
    {
        return <<<TOKENS
## DESIGN TOKENS (use these values)

### Typography Scale
- hero_h1: font_size=56, font_weight="700", line_height="1.1"
- section_h2: font_size=42, font_weight="700", line_height="1.2"
- card_h3: font_size=24, font_weight="600", line_height="1.3"
- card_h4: font_size=20, font_weight="600", line_height="1.4"
- body_lg: font_size=18, line_height="1.7"
- body: font_size=16, line_height="1.6"
- counter: font_size=48, font_weight="700"

### Spacing (padding/margin objects)
- hero section: {"top":120,"right":0,"bottom":120,"left":0}
- regular section: {"top":100,"right":0,"bottom":100,"left":0}
- compact section: {"top":60,"right":0,"bottom":60,"left":0}
- heading margin: {"top":0,"right":0,"bottom":24,"left":0}
- section heading margin: {"top":0,"right":0,"bottom":48,"left":0}
- text before button: {"top":0,"right":0,"bottom":32,"left":0}

### Border Radius (objects with top_left, top_right, bottom_right, bottom_left)
- sm=4, md=8, lg=12, xl=16, 2xl=24, full=9999

### Box Shadows (string values)
- sm: "0 1px 2px rgba(0,0,0,0.05)"
- md: "0 4px 6px rgba(0,0,0,0.07)"
- lg: "0 10px 15px rgba(0,0,0,0.1)"
- card: "0 4px 20px rgba(0,0,0,0.08)"
- elevated: "0 10px 40px rgba(0,0,0,0.12)"

### Button Presets
- primary: padding={16,32,16,32}, border_radius=8, font_size=16, font_weight="600"
- large: padding={20,40,20,40}, border_radius=10, font_size=18

### Card/Blurb Presets
- elevated: padding={32,32,32,32}, border_radius=12, box_shadow="card", bg="#ffffff"
- flat: padding={24,24,24,24}, border_radius=8, bg="#f9fafb"
- bordered: padding={24,24,24,24}, border_radius=8, border=1px solid #e5e7eb

### Icon Sizes
- sm=24, md=32, lg=48, xl=64

TOKENS;
    }

    /**
     * Get token value by name
     *
     * @param string $category Token category (typography, spacing, etc.)
     * @param string $name Token name
     * @return mixed Token value or null
     */
    public static function get(string $category, string $name)
    {
        $categories = [
            'typography' => self::TYPOGRAPHY,
            'spacing' => self::SPACING,
            'section_padding' => self::SECTION_PADDING,
            'border_radius' => self::BORDER_RADIUS,
            'box_shadow' => self::BOX_SHADOW,
            'button' => self::BUTTON_PRESETS,
            'card' => self::CARD_PRESETS,
            'testimonial' => self::TESTIMONIAL_PRESETS,
            'icon' => self::ICON_SIZES,
            'image' => self::IMAGE_PRESETS,
            'margin' => self::MARGIN_PRESETS,
        ];

        return $categories[$category][$name] ?? null;
    }

    /**
     * Expand a token shorthand to full attributes
     *
     * @param string $type Element type (heading, button, card, etc.)
     * @param string $preset Preset name
     * @return array Expanded attributes
     */
    public static function expandPreset(string $type, string $preset): array
    {
        switch ($type) {
            case 'heading':
                return self::TYPOGRAPHY[$preset] ?? self::TYPOGRAPHY['section_h2'];
            case 'button':
                return self::BUTTON_PRESETS[$preset] ?? self::BUTTON_PRESETS['primary'];
            case 'card':
            case 'blurb':
                return self::CARD_PRESETS[$preset] ?? self::CARD_PRESETS['elevated'];
            case 'testimonial':
                return self::TESTIMONIAL_PRESETS[$preset] ?? self::TESTIMONIAL_PRESETS['card'];
            case 'image':
                return self::IMAGE_PRESETS[$preset] ?? self::IMAGE_PRESETS['rounded'];
            case 'section':
                return ['padding' => self::SECTION_PADDING[$preset] ?? self::SECTION_PADDING['regular']];
            default:
                return [];
        }
    }
}
