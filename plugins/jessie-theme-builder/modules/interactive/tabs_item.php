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

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'tabs_item';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [];

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
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Tab Title');
        $bodyContent = $attrs['content'] ?? '<p>Your tab content goes here.</p>';

        // Generate unique ID for tab
        $tabId = 'tab-' . $this->generateId();

        $html = '<div class="jtb-tab-panel" id="' . $tabId . '" data-tab-title="' . $title . '" role="tabpanel">';
        $html .= $bodyContent;
        $html .= '</div>';

        return $html;
    }

    /**
     * Generate CSS for Tabs Item module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('tabs_item', JTB_Module_TabsItem::class);
