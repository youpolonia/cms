<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

class SidebarModule extends Module
{
    protected string $name = 'Sidebar';
    protected string $slug = 'sidebar';
    protected string $icon = 'panel-left';
    protected string $category = 'content';

    public function get_content_fields(): array
    {
        return [
            'sidebar_id' => [
                'label' => 'Select Sidebar',
                'type' => 'select',
                'options' => [
                    'default' => 'Default Sidebar',
                    'blog' => 'Blog Sidebar',
                    'shop' => 'Shop Sidebar',
                    'footer_1' => 'Footer Area 1',
                    'footer_2' => 'Footer Area 2',
                    'footer_3' => 'Footer Area 3'
                ],
                'default' => 'default'
            ],
            'show_title' => [
                'label' => 'Show Widget Titles',
                'type' => 'toggle',
                'default' => 'yes'
            ],
            'widget_spacing' => [
                'label' => 'Widget Spacing (px)',
                'type' => 'number',
                'default' => 30
            ],
            'title_tag' => [
                'label' => 'Title Tag',
                'type' => 'select',
                'options' => [
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'div' => 'DIV'
                ],
                'default' => 'h4'
            ],
            'remove_widget_bg' => [
                'label' => 'Remove Widget Background',
                'type' => 'toggle',
                'default' => 'no'
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $sidebarId = $data['content']['sidebar_id'] ?? 'default';
        $showTitle = ($data['content']['show_title'] ?? 'yes') === 'yes';
        $spacing = intval($data['content']['widget_spacing'] ?? 30);
        $titleTag = $data['content']['title_tag'] ?? 'h4';

        $sidebarNames = [
            'default' => 'Default Sidebar',
            'blog' => 'Blog Sidebar',
            'shop' => 'Shop Sidebar',
            'footer_1' => 'Footer Area 1',
            'footer_2' => 'Footer Area 2',
            'footer_3' => 'Footer Area 3'
        ];
        $sidebarName = $sidebarNames[$sidebarId] ?? 'Sidebar';

        $html = '<div class="tb4-sidebar" style="display:flex;flex-direction:column;gap:' . $spacing . 'px;">';

        $widgets = [
            ['title' => 'Search', 'icon' => 'search'],
            ['title' => 'Recent Posts', 'icon' => 'file-text'],
            ['title' => 'Categories', 'icon' => 'folder']
        ];

        foreach ($widgets as $widget) {
            $html .= '<div class="tb4-widget" style="background:#f9fafb;padding:20px;border-radius:8px;">';
            if ($showTitle) {
                $html .= '<' . htmlspecialchars($titleTag) . ' style="margin:0 0 12px;font-size:16px;font-weight:600;color:#111827;">';
                $html .= '<i data-lucide="' . $widget['icon'] . '" style="width:16px;height:16px;display:inline;margin-right:8px;vertical-align:middle;"></i>';
                $html .= $widget['title'];
                $html .= '</' . htmlspecialchars($titleTag) . '>';
            }
            $html .= '<div style="color:#6b7280;font-size:14px;">Widget content area</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
