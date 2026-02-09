<?php
/**
 * Site Logo Module
 * Displays the site logo
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Site_Logo extends JTB_Element
{
    public string $slug = 'site_logo';
    public string $name = 'Site Logo';
    public string $icon = 'image';
    public string $category = 'header';

    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;

    protected string $module_prefix = 'site_logo';

    protected array $style_config = [
        'logo_width' => [
            'property' => 'width',
            'selector' => '.jtb-logo-img',
            'unit' => 'px',
            'responsive' => true
        ],
        'logo_max_height' => [
            'property' => 'max-height',
            'selector' => '.jtb-logo-img',
            'unit' => 'px'
        ],
        'logo_opacity' => [
            'property' => 'opacity',
            'selector' => '.jtb-logo-img',
            'transform' => 'divide100',
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
            'logo' => [
                'label' => 'Logo Image',
                'type' => 'upload',
                'description' => 'Upload your site logo',
                'default' => ''
            ],
            'logo_url' => [
                'label' => 'Logo Link URL',
                'type' => 'text',
                'description' => 'URL the logo links to (default: homepage)',
                'default' => '/'
            ],
            'logo_alt' => [
                'label' => 'Logo Alt Text',
                'type' => 'text',
                'description' => 'Alternative text for accessibility',
                'default' => 'Site Logo'
            ],
            'logo_width' => [
                'label' => 'Logo Width',
                'type' => 'range',
                'min' => 20,
                'max' => 600,
                'step' => 1,
                'default' => 150,
                'unit' => 'px',
                'responsive' => true
            ],
            'logo_max_height' => [
                'label' => 'Logo Max Height',
                'type' => 'range',
                'min' => 0,
                'max' => 300,
                'step' => 1,
                'default' => 0,
                'unit' => 'px',
                'description' => '0 = no limit'
            ],
            'logo_opacity' => [
                'label' => 'Logo Opacity',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => 100,
                'unit' => '%',
                'hover' => true
            ],
            'alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'left',
                'responsive' => true
            ],
            'open_in_new_tab' => [
                'label' => 'Open Link in New Tab',
                'type' => 'toggle',
                'default' => false
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'site_logo_' . uniqid();
        $alignment = $attrs['alignment'] ?? 'left';
        $openNewTab = !empty($attrs['open_in_new_tab']);
        $targetAttr = $openNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';

        // Get logo from attrs or fallback to site settings
        $logo = $attrs['logo'] ?? '';
        $logoUrl = $attrs['logo_url'] ?? '/';
        $logoAlt = $attrs['logo_alt'] ?? '';

        // If no logo in attrs, try to get from site settings
        if (empty($logo)) {
            $logo = JTB_Dynamic_Context::getSiteLogo();
        }
        if (empty($logoAlt)) {
            $logoAlt = JTB_Dynamic_Context::getSiteTitle() ?: 'Site Logo';
        }

        $isPreview = JTB_Dynamic_Context::isPreviewMode();

        $html = '<div id="' . $this->esc($id) . '" class="jtb-site-logo jtb-align-' . $this->esc($alignment) . '">';

        if ($logo && !$isPreview) {
            $html .= '<a href="' . $this->esc($logoUrl) . '" class="jtb-logo-link"' . $targetAttr . '>';
            $html .= '<img src="' . $this->esc($logo) . '" alt="' . $this->esc($logoAlt) . '" class="jtb-logo-img">';
            $html .= '</a>';
        } elseif ($logo) {
            // In preview mode, show the logo but indicate it's from settings
            $html .= '<a href="' . $this->esc($logoUrl) . '" class="jtb-logo-link"' . $targetAttr . '>';
            $html .= '<img src="' . $this->esc($logo) . '" alt="' . $this->esc($logoAlt) . '" class="jtb-logo-img">';
            $html .= '</a>';
        } else {
            $html .= '<div class="jtb-logo-placeholder">';
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>';
            $html .= '<span>Upload a Logo</span>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $logoWidth = $attrs['logo_width'] ?? 150;
        $logoMaxHeight = $attrs['logo_max_height'] ?? 0;
        $logoOpacity = ($attrs['logo_opacity'] ?? 100) / 100;
        $alignment = $attrs['alignment'] ?? 'left';

        // Container alignment
        $flexAlign = 'flex-start';
        if ($alignment === 'center') $flexAlign = 'center';
        if ($alignment === 'right') $flexAlign = 'flex-end';

        $css .= $selector . ' { display: flex; justify-content: ' . $flexAlign . '; }' . "\n";

        // Logo link
        $css .= $selector . ' .jtb-logo-link { display: inline-block; line-height: 0; }' . "\n";

        // Logo image
        $css .= $selector . ' .jtb-logo-img { ';
        $css .= 'width: ' . intval($logoWidth) . 'px; ';
        $css .= 'height: auto; ';
        if ($logoMaxHeight > 0) {
            $css .= 'max-height: ' . intval($logoMaxHeight) . 'px; ';
            $css .= 'object-fit: contain; ';
        }
        $css .= 'opacity: ' . $logoOpacity . '; ';
        $css .= 'transition: opacity 0.3s ease, transform 0.3s ease; ';
        $css .= '}' . "\n";

        // Hover opacity
        if (isset($attrs['logo_opacity__hover'])) {
            $hoverOpacity = $attrs['logo_opacity__hover'] / 100;
            $css .= $selector . ':hover .jtb-logo-img { opacity: ' . $hoverOpacity . '; }' . "\n";
        }

        // Placeholder styling
        $css .= $selector . ' .jtb-logo-placeholder { ';
        $css .= 'display: flex; flex-direction: column; align-items: center; gap: 8px; ';
        $css .= 'padding: 20px 30px; ';
        $css .= 'background: #f8f9fa; ';
        $css .= 'border: 2px dashed #ddd; ';
        $css .= 'border-radius: 8px; ';
        $css .= 'color: #888; ';
        $css .= 'font-size: 14px; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-logo-placeholder svg { opacity: 0.5; }' . "\n";

        // Responsive
        if (!empty($attrs['logo_width__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-logo-img { width: ' . intval($attrs['logo_width__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['logo_width__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-logo-img { width: ' . intval($attrs['logo_width__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['alignment__tablet'])) {
            $flexTablet = 'flex-start';
            if ($attrs['alignment__tablet'] === 'center') $flexTablet = 'center';
            if ($attrs['alignment__tablet'] === 'right') $flexTablet = 'flex-end';
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { justify-content: ' . $flexTablet . '; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['alignment__phone'])) {
            $flexPhone = 'flex-start';
            if ($attrs['alignment__phone'] === 'center') $flexPhone = 'center';
            if ($attrs['alignment__phone'] === 'right') $flexPhone = 'flex-end';
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { justify-content: ' . $flexPhone . '; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('site_logo', JTB_Module_Site_Logo::class);
