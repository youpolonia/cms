<?php
/**
 * Code Module
 * Raw HTML/CSS/JS code block
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Code extends JTB_Element
{
    public string $icon = 'code';
    public string $category = 'content';

    public bool $use_typography = false;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'code';
    }

    public function getName(): string
    {
        return 'Code';
    }

    public function getFields(): array
    {
        return [
            'raw_content' => [
                'label' => 'Code',
                'type' => 'codemirror',
                'mode' => 'htmlmixed',
                'description' => 'Enter your HTML, CSS or JavaScript code here.'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $rawContent = $attrs['raw_content'] ?? '';

        // Code module outputs raw content without escaping
        $innerHtml = '<div class="jtb-code-inner">' . $rawContent . '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        return $css;
    }
}

JTB_Registry::register('code', JTB_Module_Code::class);
