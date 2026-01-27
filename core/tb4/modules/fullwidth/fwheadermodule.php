<?php
namespace Core\TB4\Modules\Fullwidth;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Fullwidth Header Module
 *
 * Full-width hero header with background image/video, title, subtitle, and CTA buttons.
 * Supports gradient backgrounds, parallax effects, and scroll indicators.
 */
class FwHeaderModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields_custom = [];

    public function __construct()
    {
        $this->name = 'Fullwidth Header';
        $this->slug = 'fw_header';
        $this->icon = 'layout-template';
        $this->category = 'fullwidth';

        $this->elements = [
            'main' => '.tb4-fw-header',
            'background' => '.tb4-fw-header-bg',
            'overlay' => '.tb4-fw-header-overlay',
            'content' => '.tb4-fw-header-content',
            'logo' => '.tb4-fw-header-logo',
            'title' => '.tb4-fw-header-title',
            'subtitle' => '.tb4-fw-header-subtitle',
            'buttons' => '.tb4-fw-header-buttons',
            'button_primary' => '.tb4-fw-header-btn-primary',
            'button_secondary' => '.tb4-fw-header-btn-secondary',
            'scroll_indicator' => '.tb4-fw-header-scroll'
        ];

        // Content fields
        $this->content_fields = [
            'title' => [
                'type' => 'text',
                'label' => 'Title',
                'default' => 'Welcome to Our Website'
            ],
            'subtitle' => [
                'type' => 'textarea',
                'label' => 'Subtitle',
                'default' => 'We create amazing digital experiences that help businesses grow and succeed in the modern world.'
            ],
            'button_one_text' => [
                'type' => 'text',
                'label' => 'Button 1 Text',
                'default' => 'Get Started'
            ],
            'button_one_url' => [
                'type' => 'text',
                'label' => 'Button 1 URL',
                'default' => '#'
            ],
            'button_two_text' => [
                'type' => 'text',
                'label' => 'Button 2 Text',
                'default' => 'Learn More'
            ],
            'button_two_url' => [
                'type' => 'text',
                'label' => 'Button 2 URL',
                'default' => '#'
            ],
            'show_button_two' => [
                'type' => 'select',
                'label' => 'Show Second Button',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes'
            ],
            'background_type' => [
                'type' => 'select',
                'label' => 'Background Type',
                'options' => [
                    'color' => 'Solid Color',
                    'gradient' => 'Gradient',
                    'image' => 'Image',
                    'video' => 'Video'
                ],
                'default' => 'gradient'
            ],
            'background_image' => [
                'type' => 'text',
                'label' => 'Background Image URL',
                'default' => ''
            ],
            'background_video' => [
                'type' => 'text',
                'label' => 'Background Video URL',
                'default' => ''
            ],
            'parallax' => [
                'type' => 'select',
                'label' => 'Parallax Effect',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'show_scroll_indicator' => [
                'type' => 'select',
                'label' => 'Show Scroll Indicator',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ],
            'logo_image' => [
                'type' => 'text',
                'label' => 'Logo Image URL',
                'default' => ''
            ],
            'show_logo' => [
                'type' => 'select',
                'label' => 'Show Logo Above Title',
                'options' => ['no' => 'No', 'yes' => 'Yes'],
                'default' => 'no'
            ]
        ];

        // Design fields
        $this->design_fields_custom = [
            'min_height' => [
                'type' => 'text',
                'label' => 'Minimum Height',
                'default' => '100vh'
            ],
            'content_width' => [
                'type' => 'text',
                'label' => 'Content Max Width',
                'default' => '800px'
            ],
            'content_alignment' => [
                'type' => 'select',
                'label' => 'Content Alignment',
                'options' => [
                    'center' => 'Center',
                    'left' => 'Left',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'vertical_alignment' => [
                'type' => 'select',
                'label' => 'Vertical Alignment',
                'options' => [
                    'center' => 'Center',
                    'top' => 'Top',
                    'bottom' => 'Bottom'
                ],
                'default' => 'center'
            ],
            'background_color' => [
                'type' => 'color',
                'label' => 'Background Color',
                'default' => '#1f2937'
            ],
            'gradient_start' => [
                'type' => 'color',
                'label' => 'Gradient Start',
                'default' => '#1e3a8a'
            ],
            'gradient_end' => [
                'type' => 'color',
                'label' => 'Gradient End',
                'default' => '#7c3aed'
            ],
            'gradient_direction' => [
                'type' => 'select',
                'label' => 'Gradient Direction',
                'options' => [
                    'to right' => 'Horizontal',
                    'to bottom' => 'Vertical',
                    'to bottom right' => 'Diagonal'
                ],
                'default' => 'to bottom right'
            ],
            'overlay_color' => [
                'type' => 'color',
                'label' => 'Overlay Color',
                'default' => 'rgba(0,0,0,0.5)'
            ],
            'overlay_opacity' => [
                'type' => 'select',
                'label' => 'Overlay Opacity',
                'options' => [
                    '0' => 'None',
                    '0.2' => '20%',
                    '0.4' => '40%',
                    '0.5' => '50%',
                    '0.6' => '60%',
                    '0.8' => '80%'
                ],
                'default' => '0.5'
            ],
            'title_color' => [
                'type' => 'color',
                'label' => 'Title Color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'type' => 'text',
                'label' => 'Title Font Size',
                'default' => '56px'
            ],
            'title_font_weight' => [
                'type' => 'select',
                'label' => 'Title Font Weight',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
                ],
                'default' => '700'
            ],
            'title_line_height' => [
                'type' => 'text',
                'label' => 'Title Line Height',
                'default' => '1.2'
            ],
            'title_margin_bottom' => [
                'type' => 'text',
                'label' => 'Title Margin Bottom',
                'default' => '24px'
            ],
            'subtitle_color' => [
                'type' => 'color',
                'label' => 'Subtitle Color',
                'default' => 'rgba(255,255,255,0.9)'
            ],
            'subtitle_font_size' => [
                'type' => 'text',
                'label' => 'Subtitle Font Size',
                'default' => '20px'
            ],
            'subtitle_line_height' => [
                'type' => 'text',
                'label' => 'Subtitle Line Height',
                'default' => '1.6'
            ],
            'subtitle_margin_bottom' => [
                'type' => 'text',
                'label' => 'Subtitle Margin Bottom',
                'default' => '40px'
            ],
            'button_one_bg' => [
                'type' => 'color',
                'label' => 'Button 1 Background',
                'default' => '#2563eb'
            ],
            'button_one_color' => [
                'type' => 'color',
                'label' => 'Button 1 Text',
                'default' => '#ffffff'
            ],
            'button_one_radius' => [
                'type' => 'text',
                'label' => 'Button 1 Radius',
                'default' => '8px'
            ],
            'button_two_bg' => [
                'type' => 'color',
                'label' => 'Button 2 Background',
                'default' => 'transparent'
            ],
            'button_two_color' => [
                'type' => 'color',
                'label' => 'Button 2 Text',
                'default' => '#ffffff'
            ],
            'button_two_border' => [
                'type' => 'color',
                'label' => 'Button 2 Border',
                'default' => '#ffffff'
            ],
            'button_two_radius' => [
                'type' => 'text',
                'label' => 'Button 2 Radius',
                'default' => '8px'
            ],
            'button_padding' => [
                'type' => 'text',
                'label' => 'Button Padding',
                'default' => '16px 32px'
            ],
            'button_gap' => [
                'type' => 'text',
                'label' => 'Button Gap',
                'default' => '16px'
            ],
            'logo_width' => [
                'type' => 'text',
                'label' => 'Logo Width',
                'default' => '120px'
            ],
            'logo_margin_bottom' => [
                'type' => 'text',
                'label' => 'Logo Margin Bottom',
                'default' => '32px'
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
        $title = $attrs['title'] ?? 'Welcome to Our Website';
        $subtitle = $attrs['subtitle'] ?? 'We create amazing digital experiences that help businesses grow and succeed in the modern world.';
        $btn1Text = $attrs['button_one_text'] ?? 'Get Started';
        $btn1Url = $attrs['button_one_url'] ?? '#';
        $btn2Text = $attrs['button_two_text'] ?? 'Learn More';
        $btn2Url = $attrs['button_two_url'] ?? '#';
        $showBtn2 = ($attrs['show_button_two'] ?? 'yes') !== 'no';
        $bgType = $attrs['background_type'] ?? 'gradient';
        $bgImage = $attrs['background_image'] ?? '';
        $bgVideo = $attrs['background_video'] ?? '';
        $parallax = ($attrs['parallax'] ?? 'no') === 'yes';
        $showScroll = ($attrs['show_scroll_indicator'] ?? 'no') === 'yes';
        $logoImage = $attrs['logo_image'] ?? '';
        $showLogo = ($attrs['show_logo'] ?? 'no') === 'yes';

        // Design fields
        $minHeight = $attrs['min_height'] ?? '100vh';
        $contentWidth = $attrs['content_width'] ?? '800px';
        $contentAlign = $attrs['content_alignment'] ?? 'center';
        $vertAlign = $attrs['vertical_alignment'] ?? 'center';
        $bgColor = $attrs['background_color'] ?? '#1f2937';
        $gradStart = $attrs['gradient_start'] ?? '#1e3a8a';
        $gradEnd = $attrs['gradient_end'] ?? '#7c3aed';
        $gradDir = $attrs['gradient_direction'] ?? 'to bottom right';
        $overlayColor = $attrs['overlay_color'] ?? 'rgba(0,0,0,0.5)';
        $overlayOpacity = $attrs['overlay_opacity'] ?? '0.5';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $titleFontSize = $attrs['title_font_size'] ?? '56px';
        $titleFontWeight = $attrs['title_font_weight'] ?? '700';
        $titleLineHeight = $attrs['title_line_height'] ?? '1.2';
        $titleMarginBottom = $attrs['title_margin_bottom'] ?? '24px';
        $subtitleColor = $attrs['subtitle_color'] ?? 'rgba(255,255,255,0.9)';
        $subtitleFontSize = $attrs['subtitle_font_size'] ?? '20px';
        $subtitleLineHeight = $attrs['subtitle_line_height'] ?? '1.6';
        $subtitleMarginBottom = $attrs['subtitle_margin_bottom'] ?? '40px';
        $btn1Bg = $attrs['button_one_bg'] ?? '#2563eb';
        $btn1Color = $attrs['button_one_color'] ?? '#ffffff';
        $btn1Radius = $attrs['button_one_radius'] ?? '8px';
        $btn2Bg = $attrs['button_two_bg'] ?? 'transparent';
        $btn2Color = $attrs['button_two_color'] ?? '#ffffff';
        $btn2Border = $attrs['button_two_border'] ?? '#ffffff';
        $btn2Radius = $attrs['button_two_radius'] ?? '8px';
        $btnPadding = $attrs['button_padding'] ?? '16px 32px';
        $btnGap = $attrs['button_gap'] ?? '16px';
        $logoWidth = $attrs['logo_width'] ?? '120px';
        $logoMarginBottom = $attrs['logo_margin_bottom'] ?? '32px';

        // Advanced fields
        $cssId = $attrs['css_id'] ?? '';
        $cssClass = $attrs['css_class'] ?? '';

        // Build background style
        $bgStyle = '';
        switch ($bgType) {
            case 'color':
                $bgStyle = "background-color:" . esc_attr($bgColor) . ";";
                break;
            case 'gradient':
                $bgStyle = "background:linear-gradient(" . esc_attr($gradDir) . "," . esc_attr($gradStart) . "," . esc_attr($gradEnd) . ");";
                break;
            case 'image':
                if ($bgImage) {
                    $attachment = $parallax ? 'fixed' : 'scroll';
                    $bgStyle = "background-image:url('" . esc_attr($bgImage) . "');background-size:cover;background-position:center;background-attachment:{$attachment};";
                } else {
                    $bgStyle = "background:linear-gradient(" . esc_attr($gradDir) . "," . esc_attr($gradStart) . "," . esc_attr($gradEnd) . ");";
                }
                break;
            case 'video':
                $bgStyle = "background-color:" . esc_attr($bgColor) . ";";
                break;
            default:
                $bgStyle = "background:linear-gradient(" . esc_attr($gradDir) . "," . esc_attr($gradStart) . "," . esc_attr($gradEnd) . ");";
        }

        // Alignment
        $justifyContent = match ($vertAlign) {
            'top' => 'flex-start',
            'bottom' => 'flex-end',
            default => 'center'
        };
        $alignItems = match ($contentAlign) {
            'left' => 'flex-start',
            'right' => 'flex-end',
            default => 'center'
        };
        $textAlign = $contentAlign;

        // Container ID/Class
        $idAttr = $cssId ? ' id="' . esc_attr($cssId) . '"' : '';
        $classAttr = 'tb4-fw-header' . ($cssClass ? ' ' . esc_attr($cssClass) : '');

        // Build HTML
        $html = '<div' . $idAttr . ' class="' . $classAttr . '" style="position:relative;width:100%;min-height:' . esc_attr($minHeight) . ';overflow:hidden;">';

        // Background
        $html .= '<div class="tb4-fw-header-bg" style="position:absolute;inset:0;' . $bgStyle . '"></div>';

        // Video background
        if ($bgType === 'video' && $bgVideo) {
            $html .= '<video class="tb4-fw-header-video" autoplay muted loop playsinline style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">';
            $html .= '<source src="' . esc_attr($bgVideo) . '" type="video/mp4">';
            $html .= '</video>';
        }

        // Overlay
        if ($overlayOpacity !== '0') {
            $html .= '<div class="tb4-fw-header-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,' . esc_attr($overlayOpacity) . ');"></div>';
        }

        // Content container
        $html .= '<div class="tb4-fw-header-content" style="position:relative;z-index:2;display:flex;flex-direction:column;align-items:' . $alignItems . ';justify-content:' . $justifyContent . ';text-align:' . $textAlign . ';min-height:' . esc_attr($minHeight) . ';padding:60px 40px;box-sizing:border-box;">';
        $html .= '<div style="max-width:' . esc_attr($contentWidth) . ';width:100%;">';

        // Logo
        if ($showLogo) {
            if ($logoImage) {
                $html .= '<img class="tb4-fw-header-logo" src="' . esc_attr($logoImage) . '" alt="Logo" style="max-width:' . esc_attr($logoWidth) . ';height:auto;margin-bottom:' . esc_attr($logoMarginBottom) . ';">';
            } else {
                $html .= '<div style="width:' . esc_attr($logoWidth) . ';height:60px;background:rgba(255,255,255,0.2);border-radius:8px;margin-bottom:' . esc_attr($logoMarginBottom) . ';display:' . ($contentAlign === 'center' ? 'inline-flex' : 'flex') . ';align-items:center;justify-content:center;color:rgba(255,255,255,0.5);font-size:12px;">LOGO</div>';
            }
        }

        // Title
        $html .= '<h1 class="tb4-fw-header-title" style="font-size:' . esc_attr($titleFontSize) . ';font-weight:' . esc_attr($titleFontWeight) . ';color:' . esc_attr($titleColor) . ';margin:0 0 ' . esc_attr($titleMarginBottom) . ' 0;line-height:' . esc_attr($titleLineHeight) . ';">' . esc_html($title) . '</h1>';

        // Subtitle
        $html .= '<p class="tb4-fw-header-subtitle" style="font-size:' . esc_attr($subtitleFontSize) . ';color:' . esc_attr($subtitleColor) . ';margin:0 0 ' . esc_attr($subtitleMarginBottom) . ' 0;line-height:' . esc_attr($subtitleLineHeight) . ';">' . esc_html($subtitle) . '</p>';

        // Buttons
        $btnJustify = match ($contentAlign) {
            'left' => 'flex-start',
            'right' => 'flex-end',
            default => 'center'
        };

        $html .= '<div class="tb4-fw-header-buttons" style="display:flex;gap:' . esc_attr($btnGap) . ';flex-wrap:wrap;justify-content:' . $btnJustify . ';">';

        // Primary button
        $html .= '<a href="' . esc_attr($btn1Url) . '" class="tb4-fw-header-btn tb4-fw-header-btn-primary" style="display:inline-block;padding:' . esc_attr($btnPadding) . ';font-size:16px;font-weight:600;text-decoration:none;border-radius:' . esc_attr($btn1Radius) . ';background:' . esc_attr($btn1Bg) . ';color:' . esc_attr($btn1Color) . ';transition:all 0.2s;">' . esc_html($btn1Text) . '</a>';

        // Secondary button
        if ($showBtn2) {
            $html .= '<a href="' . esc_attr($btn2Url) . '" class="tb4-fw-header-btn tb4-fw-header-btn-secondary" style="display:inline-block;padding:' . esc_attr($btnPadding) . ';font-size:16px;font-weight:600;text-decoration:none;border-radius:' . esc_attr($btn2Radius) . ';background:' . esc_attr($btn2Bg) . ';color:' . esc_attr($btn2Color) . ';border:2px solid ' . esc_attr($btn2Border) . ';transition:all 0.2s;">' . esc_html($btn2Text) . '</a>';
        }

        $html .= '</div>'; // Close buttons
        $html .= '</div>'; // Close inner container
        $html .= '</div>'; // Close content

        // Scroll indicator
        if ($showScroll) {
            $html .= '<div class="tb4-fw-header-scroll" style="position:absolute;bottom:32px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.7);animation:tb4FwHeaderBounce 2s infinite;">';
            $html .= '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
            $html .= '<path d="M12 5v14M5 12l7 7 7-7"/>';
            $html .= '</svg>';
            $html .= '</div>';
        }

        $html .= '</div>'; // Close main container

        // Add keyframes for scroll indicator animation
        if ($showScroll) {
            $html .= '<style>@keyframes tb4FwHeaderBounce{0%,20%,50%,80%,100%{transform:translateX(-50%) translateY(0);}40%{transform:translateX(-50%) translateY(-10px);}60%{transform:translateX(-50%) translateY(-5px);}}</style>';
        }

        return $html;
    }
}
