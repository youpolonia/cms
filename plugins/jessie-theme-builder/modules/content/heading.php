<?php
/**
 * Heading Module
 * Heading text with multiple levels
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Heading extends JTB_Element
{
    public string $icon = 'heading';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'heading';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [];

    public function getSlug(): string
    {
        return 'heading';
    }

    public function getName(): string
    {
        return 'Heading';
    }

    public function getFields(): array
    {
        return [
            'text' => [
                'label' => 'Heading Text',
                'type' => 'text',
                'default' => 'Your Heading Here'
            ],
            'level' => [
                'label' => 'Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6'
                ],
                'default' => 'h2'
            ],
            'link_url' => [
                'label' => 'Link URL',
                'type' => 'url',
                'description' => 'Optional link for the heading'
            ],
            'link_target' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false,
                'show_if_not' => ['link_url' => '']
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $text = $attrs['text'] ?? 'Your Heading Here';
        $level = $attrs['level'] ?? 'h2';
        $linkUrl = $attrs['link_url'] ?? '';
        $linkTarget = !empty($attrs['link_target']) ? ' target="_blank" rel="noopener noreferrer"' : '';

        // Validate heading level
        $validLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        if (!in_array($level, $validLevels)) {
            $level = 'h2';
        }

        // Clean up any markdown syntax that might be in the text
        // Remove ** markdown bold markers
        $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text);
        // Remove * markdown italic markers
        $text = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '$1', $text);
        // Remove __ markdown bold markers
        $text = preg_replace('/__([^_]+)__/', '$1', $text);
        // Remove _ markdown italic markers
        $text = preg_replace('/(?<!_)_([^_]+)_(?!_)/', '$1', $text);

        // Build heading content
        $headingContent = $this->esc($text);

        if (!empty($linkUrl)) {
            $headingContent = '<a href="' . $this->esc($linkUrl) . '"' . $linkTarget . ' class="jtb-heading-link">' . $headingContent . '</a>';
        }

        $innerHtml = '<' . $level . ' class="jtb-heading-text">' . $headingContent . '</' . $level . '>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Heading module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Reset default heading margins
        $css .= $selector . ' .jtb-heading-text { margin: 0; }' . "\n";

        // Link styling
        if (!empty($attrs['link_url'])) {
            $css .= $selector . ' .jtb-heading-link { text-decoration: none; color: inherit; }' . "\n";
            $css .= $selector . ' .jtb-heading-link:hover { text-decoration: underline; }' . "\n";
        }

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

// Register module
JTB_Registry::register('heading', JTB_Module_Heading::class);
