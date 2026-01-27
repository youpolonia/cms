<?php
/**
 * Social Follow Item Module (Child)
 * Single social network icon/link
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_SocialFollowItem extends JTB_Element
{
    public string $icon = 'share-2';
    public string $category = 'content';
    public bool $is_child = true;

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    private array $networks = [
        'facebook' => ['label' => 'Facebook', 'color' => '#1877f2', 'icon' => 'facebook'],
        'twitter' => ['label' => 'Twitter/X', 'color' => '#1da1f2', 'icon' => 'twitter'],
        'instagram' => ['label' => 'Instagram', 'color' => '#e4405f', 'icon' => 'instagram'],
        'linkedin' => ['label' => 'LinkedIn', 'color' => '#0077b5', 'icon' => 'linkedin'],
        'youtube' => ['label' => 'YouTube', 'color' => '#ff0000', 'icon' => 'youtube'],
        'pinterest' => ['label' => 'Pinterest', 'color' => '#bd081c', 'icon' => 'pinterest'],
        'tiktok' => ['label' => 'TikTok', 'color' => '#000000', 'icon' => 'music'],
        'github' => ['label' => 'GitHub', 'color' => '#333333', 'icon' => 'github'],
        'dribbble' => ['label' => 'Dribbble', 'color' => '#ea4c89', 'icon' => 'dribbble'],
        'behance' => ['label' => 'Behance', 'color' => '#1769ff', 'icon' => 'figma'],
        'twitch' => ['label' => 'Twitch', 'color' => '#9146ff', 'icon' => 'twitch'],
        'discord' => ['label' => 'Discord', 'color' => '#5865f2', 'icon' => 'message-circle'],
        'telegram' => ['label' => 'Telegram', 'color' => '#0088cc', 'icon' => 'send'],
        'whatsapp' => ['label' => 'WhatsApp', 'color' => '#25d366', 'icon' => 'phone'],
        'snapchat' => ['label' => 'Snapchat', 'color' => '#fffc00', 'icon' => 'camera'],
        'reddit' => ['label' => 'Reddit', 'color' => '#ff4500', 'icon' => 'message-square'],
        'spotify' => ['label' => 'Spotify', 'color' => '#1db954', 'icon' => 'music'],
        'soundcloud' => ['label' => 'SoundCloud', 'color' => '#ff5500', 'icon' => 'cloud'],
        'email' => ['label' => 'Email', 'color' => '#666666', 'icon' => 'mail'],
        'website' => ['label' => 'Website', 'color' => '#333333', 'icon' => 'globe'],
        'rss' => ['label' => 'RSS Feed', 'color' => '#f26522', 'icon' => 'rss'],
    ];

    public function getSlug(): string
    {
        return 'social_follow_item';
    }

    public function getName(): string
    {
        return 'Social Network';
    }

    public function getFields(): array
    {
        $networkOptions = [];
        foreach ($this->networks as $key => $data) {
            $networkOptions[$key] = $data['label'];
        }

        return [
            'social_network' => [
                'label' => 'Social Network',
                'type' => 'select',
                'options' => $networkOptions,
                'default' => 'facebook'
            ],
            'url' => [
                'label' => 'Profile URL',
                'type' => 'url',
                'default' => '#'
            ],
            'content' => [
                'label' => 'Custom Label (optional)',
                'type' => 'text',
                'default' => ''
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '',
                'hover' => true
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'use_brand_color' => [
                'label' => 'Use Brand Color',
                'type' => 'toggle',
                'default' => true
            ],
            'skype_action' => [
                'label' => 'Skype Action',
                'type' => 'select',
                'options' => [
                    'call' => 'Call',
                    'chat' => 'Chat'
                ],
                'default' => 'call',
                'show_if' => ['social_network' => 'skype']
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $network = $attrs['social_network'] ?? 'facebook';
        $url = $attrs['url'] ?? '#';
        $customLabel = $attrs['content'] ?? '';
        $useBrandColor = $attrs['use_brand_color'] ?? true;

        $networkData = $this->networks[$network] ?? $this->networks['facebook'];
        $label = !empty($customLabel) ? $customLabel : $networkData['label'];
        $iconName = $networkData['icon'];

        // Handle email specially
        if ($network === 'email' && strpos($url, 'mailto:') !== 0) {
            $url = 'mailto:' . $url;
        }

        $brandColor = $useBrandColor ? $networkData['color'] : '';

        $html = '<a href="' . $this->esc($url) . '" ';
        $html .= 'class="jtb-social-item jtb-social-' . $this->esc($network) . '" ';
        $html .= 'target="_blank" rel="noopener noreferrer" ';
        $html .= 'title="' . $this->esc($label) . '" ';
        if ($brandColor) {
            $html .= 'data-brand-color="' . $brandColor . '" ';
        }
        $html .= '>';

        // Icon (using Feather icon name or fallback)
        $html .= '<span class="jtb-social-icon" data-icon="' . $this->esc($iconName) . '"></span>';

        $html .= '</a>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);

        $network = $attrs['social_network'] ?? 'facebook';
        $networkData = $this->networks[$network] ?? $this->networks['facebook'];
        $useBrandColor = $attrs['use_brand_color'] ?? true;

        $bgColor = $attrs['background_color'] ?? '';
        $iconColor = $attrs['icon_color'] ?? '#ffffff';

        if (empty($bgColor) && $useBrandColor) {
            $bgColor = $networkData['color'];
        } elseif (empty($bgColor)) {
            $bgColor = '#666666';
        }

        // Base styling
        $css .= $selector . ' { ';
        $css .= 'display: inline-flex; align-items: center; justify-content: center; ';
        $css .= 'width: 40px; height: 40px; ';
        $css .= 'background: ' . $bgColor . '; ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'border-radius: 50%; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: all 0.3s ease; ';
        $css .= '}' . "\n";

        // Hover
        $hoverBg = $attrs['background_color__hover'] ?? '';
        $hoverIcon = $attrs['icon_color__hover'] ?? '';

        if ($hoverBg) {
            $css .= $selector . ':hover { background: ' . $hoverBg . '; }' . "\n";
        } else {
            $css .= $selector . ':hover { filter: brightness(1.15); transform: translateY(-2px); }' . "\n";
        }

        if ($hoverIcon) {
            $css .= $selector . ':hover { color: ' . $hoverIcon . '; }' . "\n";
        }

        // Icon size
        $css .= $selector . ' .jtb-social-icon { font-size: 18px; }' . "\n";

        return $css;
    }
}

JTB_Registry::register('social_follow_item', JTB_Module_SocialFollowItem::class);
