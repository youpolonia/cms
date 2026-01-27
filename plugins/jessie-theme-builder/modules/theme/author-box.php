<?php
/**
 * Author Box Module
 * Displays author information with avatar and bio
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Author_Box extends JTB_Element
{
    public string $slug = 'author_box';
    public string $name = 'Author Box';
    public string $icon = 'user';
    public string $category = 'theme';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;

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
            'show_avatar' => [
                'label' => 'Show Avatar',
                'type' => 'toggle',
                'default' => true
            ],
            'avatar_size' => [
                'label' => 'Avatar Size',
                'type' => 'range',
                'min' => 50,
                'max' => 200,
                'step' => 10,
                'default' => 100,
                'unit' => 'px',
                'responsive' => true
            ],
            'avatar_style' => [
                'label' => 'Avatar Style',
                'type' => 'select',
                'options' => [
                    'circle' => 'Circle',
                    'rounded' => 'Rounded',
                    'square' => 'Square'
                ],
                'default' => 'circle'
            ],
            'show_name' => [
                'label' => 'Show Name',
                'type' => 'toggle',
                'default' => true
            ],
            'name_size' => [
                'label' => 'Name Font Size',
                'type' => 'range',
                'min' => 14,
                'max' => 32,
                'step' => 1,
                'default' => 20,
                'unit' => 'px'
            ],
            'show_role' => [
                'label' => 'Show Role/Title',
                'type' => 'toggle',
                'default' => true
            ],
            'show_bio' => [
                'label' => 'Show Bio',
                'type' => 'toggle',
                'default' => true
            ],
            'show_website' => [
                'label' => 'Show Website Link',
                'type' => 'toggle',
                'default' => false
            ],
            'show_social' => [
                'label' => 'Show Social Links',
                'type' => 'toggle',
                'default' => true
            ],
            'show_posts_link' => [
                'label' => 'Show View All Posts',
                'type' => 'toggle',
                'default' => true
            ],
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'horizontal' => 'Horizontal',
                    'vertical' => 'Vertical (centered)'
                ],
                'default' => 'horizontal'
            ],
            'name_color' => [
                'label' => 'Name Color',
                'type' => 'color',
                'default' => '#1f2937'
            ],
            'role_color' => [
                'label' => 'Role Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'bio_color' => [
                'label' => 'Bio Color',
                'type' => 'color',
                'default' => '#4b5563'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#7c3aed',
                'hover' => true
            ],
            'box_background' => [
                'label' => 'Box Background',
                'type' => 'color',
                'default' => '#f9fafb'
            ],
            'box_border_color' => [
                'label' => 'Box Border Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'box_border_radius' => [
                'label' => 'Box Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 24,
                'step' => 1,
                'default' => 8,
                'unit' => 'px'
            ],
            'box_padding' => [
                'label' => 'Box Padding',
                'type' => 'range',
                'min' => 10,
                'max' => 60,
                'step' => 5,
                'default' => 30,
                'unit' => 'px'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $id = $attrs['id'] ?? 'author_box_' . uniqid();
        $showAvatar = $attrs['show_avatar'] ?? true;
        $avatarSize = $attrs['avatar_size'] ?? 100;
        $avatarStyle = $attrs['avatar_style'] ?? 'circle';
        $showName = $attrs['show_name'] ?? true;
        $showRole = $attrs['show_role'] ?? true;
        $showBio = $attrs['show_bio'] ?? true;
        $showSocial = $attrs['show_social'] ?? true;
        $showPostsLink = $attrs['show_posts_link'] ?? true;
        $layout = $attrs['layout'] ?? 'horizontal';

        $classes = ['jtb-author-box', 'jtb-author-layout-' . $this->esc($layout)];

        // Social icons SVG
        $twitterIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>';
        $facebookIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>';
        $linkedinIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>';
        $instagramIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>';

        // Avatar placeholder SVG
        $avatarRadius = $avatarStyle === 'circle' ? '50' : ($avatarStyle === 'rounded' ? '8' : '0');
        $avatarSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $avatarSize . '" height="' . $avatarSize . '" viewBox="0 0 100 100"><rect fill="#e5e7eb" width="100" height="100" rx="' . $avatarRadius . '"/><circle fill="#9ca3af" cx="50" cy="38" r="18"/><ellipse fill="#9ca3af" cx="50" cy="82" rx="30" ry="22"/></svg>';

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';
        $html .= '<div class="jtb-author-inner">';

        // Avatar
        if ($showAvatar) {
            $html .= '<div class="jtb-author-avatar">';
            $html .= '<img src="data:image/svg+xml,' . rawurlencode($avatarSvg) . '" alt="Author" class="jtb-avatar-img" />';
            $html .= '</div>';
        }

        // Content
        $html .= '<div class="jtb-author-content">';

        if ($showName) {
            $html .= '<h4 class="jtb-author-name">Author Name</h4>';
        }

        if ($showRole) {
            $html .= '<p class="jtb-author-role">Content Writer</p>';
        }

        if ($showBio) {
            $html .= '<p class="jtb-author-bio">This is where the author bio will be displayed. It provides a brief introduction about the author, their expertise, and background.</p>';
        }

        if ($showSocial) {
            $html .= '<div class="jtb-author-social">';
            $html .= '<a href="#" class="jtb-social-link" title="Twitter">' . $twitterIcon . '</a>';
            $html .= '<a href="#" class="jtb-social-link" title="Facebook">' . $facebookIcon . '</a>';
            $html .= '<a href="#" class="jtb-social-link" title="LinkedIn">' . $linkedinIcon . '</a>';
            $html .= '<a href="#" class="jtb-social-link" title="Instagram">' . $instagramIcon . '</a>';
            $html .= '</div>';
        }

        if ($showPostsLink) {
            $html .= '<a href="#" class="jtb-author-posts-link">View all posts by Author <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>';
        }

        $html .= '</div>'; // content
        $html .= '</div>'; // inner
        $html .= '</div>'; // main wrapper

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $avatarSize = $attrs['avatar_size'] ?? 100;
        $avatarStyle = $attrs['avatar_style'] ?? 'circle';
        $nameSize = $attrs['name_size'] ?? 20;
        $layout = $attrs['layout'] ?? 'horizontal';
        $nameColor = $attrs['name_color'] ?? '#1f2937';
        $roleColor = $attrs['role_color'] ?? '#6b7280';
        $bioColor = $attrs['bio_color'] ?? '#4b5563';
        $linkColor = $attrs['link_color'] ?? '#7c3aed';
        $boxBg = $attrs['box_background'] ?? '#f9fafb';
        $boxBorder = $attrs['box_border_color'] ?? '#e5e7eb';
        $boxRadius = $attrs['box_border_radius'] ?? 8;
        $boxPadding = $attrs['box_padding'] ?? 30;

        $avatarRadius = $avatarStyle === 'circle' ? '50%' : ($avatarStyle === 'rounded' ? '8px' : '0');

        // Main container
        $css .= $selector . ' { ';
        $css .= 'background: ' . $boxBg . '; ';
        $css .= 'border: 1px solid ' . $boxBorder . '; ';
        $css .= 'border-radius: ' . intval($boxRadius) . 'px; ';
        $css .= 'padding: ' . intval($boxPadding) . 'px; ';
        $css .= '}' . "\n";

        // Inner layout
        if ($layout === 'horizontal') {
            $css .= $selector . ' .jtb-author-inner { ';
            $css .= 'display: flex; ';
            $css .= 'gap: 24px; ';
            $css .= 'align-items: flex-start; ';
            $css .= '}' . "\n";
        } else {
            $css .= $selector . ' .jtb-author-inner { text-align: center; }' . "\n";
        }

        // Avatar
        $css .= $selector . ' .jtb-author-avatar { flex-shrink: 0; ';
        if ($layout === 'vertical') {
            $css .= 'margin: 0 auto 16px; ';
        }
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-avatar-img { ';
        $css .= 'width: ' . intval($avatarSize) . 'px; ';
        $css .= 'height: ' . intval($avatarSize) . 'px; ';
        $css .= 'border-radius: ' . $avatarRadius . '; ';
        $css .= 'object-fit: cover; ';
        $css .= '}' . "\n";

        // Content
        $css .= $selector . ' .jtb-author-content { flex: 1; }' . "\n";

        // Name
        $css .= $selector . ' .jtb-author-name { ';
        $css .= 'margin: 0 0 4px; ';
        $css .= 'font-size: ' . intval($nameSize) . 'px; ';
        $css .= 'font-weight: 600; ';
        $css .= 'color: ' . $nameColor . '; ';
        $css .= '}' . "\n";

        // Role
        $css .= $selector . ' .jtb-author-role { ';
        $css .= 'margin: 0 0 12px; ';
        $css .= 'font-size: 14px; ';
        $css .= 'color: ' . $roleColor . '; ';
        $css .= '}' . "\n";

        // Bio
        $css .= $selector . ' .jtb-author-bio { ';
        $css .= 'margin: 0 0 16px; ';
        $css .= 'color: ' . $bioColor . '; ';
        $css .= 'line-height: 1.6; ';
        $css .= '}' . "\n";

        // Social links
        $css .= $selector . ' .jtb-author-social { ';
        $css .= 'display: flex; ';
        $css .= 'gap: 12px; ';
        $css .= 'margin-bottom: 12px; ';
        if ($layout === 'vertical') {
            $css .= 'justify-content: center; ';
        }
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-social-link { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'opacity: 0.7; ';
        $css .= 'transition: opacity 0.3s ease, transform 0.3s ease; ';
        $css .= 'display: inline-flex; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-social-link:hover { ';
        $css .= 'opacity: 1; ';
        $css .= 'transform: translateY(-2px); ';
        $css .= '}' . "\n";

        // Posts link
        $css .= $selector . ' .jtb-author-posts-link { ';
        $css .= 'display: inline-flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: 6px; ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'font-weight: 500; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: gap 0.3s ease; ';
        $css .= '}' . "\n";

        if (!empty($attrs['link_color__hover'])) {
            $css .= $selector . ' .jtb-author-posts-link:hover { color: ' . $attrs['link_color__hover'] . '; gap: 10px; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-author-posts-link:hover { gap: 10px; }' . "\n";
        }

        // Responsive
        if (!empty($attrs['avatar_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-avatar-img { width: ' . intval($attrs['avatar_size__tablet']) . 'px; height: ' . intval($attrs['avatar_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['avatar_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-avatar-img { width: ' . intval($attrs['avatar_size__phone']) . 'px; height: ' . intval($attrs['avatar_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Mobile: switch to vertical layout
        $css .= '@media (max-width: 767px) { ';
        $css .= $selector . ' .jtb-author-inner { flex-direction: column; align-items: center; text-align: center; }';
        $css .= $selector . ' .jtb-author-avatar { margin-bottom: 16px; }';
        $css .= $selector . ' .jtb-author-social { justify-content: center; }';
        $css .= ' }' . "\n";

        return $css;
    }
}

JTB_Registry::register('author_box', JTB_Module_Author_Box::class);
