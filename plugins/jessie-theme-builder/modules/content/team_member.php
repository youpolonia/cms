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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Text alignment
        if (!empty($attrs['text_orientation'])) {
            $css .= $selector . ' .jtb-team-member-container { text-align: ' . $attrs['text_orientation'] . '; }' . "\n";
        }

        // Image styling
        $imageRadius = $attrs['image_border_radius'] ?? 0;
        $css .= $selector . ' .jtb-team-member-image img { ';
        $css .= 'width: 100%; ';
        $css .= 'border-radius: ' . $imageRadius . '%; ';
        $css .= '}' . "\n";

        // Position styling
        $css .= $selector . ' .jtb-team-member-position { font-size: 0.9em; color: #666; margin-bottom: 10px; }' . "\n";

        // Bio styling
        $css .= $selector . ' .jtb-team-member-bio { margin-bottom: 15px; }' . "\n";

        // Social icons
        $iconColor = $attrs['icon_color'] ?? '#2ea3f2';
        $iconSize = $attrs['icon_size'] ?? 20;

        $css .= $selector . ' .jtb-team-member-social { display: flex; gap: 10px; justify-content: center; }' . "\n";

        if (!empty($attrs['text_orientation'])) {
            $justify = $attrs['text_orientation'];
            if ($justify === 'left') {
                $justify = 'flex-start';
            } elseif ($justify === 'right') {
                $justify = 'flex-end';
            }
            $css .= $selector . ' .jtb-team-member-social { justify-content: ' . $justify . '; }' . "\n";
        }

        $css .= $selector . ' .jtb-social-icon { ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'font-size: ' . $iconSize . 'px; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        if (!empty($attrs['icon_color__hover'])) {
            $css .= $selector . ' .jtb-social-icon:hover { color: ' . $attrs['icon_color__hover'] . '; }' . "\n";
        }

        // Responsive
        if (!empty($attrs['text_orientation__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-team-member-container { text-align: ' . $attrs['text_orientation__tablet'] . '; } }' . "\n";
        }
        if (!empty($attrs['text_orientation__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-team-member-container { text-align: ' . $attrs['text_orientation__phone'] . '; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('team_member', JTB_Module_TeamMember::class);
