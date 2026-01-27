<?php
class AdvisorRuleset {
    public static function getLayoutRules(): array {
        return [
            'max_blocks_per_row' => [
                'threshold' => 4,
                'message' => 'Too many elements in one row (max recommended: 4)',
                'severity' => 'warning'
            ],
            'min_heading_level' => [
                'required' => true,
                'message' => 'Missing heading element in content section',
                'severity' => 'suggestion'  
            ],
            'consistent_spacing' => [
                'tolerance' => 0.2,
                'message' => 'Inconsistent spacing between blocks',
                'severity' => 'warning'
            ]
        ];
    }

    public static function getThemeRules(): array {
        return [
            'min_contrast_ratio' => [
                'threshold' => 4.5,
                'message' => 'Low color contrast (minimum recommended: 4.5:1)',
                'severity' => 'warning'
            ],
            'max_font_variants' => [
                'threshold' => 3,
                'message' => 'Too many font variants (max recommended: 3)',
                'severity' => 'suggestion'
            ],
            'consistent_spacing_scale' => [
                'base' => 8,
                'message' => 'Spacing values should use consistent scale',
                'severity' => 'suggestion'
            ]
        ];
    }

    public static function checkContrast(string $color1, string $color2): float {
        // Convert hex to RGB
        $rgb1 = self::hexToRgb($color1);
        $rgb2 = self::hexToRgb($color2);

        // Calculate relative luminance
        $l1 = self::relativeLuminance($rgb1);
        $l2 = self::relativeLuminance($rgb2);

        // Calculate contrast ratio
        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    private static function hexToRgb(string $hex): array {
        $hex = str_replace('#', '', $hex);
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    private static function relativeLuminance(array $rgb): float {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
