<?php
/**
 * Social Media Follow Module
 * Social media follow icons/buttons
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_SocialFollow extends JTB_Element
{
    public string $icon = 'share-2';
    public string $category = 'content';
    public string $child_slug = 'social_follow_item';

    public bool $use_typography = false;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'social_follow';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-social-follow-icon',
            'hover' => true
        ],
        'icon_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-social-follow-icon',
            'hover' => true
        ],
        'icon_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-social-follow-icon',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'social_follow';
    }

    public function getName(): string
    {
        return 'Social Media Follow';
    }

    public function getFields(): array
    {
        return [
            'facebook_url' => [
                'label' => 'Facebook URL',
                'type' => 'text'
            ],
            'twitter_url' => [
                'label' => 'Twitter/X URL',
                'type' => 'text'
            ],
            'instagram_url' => [
                'label' => 'Instagram URL',
                'type' => 'text'
            ],
            'linkedin_url' => [
                'label' => 'LinkedIn URL',
                'type' => 'text'
            ],
            'youtube_url' => [
                'label' => 'YouTube URL',
                'type' => 'text'
            ],
            'pinterest_url' => [
                'label' => 'Pinterest URL',
                'type' => 'text'
            ],
            'tiktok_url' => [
                'label' => 'TikTok URL',
                'type' => 'text'
            ],
            'github_url' => [
                'label' => 'GitHub URL',
                'type' => 'text'
            ],
            'dribbble_url' => [
                'label' => 'Dribbble URL',
                'type' => 'text'
            ],
            'behance_url' => [
                'label' => 'Behance URL',
                'type' => 'text'
            ],
            'email' => [
                'label' => 'Email Address',
                'type' => 'text'
            ],
            'icon_style' => [
                'label' => 'Icon Style',
                'type' => 'select',
                'options' => [
                    'icons_only' => 'Icons Only',
                    'circle' => 'Circle Background',
                    'rounded' => 'Rounded Square',
                    'square' => 'Square'
                ],
                'default' => 'icons_only'
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#666666',
                'hover' => true
            ],
            'use_brand_colors' => [
                'label' => 'Use Brand Colors',
                'type' => 'toggle',
                'default' => false
            ],
            'icon_bg_color' => [
                'label' => 'Icon Background',
                'type' => 'color',
                'default' => '#eeeeee',
                'hover' => true,
                'show_if_not' => ['icon_style' => 'icons_only']
            ],
            'icon_size' => [
                'label' => 'Icon Size',
                'type' => 'range',
                'min' => 10,
                'max' => 60,
                'unit' => 'px',
                'default' => 24,
                'responsive' => true
            ],
            'icon_spacing' => [
                'label' => 'Spacing Between Icons',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => 'px',
                'default' => 10
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
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $networks = [
            'facebook' => ['url' => $attrs['facebook_url'] ?? '', 'label' => 'Facebook', 'brand' => '#1877f2'],
            'twitter' => ['url' => $attrs['twitter_url'] ?? '', 'label' => 'Twitter', 'brand' => '#1da1f2'],
            'instagram' => ['url' => $attrs['instagram_url'] ?? '', 'label' => 'Instagram', 'brand' => '#e4405f'],
            'linkedin' => ['url' => $attrs['linkedin_url'] ?? '', 'label' => 'LinkedIn', 'brand' => '#0077b5'],
            'youtube' => ['url' => $attrs['youtube_url'] ?? '', 'label' => 'YouTube', 'brand' => '#ff0000'],
            'pinterest' => ['url' => $attrs['pinterest_url'] ?? '', 'label' => 'Pinterest', 'brand' => '#bd081c'],
            'tiktok' => ['url' => $attrs['tiktok_url'] ?? '', 'label' => 'TikTok', 'brand' => '#000000'],
            'github' => ['url' => $attrs['github_url'] ?? '', 'label' => 'GitHub', 'brand' => '#333333'],
            'dribbble' => ['url' => $attrs['dribbble_url'] ?? '', 'label' => 'Dribbble', 'brand' => '#ea4c89'],
            'behance' => ['url' => $attrs['behance_url'] ?? '', 'label' => 'Behance', 'brand' => '#1769ff'],
        ];

        $email = $attrs['email'] ?? '';
        $iconStyle = $attrs['icon_style'] ?? 'icons_only';

        $innerHtml = '<div class="jtb-social-follow-container jtb-social-style-' . $this->esc($iconStyle) . '">';

        foreach ($networks as $network => $data) {
            if (!empty($data['url'])) {
                $innerHtml .= '<a href="' . $this->esc($data['url']) . '" target="_blank" rel="noopener" class="jtb-social-follow-icon jtb-social-' . $network . '" title="' . $data['label'] . '" data-brand-color="' . $data['brand'] . '">';
                $innerHtml .= '<span class="jtb-icon jtb-icon-' . $network . '"></span>';
                $innerHtml .= '</a>';
            }
        }

        if (!empty($email)) {
            $innerHtml .= '<a href="mailto:' . $this->esc($email) . '" class="jtb-social-follow-icon jtb-social-email" title="Email" data-brand-color="#666666">';
            $innerHtml .= '<span class="jtb-icon jtb-icon-email"></span>';
            $innerHtml .= '</a>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Social Follow module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Container alignment
        $alignment = $attrs['alignment'] ?? 'center';
        $justify = $alignment === 'left' ? 'flex-start' : ($alignment === 'right' ? 'flex-end' : 'center');

        $css .= $selector . ' .jtb-social-follow-container { ';
        $css .= 'display: flex; flex-wrap: wrap; justify-content: ' . $justify . '; ';
        $css .= 'gap: ' . ($attrs['icon_spacing'] ?? 10) . 'px; ';
        $css .= '}' . "\n";

        // Icon base styling
        $iconStyle = $attrs['icon_style'] ?? 'icons_only';
        $iconSizeRaw = $attrs['icon_size'] ?? 24;
        $iconSize = is_numeric($iconSizeRaw) ? (int)$iconSizeRaw : (int)preg_replace('/[^0-9]/', '', $iconSizeRaw);
        if ($iconSize <= 0) $iconSize = 24;

        $css .= $selector . ' .jtb-social-follow-icon { ';
        $css .= 'display: inline-flex; align-items: center; justify-content: center; ';
        $css .= 'text-decoration: none; transition: all 0.3s ease; ';

        if ($iconStyle !== 'icons_only') {
            $padding = max(8, round($iconSize * 0.4));
            $css .= 'padding: ' . $padding . 'px; ';

            if ($iconStyle === 'circle') {
                $css .= 'border-radius: 50%; ';
            } elseif ($iconStyle === 'rounded') {
                $css .= 'border-radius: 8px; ';
            }
        }

        $css .= '}' . "\n";

        // Brand colors on hover (optional)
        if (!empty($attrs['use_brand_colors'])) {
            $css .= $selector . ' .jtb-social-facebook:hover { color: #1877f2; }' . "\n";
            $css .= $selector . ' .jtb-social-twitter:hover { color: #1da1f2; }' . "\n";
            $css .= $selector . ' .jtb-social-instagram:hover { color: #e4405f; }' . "\n";
            $css .= $selector . ' .jtb-social-linkedin:hover { color: #0077b5; }' . "\n";
            $css .= $selector . ' .jtb-social-youtube:hover { color: #ff0000; }' . "\n";
            $css .= $selector . ' .jtb-social-pinterest:hover { color: #bd081c; }' . "\n";
            $css .= $selector . ' .jtb-social-github:hover { color: #333333; }' . "\n";
            $css .= $selector . ' .jtb-social-dribbble:hover { color: #ea4c89; }' . "\n";
            $css .= $selector . ' .jtb-social-behance:hover { color: #1769ff; }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['alignment__tablet'])) {
            $justify = $attrs['alignment__tablet'] === 'left' ? 'flex-start' : ($attrs['alignment__tablet'] === 'right' ? 'flex-end' : 'center');
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-social-follow-container { justify-content: ' . $justify . '; } }' . "\n";
        }
        if (!empty($attrs['alignment__phone'])) {
            $justify = $attrs['alignment__phone'] === 'left' ? 'flex-start' : ($attrs['alignment__phone'] === 'right' ? 'flex-end' : 'center');
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-social-follow-container { justify-content: ' . $justify . '; } }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('social_follow', JTB_Module_SocialFollow::class);
