<?php
/**
 * Tabs Item Module (Child)
 * Single tab panel
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_TabsItem extends JTB_Element
{
    public string $icon = 'tab-item';
    public string $category = 'interactive';
    public bool $is_child = true;

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'tabs_item';
    }

    public function getName(): string
    {
        return 'Tab';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Tab Title',
                'type' => 'text',
                'default' => 'Tab Title'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'richtext',
                'default' => '<p>Your tab content goes here.</p>'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $title = $this->esc($attrs['title'] ?? 'Tab Title');
        $bodyContent = $attrs['content'] ?? '<p>Your tab content goes here.</p>';

        // Generate unique ID for tab
        $tabId = 'tab-' . $this->generateId();

        $html = '<div class="jtb-tab-panel" id="' . $tabId . '" data-tab-title="' . $title . '" role="tabpanel">';
        $html .= $bodyContent;
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        return parent::generateCss($attrs, $selector);
    }
}

JTB_Registry::register('tabs_item', JTB_Module_TabsItem::class);
