<?php
/**
 * Fullwidth Code Module
 * Full-width raw code block
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthCode extends JTB_Element
{
    public string $icon = 'code-fullwidth';
    public string $category = 'fullwidth';

    public bool $use_typography = false;
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
        return 'fullwidth_code';
    }

    public function getName(): string
    {
        return 'Fullwidth Code';
    }

    public function getFields(): array
    {
        return [
            'raw_content' => [
                'label' => 'Code',
                'type' => 'code',
                'language' => 'html',
                'default' => '<!-- Add your custom HTML, CSS, or JavaScript here -->'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $rawContent = $attrs['raw_content'] ?? '';

        $innerHtml = '<div class="jtb-fullwidth-code-container">';
        $innerHtml .= $rawContent; // Raw HTML output
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $css .= $selector . ' .jtb-fullwidth-code-container { width: 100%; }' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_code', JTB_Module_FullwidthCode::class);
