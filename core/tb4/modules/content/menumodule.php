<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Menu Module
 * Displays a navigation menu with various styles and configurations
 */
class MenuModule extends Module
{
    protected string $name = 'Menu';
    protected string $slug = 'menu';
    protected string $icon = 'menu';
    protected string $category = 'content';

    public function get_content_fields(): array
    {
        return [
            'menu_id' => [
                'label' => 'Select Menu',
                'type' => 'select',
                'options' => [],
                'default' => '',
                'description' => 'Choose a menu from CMS'
            ],
            'menu_style' => [
                'label' => 'Menu Style',
                'type' => 'select',
                'options' => [
                    'horizontal' => 'Horizontal',
                    'vertical' => 'Vertical',
                    'dropdown' => 'Dropdown'
                ],
                'default' => 'horizontal'
            ],
            'menu_alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justified' => 'Justified'
                ],
                'default' => 'left'
            ],
            'show_dropdown_arrow' => [
                'label' => 'Show Dropdown Arrow',
                'type' => 'toggle',
                'default' => 'yes'
            ],
            'dropdown_animation' => [
                'label' => 'Dropdown Animation',
                'type' => 'select',
                'options' => [
                    'fade' => 'Fade',
                    'slide' => 'Slide Down',
                    'expand' => 'Expand',
                    'none' => 'None'
                ],
                'default' => 'fade'
            ],
            'mobile_breakpoint' => [
                'label' => 'Mobile Breakpoint (px)',
                'type' => 'number',
                'default' => 768
            ],
            'mobile_menu_style' => [
                'label' => 'Mobile Menu Style',
                'type' => 'select',
                'options' => [
                    'hamburger' => 'Hamburger',
                    'fullscreen' => 'Fullscreen',
                    'slide' => 'Slide Panel'
                ],
                'default' => 'hamburger'
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $content = $data['content'] ?? $data;
        $style = $content['menu_style'] ?? 'horizontal';
        $alignment = $content['menu_alignment'] ?? 'left';

        $alignMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
            'justified' => 'space-between'
        ];
        $justify = $alignMap[$alignment] ?? 'flex-start';

        $isVertical = $style === 'vertical';
        $flexDir = $isVertical ? 'column' : 'row';
        $gap = $isVertical ? '8px' : '24px';

        $html = '<nav class="tb4-menu tb4-menu--' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '" ';
        $html .= 'style="display:flex;flex-direction:' . $flexDir . ';justify-content:' . $justify . ';gap:' . $gap . ';">';

        // Default menu items for preview
        $items = ['Home', 'About', 'Services', 'Portfolio', 'Contact'];
        foreach ($items as $item) {
            $html .= '<a href="#" style="color:#374151;text-decoration:none;font-weight:500;padding:8px 0;transition:color 0.2s;">';
            $html .= htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            $html .= '</a>';
        }

        $html .= '</nav>';

        return $html;
    }
}
