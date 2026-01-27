<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

/**
 * TB 4.0 Social Item Module (Child Module)
 * Individual social link item for use within parent social container modules.
 */
class SocialItemModule extends ChildModule
{
    protected string $name = 'Social Link';
    protected string $slug = 'social_item';
    protected string $icon = 'share-2';
    protected string $category = 'content';
    protected string $type = 'child';
    protected ?string $parent_slug = 'social';
    protected ?string $child_title_var = 'network';

    public function get_content_fields(): array
    {
        return [
            'network' => [
                'label' => 'Social Network',
                'type' => 'select',
                'options' => [
                    'facebook' => 'Facebook',
                    'twitter' => 'Twitter / X',
                    'instagram' => 'Instagram',
                    'linkedin' => 'LinkedIn',
                    'youtube' => 'YouTube',
                    'tiktok' => 'TikTok',
                    'pinterest' => 'Pinterest',
                    'github' => 'GitHub',
                    'dribbble' => 'Dribbble',
                    'behance' => 'Behance',
                    'discord' => 'Discord',
                    'twitch' => 'Twitch',
                    'telegram' => 'Telegram',
                    'whatsapp' => 'WhatsApp',
                    'custom' => 'Custom'
                ],
                'default' => 'facebook'
            ],
            'url' => [
                'label' => 'Profile URL',
                'type' => 'text',
                'default' => '#'
            ],
            'custom_icon' => [
                'label' => 'Custom Icon (Lucide name)',
                'type' => 'text',
                'default' => '',
                'description' => 'Only for Custom network type'
            ],
            'open_new_tab' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => 'yes'
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $network = $data['content']['network'] ?? 'facebook';
        $url = $data['content']['url'] ?? '#';
        $newTab = ($data['content']['open_new_tab'] ?? 'yes') === 'yes' ? ' target="_blank" rel="noopener"' : '';

        $icons = [
            'facebook' => 'facebook',
            'twitter' => 'twitter',
            'instagram' => 'instagram',
            'linkedin' => 'linkedin',
            'youtube' => 'youtube',
            'tiktok' => 'music-2',
            'pinterest' => 'pin',
            'github' => 'github',
            'dribbble' => 'dribbble',
            'behance' => 'pen-tool',
            'discord' => 'message-circle',
            'twitch' => 'twitch',
            'telegram' => 'send',
            'whatsapp' => 'phone'
        ];

        $icon = $icons[$network] ?? ($data['content']['custom_icon'] ?? 'link');

        return '<a href="' . htmlspecialchars($url) . '"' . $newTab . ' class="tb4-social-link" style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:#f3f4f6;color:#374151;text-decoration:none;">
            <i data-lucide="' . htmlspecialchars($icon) . '" style="width:20px;height:20px;"></i>
        </a>';
    }
}
