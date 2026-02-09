<?php
/**
 * JTB CSS Extractor
 * 
 * Extracts CSS from mockup HTML and maps to JTB module attributes.
 * Bridges AI-generated mockups with JTB's attribute system.
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 * @updated 2026-02-08: Added filters, position, overflow, font-style, text-decoration
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_CSS_Extractor
{
    private array $cssRules = [];
    private array $cssVariables = [];

    public function extract(string $html): array
    {
        $this->cssRules = [];
        $this->cssVariables = [];

        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches)) {
            foreach ($matches[1] as $css) {
                $this->parseCss($css);
            }
        }

        return ['variables' => $this->cssVariables, 'rules' => $this->cssRules];
    }

    private function parseCss(string $css): void
    {
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        if (preg_match('/:root\s*\{([^}]+)\}/i', $css, $rootMatch)) {
            $this->parseVariables($rootMatch[1]);
        }
        $css = preg_replace('/@media[^{]+\{([^{}]*\{[^}]*\})+\s*\}/i', '', $css);
        preg_match_all('/([^{]+)\{([^}]+)\}/s', $css, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $selectors = trim($match[1]);
            if (strpos($selectors, ':root') !== false) continue;
            $props = $this->parseProperties(trim($match[2]));
            if (!empty($props)) $this->cssRules[$selectors] = $props;
        }
    }

    private function parseVariables(string $content): void
    {
        preg_match_all('/--([\w-]+)\s*:\s*([^;]+);?/i', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $this->cssVariables[trim($match[1])] = trim($match[2]);
        }
    }

    private function parseProperties(string $content): array
    {
        $props = [];
        foreach (explode(';', $content) as $part) {
            $part = trim($part);
            if (empty($part)) continue;
            $colonPos = strpos($part, ':');
            if ($colonPos === false) continue;
            $props[trim(substr($part, 0, $colonPos))] = $this->resolveVariables(trim(substr($part, $colonPos + 1)));
        }
        return $props;
    }

    private function resolveVariables(string $value): string
    {
        return preg_replace_callback('/var\(\s*--([\w-]+)\s*(?:,\s*([^)]+))?\)/i', function($m) {
            return $this->cssVariables[$m[1]] ?? ($m[2] ?? '');
        }, $value);
    }

    public function getRulesFor(string $pattern): array
    {
        $combined = [];
        foreach ($this->cssRules as $selector => $props) {
            if (stripos($selector, $pattern) !== false) {
                $combined = array_merge($combined, $props);
            }
        }
        return $combined;
    }

    // =========================================================================
    // MAIN MAPPING METHODS
    // =========================================================================

    public function mapToSectionAttrs(array $css): array
    {
        return array_merge(
            $this->mapBackground($css),
            $this->mapSpacing($css),
            $this->mapBorder($css),
            $this->mapBoxShadow($css),
            $this->mapSizing($css),
            $this->mapFilters($css),
            $this->mapPosition($css),
            $this->mapOverflow($css)
        );
    }

    public function mapToModuleAttrs(array $css): array
    {
        return array_merge(
            $this->mapTypography($css),
            $this->mapBackground($css),
            $this->mapSpacing($css),
            $this->mapBorder($css),
            $this->mapBoxShadow($css),
            $this->mapTransform($css),
            $this->mapFilters($css),
            $this->mapPosition($css),
            $this->mapOverflow($css)
        );
    }

    // =========================================================================
    // BACKGROUND
    // =========================================================================

    private function mapBackground(array $css): array
    {
        $attrs = [];
        if (!empty($css['background-color'])) {
            $attrs['background_type'] = 'color';
            $attrs['background_color'] = $css['background-color'];
        }
        $bg = $css['background'] ?? '';
        if (preg_match('/linear-gradient\s*\([^)]+\)/i', $bg, $m)) {
            $attrs['background_type'] = 'gradient';
            $attrs['background_gradient_type'] = 'linear';
            $attrs = array_merge($attrs, $this->parseGradient($m[0]));
        } elseif (preg_match('/radial-gradient\s*\([^)]+\)/i', $bg, $m)) {
            $attrs['background_type'] = 'gradient';
            $attrs['background_gradient_type'] = 'radial';
            $attrs = array_merge($attrs, $this->parseGradient($m[0]));
        } elseif (preg_match('/conic-gradient\s*\([^)]+\)/i', $bg, $m)) {
            $attrs['background_type'] = 'gradient';
            $attrs['background_gradient_type'] = 'conic';
            $attrs = array_merge($attrs, $this->parseGradient($m[0]));
        } elseif (preg_match('/url\s*\(["\']?([^"\']+)["\']?\)/i', $bg, $m)) {
            $attrs['background_type'] = 'image';
            $attrs['background_image'] = $m[1];
        }
        if (!empty($css['background-size'])) $attrs['background_size'] = $css['background-size'];
        if (!empty($css['background-position'])) $attrs['background_position'] = $css['background-position'];
        if (!empty($css['background-repeat'])) $attrs['background_repeat'] = $css['background-repeat'];
        if (!empty($css['background-attachment']) && $css['background-attachment'] === 'fixed') {
            $attrs['parallax'] = true;
        }
        return $attrs;
    }

    private function parseGradient(string $gradient): array
    {
        $attrs = [];
        if (preg_match('/(\d+)deg/i', $gradient, $m)) {
            $attrs['background_gradient_direction'] = (int)$m[1];
        } elseif (preg_match('/to\s+(top|bottom|left|right)/i', $gradient, $m)) {
            $map = ['top' => 0, 'right' => 90, 'bottom' => 180, 'left' => 270];
            $attrs['background_gradient_direction'] = $map[$m[1]] ?? 180;
        }
        preg_match_all('/(#[a-f0-9]{3,8}|rgba?\([^)]+\))\s*(\d+%)?/i', $gradient, $stops, PREG_SET_ORDER);
        if (!empty($stops)) {
            $gradientStops = [];
            $count = count($stops);
            foreach ($stops as $i => $stop) {
                $gradientStops[] = [
                    'color' => $stop[1],
                    'position' => isset($stop[2]) ? (int)$stop[2] : (int)round($i / max(1, $count - 1) * 100)
                ];
            }
            $attrs['background_gradient_stops'] = $gradientStops;
        }
        return $attrs;
    }

    // =========================================================================
    // SPACING
    // =========================================================================

    private function mapSpacing(array $css): array
    {
        $attrs = [];
        if (!empty($css['padding'])) $attrs['padding'] = $this->parseSpacingShorthand($css['padding']);
        if (!empty($css['margin'])) $attrs['margin'] = $this->parseSpacingShorthand($css['margin']);
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            if (!empty($css["padding-$side"])) $attrs['padding'][$side] = $this->parseUnit($css["padding-$side"]);
            if (!empty($css["margin-$side"])) $attrs['margin'][$side] = $this->parseUnit($css["margin-$side"]);
        }
        return $attrs;
    }

    private function parseSpacingShorthand(string $value): array
    {
        $parts = preg_split('/\s+/', trim($value));
        $v = array_map([$this, 'parseUnit'], $parts);
        switch (count($v)) {
            case 1: return ['top' => $v[0], 'right' => $v[0], 'bottom' => $v[0], 'left' => $v[0]];
            case 2: return ['top' => $v[0], 'right' => $v[1], 'bottom' => $v[0], 'left' => $v[1]];
            case 3: return ['top' => $v[0], 'right' => $v[1], 'bottom' => $v[2], 'left' => $v[1]];
            case 4: return ['top' => $v[0], 'right' => $v[1], 'bottom' => $v[2], 'left' => $v[3]];
            default: return ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0];
        }
    }

    private function parseUnit(string $value)
    {
        $value = trim($value);
        if (preg_match('/^([\d.]+)(rem|em)$/i', $value, $m)) return (int)round((float)$m[1] * 16);
        if (preg_match('/^[\d.]+(vh|vw|%)$/i', $value)) return $value;
        return (int)preg_replace('/[^\d.-]/', '', $value);
    }

    // =========================================================================
    // BORDER
    // =========================================================================

    private function mapBorder(array $css): array
    {
        $attrs = [];
        if (!empty($css['border']) && preg_match('/(\d+)px\s+(solid|dashed|dotted|double|groove|ridge)\s+(#[a-f0-9]{3,8}|rgba?\([^)]+\)|[a-z]+)/i', $css['border'], $m)) {
            $w = (int)$m[1];
            $attrs['border_width'] = ['top' => $w, 'right' => $w, 'bottom' => $w, 'left' => $w];
            $attrs['border_style'] = $m[2];
            $attrs['border_color'] = $m[3];
        }
        if (!empty($css['border-width'])) $attrs['border_width'] = $this->parseSpacingShorthand($css['border-width']);
        if (!empty($css['border-style'])) $attrs['border_style'] = $css['border-style'];
        if (!empty($css['border-color'])) $attrs['border_color'] = $css['border-color'];
        if (!empty($css['border-radius'])) {
            $r = $this->parseSpacingShorthand($css['border-radius']);
            $attrs['border_radius'] = ['top_left' => $r['top'], 'top_right' => $r['right'], 'bottom_right' => $r['bottom'], 'bottom_left' => $r['left']];
        }
        return $attrs;
    }

    // =========================================================================
    // BOX SHADOW
    // =========================================================================

    private function mapBoxShadow(array $css): array
    {
        $attrs = [];
        if (empty($css['box-shadow']) || $css['box-shadow'] === 'none') return $attrs;
        $shadow = $css['box-shadow'];
        // Pattern: [inset] offsetX offsetY blur [spread] [color]
        if (preg_match('/(-?[\d.]+)(px)?\s+(-?[\d.]+)(px)?\s+([\d.]+)(px)?(?:\s+(-?[\d.]+)(px)?)?(?:\s+(#[a-f0-9]{3,8}|rgba?\([^)]+\)))?/i', $shadow, $m)) {
            $attrs['box_shadow_style'] = 'custom';
            $attrs['box_shadow_horizontal'] = (int)$m[1];
            $attrs['box_shadow_vertical'] = (int)$m[3];
            $attrs['box_shadow_blur'] = (int)$m[5];
            if (!empty($m[7])) $attrs['box_shadow_spread'] = (int)$m[7];
            if (!empty($m[9])) $attrs['box_shadow_color'] = $m[9];
        }
        return $attrs;
    }

    // =========================================================================
    // TYPOGRAPHY
    // =========================================================================

    private function mapTypography(array $css): array
    {
        $attrs = [];
        if (!empty($css['font-family'])) $attrs['font_family'] = trim($css['font-family'], '"\'');
        if (!empty($css['font-size'])) $attrs['font_size'] = $this->parseUnit($css['font-size']);
        if (!empty($css['font-weight'])) $attrs['font_weight'] = $css['font-weight'];
        if (!empty($css['font-style'])) $attrs['font_style'] = $css['font-style'];
        if (!empty($css['line-height'])) {
            $lh = $css['line-height'];
            $attrs['line_height'] = is_numeric($lh) ? (float)$lh : (preg_match('/^([\d.]+)/i', $lh, $m) ? (float)$m[1] : 1.6);
        }
        if (!empty($css['letter-spacing'])) $attrs['letter_spacing'] = $this->parseUnit($css['letter-spacing']);
        if (!empty($css['color'])) $attrs['text_color'] = $css['color'];
        if (!empty($css['text-align'])) $attrs['text_align'] = $css['text-align'];
        if (!empty($css['text-transform'])) $attrs['text_transform'] = $css['text-transform'];
        if (!empty($css['text-decoration'])) {
            $td = strtolower($css['text-decoration']);
            if (strpos($td, 'underline') !== false) $attrs['text_decoration'] = 'underline';
            elseif (strpos($td, 'line-through') !== false) $attrs['text_decoration'] = 'line-through';
            elseif (strpos($td, 'none') !== false) $attrs['text_decoration'] = 'none';
        }
        // Text shadow
        if (!empty($css['text-shadow']) && $css['text-shadow'] !== 'none') {
            if (preg_match('/(-?[\d.]+)(px)?\s+(-?[\d.]+)(px)?\s+([\d.]+)(px)?(?:\s+(#[a-f0-9]{3,8}|rgba?\([^)]+\)))?/i', $css['text-shadow'], $m)) {
                $attrs['text_shadow_style'] = 'custom';
                $attrs['text_shadow_horizontal'] = (int)$m[1];
                $attrs['text_shadow_vertical'] = (int)$m[3];
                $attrs['text_shadow_blur'] = (int)$m[5];
                if (!empty($m[7])) $attrs['text_shadow_color'] = $m[7];
            }
        }
        return $attrs;
    }

    // =========================================================================
    // TRANSFORM
    // =========================================================================

    private function mapTransform(array $css): array
    {
        $attrs = [];
        if (empty($css['transform']) || $css['transform'] === 'none') return $attrs;
        $t = $css['transform'];
        if (preg_match('/scale\s*\(\s*([\d.]+)(?:\s*,\s*([\d.]+))?\s*\)/i', $t, $m)) {
            $attrs['transform_scale'] = (int)((float)$m[1] * 100);
        }
        if (preg_match('/rotate\s*\(\s*(-?[\d.]+)deg\s*\)/i', $t, $m)) $attrs['transform_rotate'] = (int)$m[1];
        if (preg_match('/skewX\s*\(\s*(-?[\d.]+)deg\s*\)/i', $t, $m)) $attrs['transform_skew_x'] = (int)$m[1];
        if (preg_match('/skewY\s*\(\s*(-?[\d.]+)deg\s*\)/i', $t, $m)) $attrs['transform_skew_y'] = (int)$m[1];
        if (preg_match('/translateX\s*\(\s*(-?[\d.]+)(px|%|rem|em)?\s*\)/i', $t, $m)) {
            $attrs['transform_translate_x'] = $this->parseUnit($m[1] . ($m[2] ?? 'px'));
        }
        if (preg_match('/translateY\s*\(\s*(-?[\d.]+)(px|%|rem|em)?\s*\)/i', $t, $m)) {
            $attrs['transform_translate_y'] = $this->parseUnit($m[1] . ($m[2] ?? 'px'));
        }
        if (preg_match('/translate\s*\(\s*(-?[\d.]+)(px|%)?(?:\s*,\s*(-?[\d.]+)(px|%)?)?\s*\)/i', $t, $m)) {
            $attrs['transform_translate_x'] = $this->parseUnit($m[1] . ($m[2] ?? 'px'));
            if (!empty($m[3])) $attrs['transform_translate_y'] = $this->parseUnit($m[3] . ($m[4] ?? 'px'));
        }
        if (!empty($css['transform-origin'])) $attrs['transform_origin'] = $css['transform-origin'];
        return $attrs;
    }

    // =========================================================================
    // FILTERS
    // =========================================================================

    private function mapFilters(array $css): array
    {
        $attrs = [];
        $filter = $css['filter'] ?? $css['backdrop-filter'] ?? '';
        if (empty($filter) || $filter === 'none') return $attrs;

        if (preg_match('/blur\s*\(\s*([\d.]+)(px)?\s*\)/i', $filter, $m)) {
            $attrs['filter_blur'] = (int)$m[1];
        }
        if (preg_match('/brightness\s*\(\s*([\d.]+)(%?)\s*\)/i', $filter, $m)) {
            $attrs['filter_brightness'] = $m[2] === '%' ? (int)$m[1] : (int)((float)$m[1] * 100);
        }
        if (preg_match('/contrast\s*\(\s*([\d.]+)(%?)\s*\)/i', $filter, $m)) {
            $attrs['filter_contrast'] = $m[2] === '%' ? (int)$m[1] : (int)((float)$m[1] * 100);
        }
        if (preg_match('/saturate\s*\(\s*([\d.]+)(%?)\s*\)/i', $filter, $m)) {
            $attrs['filter_saturate'] = $m[2] === '%' ? (int)$m[1] : (int)((float)$m[1] * 100);
        }
        if (preg_match('/hue-rotate\s*\(\s*([\d.]+)deg\s*\)/i', $filter, $m)) {
            $attrs['filter_hue_rotate'] = (int)$m[1];
        }
        if (preg_match('/invert\s*\(\s*([\d.]+)(%?)\s*\)/i', $filter, $m)) {
            $attrs['filter_invert'] = $m[2] === '%' ? (int)$m[1] : (int)((float)$m[1] * 100);
        }
        if (preg_match('/sepia\s*\(\s*([\d.]+)(%?)\s*\)/i', $filter, $m)) {
            $attrs['filter_sepia'] = $m[2] === '%' ? (int)$m[1] : (int)((float)$m[1] * 100);
        }
        if (preg_match('/grayscale\s*\(\s*([\d.]+)(%?)\s*\)/i', $filter, $m)) {
            $attrs['filter_grayscale'] = $m[2] === '%' ? (int)$m[1] : (int)((float)$m[1] * 100);
        }
        return $attrs;
    }

    // =========================================================================
    // POSITION
    // =========================================================================

    private function mapPosition(array $css): array
    {
        $attrs = [];
        if (!empty($css['position'])) {
            $pos = strtolower($css['position']);
            if (in_array($pos, ['relative', 'absolute', 'fixed', 'sticky'])) {
                $attrs['position_type'] = $pos;
            }
        }
        if (!empty($css['z-index']) && $css['z-index'] !== 'auto') {
            $attrs['z_index'] = (int)$css['z-index'];
        }
        if (!empty($css['top'])) $attrs['position_vertical_offset'] = $this->parseUnit($css['top']);
        if (!empty($css['left'])) $attrs['position_horizontal_offset'] = $this->parseUnit($css['left']);
        if (!empty($css['opacity']) && $css['opacity'] !== '1') {
            $attrs['opacity'] = (float)$css['opacity'];
        }
        return $attrs;
    }

    // =========================================================================
    // SIZING & OVERFLOW
    // =========================================================================

    private function mapSizing(array $css): array
    {
        $attrs = [];
        if (!empty($css['width'])) $attrs['width'] = $css['width'];
        if (!empty($css['min-width'])) $attrs['min_width'] = $css['min-width'];
        if (!empty($css['max-width'])) {
            $attrs['max_width'] = $css['max-width'];
            if (preg_match('/(\d+)px/i', $css['max-width'], $m)) $attrs['inner_width'] = (int)$m[1];
        }
        if (!empty($css['height'])) $attrs['height'] = $css['height'];
        if (!empty($css['min-height'])) $attrs['min_height'] = $css['min-height'];
        if (!empty($css['max-height'])) $attrs['max_height'] = $css['max-height'];
        return $attrs;
    }

    private function mapOverflow(array $css): array
    {
        $attrs = [];
        if (!empty($css['overflow'])) {
            $attrs['overflow_x'] = $css['overflow'];
            $attrs['overflow_y'] = $css['overflow'];
        }
        if (!empty($css['overflow-x'])) $attrs['overflow_x'] = $css['overflow-x'];
        if (!empty($css['overflow-y'])) $attrs['overflow_y'] = $css['overflow-y'];
        return $attrs;
    }

    // =========================================================================
    // PUBLIC HELPERS
    // =========================================================================

    public function getVariables(): array { return $this->cssVariables; }
    public function getRules(): array { return $this->cssRules; }
    
    public function extractForSection(string $sectionClass): array
    {
        return $this->mapToSectionAttrs($this->getRulesFor('.' . $sectionClass));
    }
}
