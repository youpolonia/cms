<?php
/**
 * Team Member Module
 * Team member profile with photo, name, position and social links
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_TeamMember extends JTB_Element
{
    public string $icon = 'user';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'team_member';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        // Text alignment
        'text_orientation' => [
            'property' => 'text-align',
            'selector' => '.jtb-team-member-container',
            'responsive' => true
        ],
        // Image
        'image_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-team-member-image img',
            'unit' => '%'
        ],
        // Name
        'name_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-team-member-name',
            'unit' => 'px'
        ],
        'name_color' => [
            'property' => 'color',
            'selector' => '.jtb-team-member-name'
        ],
        // Position
        'position_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-team-member-position',
            'unit' => 'px'
        ],
        'position_color' => [
            'property' => 'color',
            'selector' => '.jtb-team-member-position'
        ],
        // Bio
        'bio_color' => [
            'property' => 'color',
            'selector' => '.jtb-team-member-bio'
        ],
        // Social icons
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-social-icon',
            'hover' => true
        ],
        'icon_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-social-icon',
            'unit' => 'px'
        ]
    ];

    public function getSlug(): string
    {
        return 'team_member';
    }

    public function getName(): string
    {
        return 'Team Member';
    }

    public function getFields(): array
    {
        return [
            'name' => [
                'label' => 'Name',
                'type' => 'text',
                'default' => 'John Doe'
            ],
            'position' => [
                'label' => 'Position',
                'type' => 'text',
                'default' => 'CEO'
            ],
            'image_url' => [
                'label' => 'Photo',
                'type' => 'upload'
            ],
            'content' => [
                'label' => 'Biography',
                'type' => 'richtext',
                'default' => '<p>Your team member bio goes here.</p>'
            ],
            'facebook_url' => [
                'label' => 'Facebook URL',
                'type' => 'text'
            ],
            'twitter_url' => [
                'label' => 'Twitter URL',
                'type' => 'text'
            ],
            'linkedin_url' => [
                'label' => 'LinkedIn URL',
                'type' => 'text'
            ],
            'instagram_url' => [
                'label' => 'Instagram URL',
                'type' => 'text'
            ],
            'email' => [
                'label' => 'Email',
                'type' => 'text'
            ],
            'text_orientation' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center',
                'responsive' => true
            ],
            'header_level' => [
                'label' => 'Name Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6'
                ],
                'default' => 'h4'
            ],
            'image_border_radius' => [
                'label' => 'Image Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 50,
                'unit' => '%',
                'default' => 0
            ],
            'icon_color' => [
                'label' => 'Social Icon Color',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'icon_size' => [
                'label' => 'Social Icon Size',
                'type' => 'range',
                'min' => 10,
                'max' => 50,
                'unit' => 'px',
                'default' => 20
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $name = $this->esc($attrs['name'] ?? 'John Doe');
        $position = $this->esc($attrs['position'] ?? 'CEO');
        $image = $attrs['image_url'] ?? '';
        $bio = $attrs['content'] ?? '';
        $headerLevel = $attrs['header_level'] ?? 'h4';

        $socialLinks = [
            'facebook' => $attrs['facebook_url'] ?? '',
            'twitter' => $attrs['twitter_url'] ?? '',
            'linkedin' => $attrs['linkedin_url'] ?? '',
            'instagram' => $attrs['instagram_url'] ?? '',
        ];
        $email = $attrs['email'] ?? '';

        $innerHtml = '<div class="jtb-team-member-container">';

        // Image
        if (!empty($image)) {
            $innerHtml .= '<div class="jtb-team-member-image">';
            $innerHtml .= '<img src="' . $this->esc($image) . '" alt="' . $name . '" />';
            $innerHtml .= '</div>';
        }

        // Name
        $innerHtml .= '<' . $headerLevel . ' class="jtb-team-member-name">' . $name . '</' . $headerLevel . '>';

        // Position
        if (!empty($position)) {
            $innerHtml .= '<div class="jtb-team-member-position">' . $position . '</div>';
        }

        // Bio
        if (!empty($bio)) {
            $innerHtml .= '<div class="jtb-team-member-bio">' . $bio . '</div>';
        }

        // Social links
        $hasSocial = !empty(array_filter($socialLinks)) || !empty($email);
        if ($hasSocial) {
            $innerHtml .= '<div class="jtb-team-member-social">';

            foreach ($socialLinks as $network => $url) {
                if (!empty($url)) {
                    $innerHtml .= '<a href="' . $this->esc($url) . '" target="_blank" rel="noopener" class="jtb-social-icon jtb-social-' . $network . '">';
                    $innerHtml .= '<span class="jtb-icon jtb-icon-' . $network . '"></span>';
                    $innerHtml .= '</a>';
                }
            }

            if (!empty($email)) {
                $innerHtml .= '<a href="mailto:' . $this->esc($email) . '" class="jtb-social-icon jtb-social-email">';
                $innerHtml .= '<span class="jtb-icon jtb-icon-email"></span>';
                $innerHtml .= '</a>';
            }

            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Team Member module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Social icons justify content based on text alignment
        if (!empty($attrs['text_orientation'])) {
            $justify = $attrs['text_orientation'];
            if ($justify === 'left') {
                $justify = 'flex-start';
            } elseif ($justify === 'right') {
                $justify = 'flex-end';
            }
            $css .= $selector . ' .jtb-team-member-social { justify-content: ' . $justify . '; }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('team_member', JTB_Module_TeamMember::class);
