<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Team Member Module
 * Displays team member profiles with photo, bio, and social links
 */
class TeamModule extends Module
{
    public function __construct()
    {
        $this->name = 'Team Member';
        $this->slug = "team";
        $this->icon = 'Users';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-team-member',
            'photo' => '.tb4-team-member__photo',
            'photo_img' => '.tb4-team-member__photo img',
            'content' => '.tb4-team-member__content',
            'name' => '.tb4-team-member__name',
            'position' => '.tb4-team-member__position',
            'bio' => '.tb4-team-member__bio',
            'contact' => '.tb4-team-member__contact',
            'social' => '.tb4-team-member__social',
            'social_link' => '.tb4-team-member__social-link',
            'overlay' => '.tb4-team-member__overlay'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            // Basic info
            'photo' => [
                'label' => 'Photo',
                'type' => 'image',
                'default' => '',
                'description' => 'Team member photo'
            ],
            'name' => [
                'label' => 'Name',
                'type' => 'text',
                'default' => 'John Doe'
            ],
            'position' => [
                'label' => 'Position / Job Title',
                'type' => 'text',
                'default' => 'Software Engineer'
            ],
            'bio' => [
                'label' => 'Bio',
                'type' => 'textarea',
                'default' => 'A passionate professional with years of experience in the industry.',
                'rows' => 4
            ],

            // Contact info (optional)
            'email' => [
                'label' => 'Email (optional)',
                'type' => 'text',
                'default' => ''
            ],
            'phone' => [
                'label' => 'Phone (optional)',
                'type' => 'text',
                'default' => ''
            ],

            // Social links
            'facebook_url' => [
                'label' => 'Facebook URL',
                'type' => 'text',
                'default' => ''
            ],
            'twitter_url' => [
                'label' => 'Twitter / X URL',
                'type' => 'text',
                'default' => ''
            ],
            'linkedin_url' => [
                'label' => 'LinkedIn URL',
                'type' => 'text',
                'default' => ''
            ],
            'instagram_url' => [
                'label' => 'Instagram URL',
                'type' => 'text',
                'default' => ''
            ],
            'github_url' => [
                'label' => 'GitHub URL',
                'type' => 'text',
                'default' => ''
            ],

            // Layout
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'card' => 'Card (Vertical)',
                    'horizontal' => 'Horizontal (Photo Left)',
                    'overlay' => 'Overlay (Info on Hover)'
                ],
                'default' => 'card'
            ],

            // Photo settings
            'photo_size' => [
                'label' => 'Photo Size',
                'type' => 'select',
                'options' => [
                    '150px' => '150px',
                    '200px' => '200px',
                    '250px' => '250px',
                    'full' => 'Full Width'
                ],
                'default' => '200px'
            ],
            'photo_shape' => [
                'label' => 'Photo Shape',
                'type' => 'select',
                'options' => [
                    'circle' => 'Circle',
                    'rounded' => 'Rounded',
                    'square' => 'Square'
                ],
                'default' => 'circle'
            ],
            'photo_border_width' => [
                'label' => 'Photo Border Width',
                'type' => 'text',
                'default' => '0'
            ],
            'photo_border_color' => [
                'label' => 'Photo Border Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],

            // Text styling
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ],
            'name_size' => [
                'label' => 'Name Font Size',
                'type' => 'text',
                'default' => '20px'
            ],
            'name_color' => [
                'label' => 'Name Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'name_weight' => [
                'label' => 'Name Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi-Bold',
                    '700' => 'Bold'
                ],
                'default' => '600'
            ],
            'position_size' => [
                'label' => 'Position Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'position_color' => [
                'label' => 'Position Color',
                'type' => 'color',
                'default' => '#3b82f6'
            ],
            'bio_size' => [
                'label' => 'Bio Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'bio_color' => [
                'label' => 'Bio Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],

            // Card styling
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'card_padding' => [
                'label' => 'Padding',
                'type' => 'text',
                'default' => '24px'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'box_shadow_enabled' => [
                'label' => 'Enable Box Shadow',
                'type' => 'toggle',
                'default' => true
            ],

            // Hover effect
            'hover_effect' => [
                'label' => 'Hover Effect',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'lift' => 'Lift Up',
                    'glow' => 'Glow',
                    'border' => 'Border Highlight'
                ],
                'default' => 'lift'
            ],

            // Social icons styling
            'social_icon_style' => [
                'label' => 'Social Icon Style',
                'type' => 'select',
                'options' => [
                    'filled' => 'Filled',
                    'outline' => 'Outline',
                    'minimal' => 'Minimal'
                ],
                'default' => 'filled'
            ],
            'social_icon_color' => [
                'label' => 'Social Icon Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'social_icon_size' => [
                'label' => 'Social Icon Size',
                'type' => 'text',
                'default' => '20px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Get social icon SVG
     */
    private function get_social_icon(string $platform, string $size, string $color, string $style): string
    {
        $icons = [
            'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="SIZE" height="SIZE" viewBox="0 0 24 24" fill="COLOR" stroke="none"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="SIZE" height="SIZE" viewBox="0 0 24 24" fill="COLOR" stroke="none"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="SIZE" height="SIZE" viewBox="0 0 24 24" fill="COLOR" stroke="none"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="SIZE" height="SIZE" viewBox="0 0 24 24" fill="COLOR" stroke="none"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.757-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>',
            'github' => '<svg xmlns="http://www.w3.org/2000/svg" width="SIZE" height="SIZE" viewBox="0 0 24 24" fill="COLOR" stroke="none"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>'
        ];

        $svg = $icons[$platform] ?? '';
        if (empty($svg)) {
            return '';
        }

        // Apply size
        $svg = str_replace('SIZE', esc_attr($size), $svg);

        // Apply color based on style
        if ($style === 'outline') {
            $svg = str_replace('fill="COLOR"', 'fill="none" stroke="' . esc_attr($color) . '" stroke-width="1.5"', $svg);
        } elseif ($style === 'minimal') {
            $svg = str_replace('COLOR', esc_attr($color), $svg);
        } else {
            // filled
            $svg = str_replace('COLOR', esc_attr($color), $svg);
        }

        return $svg;
    }

    public function render(array $settings): string
    {
        // Content fields
        $photo = $settings['photo'] ?? '';
        $name = $settings['name'] ?? 'John Doe';
        $position = $settings['position'] ?? '';
        $bio = $settings['bio'] ?? '';
        $email = $settings['email'] ?? '';
        $phone = $settings['phone'] ?? '';

        // Social links
        $facebookUrl = $settings['facebook_url'] ?? '';
        $twitterUrl = $settings['twitter_url'] ?? '';
        $linkedinUrl = $settings['linkedin_url'] ?? '';
        $instagramUrl = $settings['instagram_url'] ?? '';
        $githubUrl = $settings['github_url'] ?? '';

        // Layout
        $layout = $settings['layout'] ?? 'card';

        // Photo settings
        $photoSize = $settings['photo_size'] ?? '200px';
        $photoShape = $settings['photo_shape'] ?? 'circle';
        $photoBorderWidth = $settings['photo_border_width'] ?? '0';
        $photoBorderColor = $settings['photo_border_color'] ?? '#e5e7eb';

        // Text styling
        $textAlign = $settings['text_align'] ?? 'center';
        $nameSize = $settings['name_size'] ?? '20px';
        $nameColor = $settings['name_color'] ?? '#111827';
        $nameWeight = $settings['name_weight'] ?? '600';
        $positionSize = $settings['position_size'] ?? '14px';
        $positionColor = $settings['position_color'] ?? '#3b82f6';
        $bioSize = $settings['bio_size'] ?? '14px';
        $bioColor = $settings['bio_color'] ?? '#6b7280';

        // Card styling
        $backgroundColor = $settings['background_color'] ?? '#ffffff';
        $cardPadding = $settings['card_padding'] ?? '24px';
        $borderRadius = $settings['border_radius'] ?? '12px';
        $boxShadowEnabled = $settings['box_shadow_enabled'] ?? true;

        // Hover effect
        $hoverEffect = $settings['hover_effect'] ?? 'lift';

        // Social icons
        $socialIconStyle = $settings['social_icon_style'] ?? 'filled';
        $socialIconColor = $settings['social_icon_color'] ?? '#6b7280';
        $socialIconSize = $settings['social_icon_size'] ?? '20px';

        // Calculate photo dimensions
        $photoDimension = $photoSize === 'full' ? '100%' : $photoSize;
        $photoWidth = $photoSize === 'full' ? '100%' : $photoSize;
        $photoHeight = $photoSize === 'full' ? 'auto' : $photoSize;

        // Calculate photo border radius
        $photoBorderRadius = match ($photoShape) {
            'circle' => '50%',
            'rounded' => '12px',
            default => '0'
        };

        // Build unique ID for scoped styles
        $uniqueId = 'tb4-team-' . uniqid();

        // Build main wrapper styles
        $wrapperStyles = [
            'position: relative',
            'background: ' . esc_attr($backgroundColor),
            'padding: ' . esc_attr($cardPadding),
            'border-radius: ' . esc_attr($borderRadius),
            'overflow: hidden',
            'transition: all 0.3s ease'
        ];

        if ($boxShadowEnabled) {
            $wrapperStyles[] = 'box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08)';
        }

        // Layout-specific container styles
        if ($layout === 'horizontal') {
            $wrapperStyles[] = 'display: flex';
            $wrapperStyles[] = 'align-items: flex-start';
            $wrapperStyles[] = 'gap: 24px';
        } elseif ($layout === 'overlay') {
            $wrapperStyles[] = 'padding: 0';
        }

        $wrapperStyle = implode('; ', $wrapperStyles);

        // Build photo styles
        $photoContainerStyles = [];
        if ($layout === 'card') {
            $photoContainerStyles[] = 'display: flex';
            $photoContainerStyles[] = 'justify-content: center';
            $photoContainerStyles[] = 'margin-bottom: 16px';
        } elseif ($layout === 'horizontal') {
            $photoContainerStyles[] = 'flex-shrink: 0';
        } elseif ($layout === 'overlay') {
            $photoContainerStyles[] = 'position: relative';
            $photoContainerStyles[] = 'width: 100%';
        }

        $photoImgStyles = [
            'width: ' . esc_attr($photoWidth),
            'height: ' . esc_attr($photoHeight),
            'object-fit: cover',
            'border-radius: ' . $photoBorderRadius,
            'display: block'
        ];

        if ($photoBorderWidth !== '0' && $photoBorderWidth !== '0px') {
            $photoImgStyles[] = 'border: ' . esc_attr($photoBorderWidth) . ' solid ' . esc_attr($photoBorderColor);
        }

        // Content container styles
        $contentStyles = [
            'text-align: ' . esc_attr($textAlign)
        ];

        if ($layout === 'horizontal') {
            $contentStyles[] = 'flex: 1';
        } elseif ($layout === 'overlay') {
            $contentStyles[] = 'position: absolute';
            $contentStyles[] = 'bottom: 0';
            $contentStyles[] = 'left: 0';
            $contentStyles[] = 'right: 0';
            $contentStyles[] = 'padding: ' . esc_attr($cardPadding);
            $contentStyles[] = 'background: linear-gradient(transparent, rgba(0,0,0,0.8))';
            $contentStyles[] = 'color: white';
            $contentStyles[] = 'opacity: 0';
            $contentStyles[] = 'transition: opacity 0.3s ease';
        }

        // Build HTML
        $html = sprintf(
            '<div class="tb4-team-member tb4-team-member--%s tb4-team-member--hover-%s" id="%s" style="%s">',
            esc_attr($layout),
            esc_attr($hoverEffect),
            esc_attr($uniqueId),
            $wrapperStyle
        );

        // Photo section
        $html .= '<div class="tb4-team-member__photo" style="' . implode('; ', $photoContainerStyles) . '">';
        if ($photo) {
            $html .= sprintf(
                '<img src="%s" alt="%s" style="%s">',
                esc_attr($photo),
                esc_attr($name),
                implode('; ', $photoImgStyles)
            );
        } else {
            // Placeholder with initials
            $initials = '';
            $nameParts = explode(' ', $name);
            foreach ($nameParts as $part) {
                if (!empty($part)) {
                    $initials .= strtoupper($part[0]);
                }
            }
            $initials = substr($initials, 0, 2);

            $placeholderStyles = [
                'width: ' . esc_attr($photoWidth),
                'height: ' . esc_attr($photoHeight),
                'border-radius: ' . $photoBorderRadius,
                'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'color: white',
                'display: flex',
                'align-items: center',
                'justify-content: center',
                'font-size: calc(' . esc_attr($photoDimension) . ' / 3)',
                'font-weight: 600'
            ];

            $html .= sprintf(
                '<div class="tb4-team-member__placeholder" style="%s">%s</div>',
                implode('; ', $placeholderStyles),
                esc_html($initials)
            );
        }
        $html .= '</div>';

        // Content section
        $html .= '<div class="tb4-team-member__content" style="' . implode('; ', $contentStyles) . '">';

        // Name
        $nameStyle = sprintf(
            'margin: 0 0 4px 0; font-size: %s; color: %s; font-weight: %s',
            esc_attr($nameSize),
            $layout === 'overlay' ? '#ffffff' : esc_attr($nameColor),
            esc_attr($nameWeight)
        );
        $html .= sprintf('<h3 class="tb4-team-member__name" style="%s">%s</h3>', $nameStyle, esc_html($name));

        // Position
        if ($position) {
            $posStyle = sprintf(
                'margin: 0 0 12px 0; font-size: %s; color: %s',
                esc_attr($positionSize),
                $layout === 'overlay' ? 'rgba(255,255,255,0.8)' : esc_attr($positionColor)
            );
            $html .= sprintf('<p class="tb4-team-member__position" style="%s">%s</p>', $posStyle, esc_html($position));
        }

        // Bio
        if ($bio && $layout !== 'overlay') {
            $bioStyle = sprintf(
                'margin: 0 0 16px 0; font-size: %s; color: %s; line-height: 1.6',
                esc_attr($bioSize),
                esc_attr($bioColor)
            );
            $html .= sprintf('<p class="tb4-team-member__bio" style="%s">%s</p>', $bioStyle, esc_html($bio));
        }

        // Contact info
        if (($email || $phone) && $layout !== 'overlay') {
            $contactStyle = 'margin: 0 0 16px 0; font-size: 13px; color: ' . esc_attr($bioColor);
            $html .= '<div class="tb4-team-member__contact" style="' . $contactStyle . '">';
            if ($email) {
                $html .= sprintf(
                    '<div style="margin-bottom: 4px"><a href="mailto:%s" style="color: inherit; text-decoration: none">%s</a></div>',
                    esc_attr($email),
                    esc_html($email)
                );
            }
            if ($phone) {
                $html .= sprintf(
                    '<div><a href="tel:%s" style="color: inherit; text-decoration: none">%s</a></div>',
                    esc_attr(preg_replace('/[^0-9+]/', '', $phone)),
                    esc_html($phone)
                );
            }
            $html .= '</div>';
        }

        // Social links
        $socialLinks = [
            'facebook' => $facebookUrl,
            'twitter' => $twitterUrl,
            'linkedin' => $linkedinUrl,
            'instagram' => $instagramUrl,
            'github' => $githubUrl
        ];
        $hasSocial = array_filter($socialLinks);

        if (!empty($hasSocial)) {
            $socialContainerStyle = 'display: flex; gap: 12px; justify-content: ' . ($textAlign === 'center' ? 'center' : ($textAlign === 'right' ? 'flex-end' : 'flex-start'));
            $html .= '<div class="tb4-team-member__social" style="' . $socialContainerStyle . '">';

            $iconColor = $layout === 'overlay' ? '#ffffff' : $socialIconColor;

            foreach ($socialLinks as $platform => $url) {
                if (empty($url)) {
                    continue;
                }

                $icon = $this->get_social_icon($platform, $socialIconSize, $iconColor, $socialIconStyle);
                if ($icon) {
                    $linkStyle = 'display: inline-flex; align-items: center; justify-content: center; transition: opacity 0.2s ease';
                    if ($socialIconStyle === 'filled') {
                        $linkStyle .= '; background: ' . esc_attr($iconColor) . '; color: white; padding: 8px; border-radius: 50%';
                        // Invert the icon color for filled style
                        $icon = $this->get_social_icon($platform, $socialIconSize, '#ffffff', $socialIconStyle);
                    }
                    $html .= sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer" class="tb4-team-member__social-link" style="%s" title="%s">%s</a>',
                        esc_attr($url),
                        $linkStyle,
                        esc_attr(ucfirst($platform)),
                        $icon
                    );
                }
            }

            $html .= '</div>';
        }

        $html .= '</div>'; // Close content

        $html .= '</div>'; // Close main wrapper

        // Add scoped CSS for hover effects
        $html .= $this->generate_scoped_css($uniqueId, $layout, $hoverEffect, $socialIconColor);

        return $html;
    }

    /**
     * Generate scoped CSS for hover effects
     */
    private function generate_scoped_css(string $uniqueId, string $layout, string $hoverEffect, string $socialColor): string
    {
        $css = '<style>';

        // Hover effects
        switch ($hoverEffect) {
            case 'lift':
                $css .= "#$uniqueId:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15); }";
                break;
            case 'glow':
                $css .= "#$uniqueId:hover { box-shadow: 0 0 30px rgba(59, 130, 246, 0.3); }";
                break;
            case 'border':
                $css .= "#$uniqueId { border: 2px solid transparent; }";
                $css .= "#$uniqueId:hover { border-color: #3b82f6; }";
                break;
        }

        // Overlay layout specific
        if ($layout === 'overlay') {
            $css .= "#$uniqueId:hover .tb4-team-member__content { opacity: 1; }";
            $css .= "#$uniqueId .tb4-team-member__photo img { width: 100%; height: 300px; object-fit: cover; border-radius: 0; }";
        }

        // Social link hover
        $css .= "#$uniqueId .tb4-team-member__social-link:hover { opacity: 0.7; }";

        $css .= '</style>';

        return $css;
    }
}
