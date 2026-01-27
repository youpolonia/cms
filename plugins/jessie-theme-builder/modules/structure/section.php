<?php
/**
 * Section Module
 * Main container for page sections
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Section extends JTB_Element
{
    public string $icon = 'section';
    public string $category = 'structure';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_typography = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;
    public bool $use_sizing = true;
    public bool $use_dividers = true;

    public function getSlug(): string
    {
        return 'section';
    }

    public function getName(): string
    {
        return 'Section';
    }

    public function getFields(): array
    {
        return [
            'fullwidth' => [
                'label' => 'Make This Section Fullwidth',
                'type' => 'toggle',
                'default' => false,
                'description' => 'Extend section to full browser width'
            ],
            'inner_width' => [
                'label' => 'Inner Width',
                'type' => 'range',
                'min' => 500,
                'max' => 2000,
                'unit' => 'px',
                'default' => 1200,
                'responsive' => true,
                'description' => 'Maximum width of section content'
            ],
            'min_height' => [
                'label' => 'Minimum Height',
                'type' => 'range',
                'min' => 0,
                'max' => 1000,
                'unit' => 'px',
                'responsive' => true
            ],
            'vertical_align' => [
                'label' => 'Vertical Alignment',
                'type' => 'select',
                'options' => [
                    'default' => 'Default',
                    'flex-start' => 'Top',
                    'center' => 'Center',
                    'flex-end' => 'Bottom',
                    'space-between' => 'Space Between',
                    'space-around' => 'Space Around'
                ]
            ],
            'admin_label' => [
                'label' => 'Admin Label',
                'type' => 'text',
                'description' => 'Label visible only in builder'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['_id'] ?? $this->generateId();
        $fullwidth = !empty($attrs['fullwidth']);
        $bgType = $attrs['background_type'] ?? 'none';

        // Section classes
        $classes = ['jtb-section'];
        if ($fullwidth) {
            $classes[] = 'jtb-section-fullwidth';
        }
        if ($bgType === 'video') {
            $classes[] = 'jtb-has-video-bg';
        }
        if (!empty($attrs['css_class'])) {
            $classes[] = $this->esc($attrs['css_class']);
        }

        // Visibility classes
        if (!empty($attrs['disable_on_desktop'])) {
            $classes[] = 'jtb-hide-desktop';
        }
        if (!empty($attrs['disable_on_tablet'])) {
            $classes[] = 'jtb-hide-tablet';
        }
        if (!empty($attrs['disable_on_phone'])) {
            $classes[] = 'jtb-hide-phone';
        }

        // Animation classes
        if (!empty($attrs['animation_style']) && $attrs['animation_style'] !== 'none') {
            $classes[] = 'jtb-animated';
            $classes[] = 'jtb-animation-' . $this->esc($attrs['animation_style']);
        }

        $classStr = implode(' ', $classes);
        $idAttr = !empty($attrs['css_id']) ? $attrs['css_id'] : $id;

        $html = '<section id="' . $this->esc($idAttr) . '" class="' . $classStr . '">';

        // Video background
        if ($bgType === 'video') {
            $html .= $this->renderVideoBackground($attrs);
        }

        $html .= '<div class="jtb-section-inner jtb-content">';
        $html .= $content;
        $html .= '</div>';
        $html .= '</section>';

        return $html;
    }

    /**
     * Render video background HTML
     */
    protected function renderVideoBackground(array $attrs): string
    {
        $mp4 = $attrs['background_video_mp4'] ?? '';
        $webm = $attrs['background_video_webm'] ?? '';
        $poster = $attrs['background_video_poster'] ?? '';
        $loop = !empty($attrs['background_video_loop']);
        $muted = !empty($attrs['background_video_muted']);
        $overlay = $attrs['background_video_overlay'] ?? '';

        if (empty($mp4) && empty($webm)) {
            return '';
        }

        $html = '';

        // Video element
        $videoAttrs = 'playsinline autoplay';
        if ($loop) {
            $videoAttrs .= ' loop';
        }
        if ($muted) {
            $videoAttrs .= ' muted';
        }
        if (!empty($poster)) {
            $videoAttrs .= ' poster="' . $this->esc($poster) . '"';
        }

        $html .= '<video class="jtb-video-background" ' . $videoAttrs . '>';
        if (!empty($mp4)) {
            $html .= '<source src="' . $this->esc($mp4) . '" type="video/mp4">';
        }
        if (!empty($webm)) {
            $html .= '<source src="' . $this->esc($webm) . '" type="video/webm">';
        }
        $html .= '</video>';

        // Overlay
        if (!empty($overlay)) {
            $html .= '<div class="jtb-video-overlay"></div>';
        }

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';
        $rules = [];
        $innerRules = [];

        // Inner width
        if (!empty($attrs['inner_width'])) {
            $innerRules[] = 'max-width: ' . (int) $attrs['inner_width'] . 'px';
        }

        // Minimum height
        if (!empty($attrs['min_height'])) {
            $rules[] = 'min-height: ' . (int) $attrs['min_height'] . 'px';
        }

        // Vertical alignment
        if (!empty($attrs['vertical_align']) && $attrs['vertical_align'] !== 'default') {
            $rules[] = 'display: flex';
            $rules[] = 'flex-direction: column';
            $rules[] = 'justify-content: ' . $attrs['vertical_align'];
        }

        if (!empty($rules)) {
            $css .= $selector . ' { ' . implode('; ', $rules) . '; }' . "\n";
        }

        if (!empty($innerRules)) {
            $css .= $selector . ' .jtb-section-inner { ' . implode('; ', $innerRules) . '; }' . "\n";
        }

        // Responsive inner width
        if (!empty($attrs['inner_width__tablet'])) {
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-section-inner { max-width: ' . (int) $attrs['inner_width__tablet'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['inner_width__phone'])) {
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' .jtb-section-inner { max-width: ' . (int) $attrs['inner_width__phone'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        // Responsive min height
        if (!empty($attrs['min_height__tablet'])) {
            $css .= '@media (max-width: 980px) {' . "\n";
            $css .= '  ' . $selector . ' { min-height: ' . (int) $attrs['min_height__tablet'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        if (!empty($attrs['min_height__phone'])) {
            $css .= '@media (max-width: 767px) {' . "\n";
            $css .= '  ' . $selector . ' { min-height: ' . (int) $attrs['min_height__phone'] . 'px; }' . "\n";
            $css .= '}' . "\n";
        }

        // Parent CSS (background, spacing, border, etc.)
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

// Register module
JTB_Registry::register('section', JTB_Module_Section::class);
