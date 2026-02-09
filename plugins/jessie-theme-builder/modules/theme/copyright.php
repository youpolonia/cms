<?php
/**
 * Copyright Module
 * Displays copyright text with dynamic year
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Copyright extends JTB_Element
{
    public string $slug = 'copyright';
    public string $name = 'Copyright';
    public string $icon = 'file-text';
    public string $category = 'footer';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'copyright';

    protected array $style_config = [
        'text_color' => [
            'property' => 'color',
            'selector' => '.jtb-copyright-text',
            'hover' => true
        ],
        'font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-copyright-text',
            'unit' => 'px',
            'responsive' => true
        ],
        'link_color' => [
            'property' => 'color',
            'selector' => '.jtb-copyright-text a',
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
            'copyright_text' => [
                'label' => 'Copyright Text',
                'type' => 'text',
                'description' => 'Use {year} for dynamic year, {site_name} for site name',
                'default' => '© {year} {site_name}. All rights reserved.'
            ],
            'site_name' => [
                'label' => 'Site Name',
                'type' => 'text',
                'description' => 'Your company/site name',
                'default' => 'Your Company'
            ],
            'start_year' => [
                'label' => 'Start Year',
                'type' => 'text',
                'description' => 'Optional: Shows range like 2020-2024',
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
                'default' => 'center',
                'responsive' => true
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#666666',
                'hover' => true
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 20,
                'step' => 1,
                'default' => 14,
                'unit' => 'px',
                'responsive' => true
            ],
            'show_powered_by' => [
                'label' => 'Show "Powered by"',
                'type' => 'toggle',
                'default' => false
            ],
            'powered_by_text' => [
                'label' => 'Powered By Text',
                'type' => 'text',
                'default' => 'Powered by Jessie CMS',
                'condition' => ['show_powered_by' => true]
            ],
            'powered_by_url' => [
                'label' => 'Powered By URL',
                'type' => 'text',
                'default' => 'https://jessiecms.com',
                'condition' => ['show_powered_by' => true]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'copyright_' . uniqid();
        $copyrightText = $attrs['copyright_text'] ?? '© {year} {site_name}. All rights reserved.';
        $siteName = $attrs['site_name'] ?? '';
        $startYear = $attrs['start_year'] ?? '';
        $alignment = $attrs['alignment'] ?? 'center';
        $showPoweredBy = $attrs['show_powered_by'] ?? false;
        $poweredByText = $attrs['powered_by_text'] ?? 'Powered by Jessie CMS';
        $poweredByUrl = $attrs['powered_by_url'] ?? 'https://jessiecms.com';

        // If no site name set, get from dynamic context
        if (empty($siteName)) {
            $siteName = JTB_Dynamic_Context::getSiteTitle();
            if (empty($siteName)) {
                $siteName = 'Your Company';
            }
        }

        // Replace dynamic placeholders
        $currentYear = date('Y');
        $yearDisplay = $startYear && $startYear !== $currentYear ? $startYear . '-' . $currentYear : $currentYear;

        $text = str_replace(
            ['{year}', '{site_name}'],
            [$yearDisplay, $this->esc($siteName)],
            $copyrightText
        );

        $html = '<div id="' . $this->esc($id) . '" class="jtb-copyright jtb-align-' . $this->esc($alignment) . '">';
        $html .= '<p class="jtb-copyright-text">' . $text . '</p>';

        if ($showPoweredBy) {
            $html .= '<p class="jtb-powered-by">';
            if ($poweredByUrl) {
                $html .= '<a href="' . $this->esc($poweredByUrl) . '" target="_blank" rel="noopener noreferrer">';
                $html .= $this->esc($poweredByText);
                $html .= '</a>';
            } else {
                $html .= $this->esc($poweredByText);
            }
            $html .= '</p>';
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $alignment = $attrs['alignment'] ?? 'center';
        $textColor = $attrs['text_color'] ?? '#666666';
        $linkColor = $attrs['link_color'] ?? '#2ea3f2';
        $fontSize = $attrs['font_size'] ?? 14;

        // Container
        $css .= $selector . ' { text-align: ' . $alignment . '; }' . "\n";

        // Text
        $css .= $selector . ' .jtb-copyright-text { ';
        $css .= 'margin: 0; ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'line-height: 1.6; ';
        $css .= '}' . "\n";

        // Links
        $css .= $selector . ' .jtb-copyright-text a { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        if (isset($attrs['link_color__hover'])) {
            $css .= $selector . ' .jtb-copyright-text a:hover { color: ' . $attrs['link_color__hover'] . '; }' . "\n";
        }

        // Powered by
        $css .= $selector . ' .jtb-powered-by { ';
        $css .= 'margin: 8px 0 0 0; ';
        $css .= 'font-size: ' . (intval($fontSize) - 2) . 'px; ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'opacity: 0.8; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-powered-by a { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= '}' . "\n";

        // Responsive alignment
        if (!empty($attrs['alignment__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['alignment__tablet'] . '; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['alignment__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['alignment__phone'] . '; }';
            $css .= ' }' . "\n";
        }

        // Responsive font size
        if (!empty($attrs['font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-copyright-text { font-size: ' . intval($attrs['font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-copyright-text { font-size: ' . intval($attrs['font_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('copyright', JTB_Module_Copyright::class);
