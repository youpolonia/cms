<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Post Navigation Module
 * Displays previous/next post navigation links
 */
class PostNavModule extends Module
{
    public function __construct()
    {
        $this->name = 'Post Navigation';
        $this->slug = 'post_nav';
        $this->icon = 'arrow-left-right';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-post-nav-preview',
            'wrapper' => '.tb4-post-nav-wrapper',
            'item' => '.tb4-post-nav-item',
            'prev' => '.tb4-post-nav-item.nav-prev',
            'next' => '.tb4-post-nav-item.nav-next',
            'arrow' => '.tb4-post-nav-arrow',
            'thumb' => '.tb4-post-nav-thumb',
            'content' => '.tb4-post-nav-content',
            'label' => '.tb4-post-nav-label',
            'title' => '.tb4-post-nav-title',
            'divider' => '.tb4-post-nav-divider'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'sides' => 'Side by Side',
                    'stacked' => 'Stacked',
                    'minimal' => 'Minimal'
                ],
                'default' => 'sides'
            ],
            'show_thumbnail' => [
                'label' => 'Show Thumbnail',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_label' => [
                'label' => 'Show Label',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'prev_label' => [
                'label' => 'Previous Label',
                'type' => 'text',
                'default' => 'Previous Post'
            ],
            'next_label' => [
                'label' => 'Next Label',
                'type' => 'text',
                'default' => 'Next Post'
            ],
            'show_title' => [
                'label' => 'Show Post Title',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_arrow' => [
                'label' => 'Show Arrow Icons',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'same_category' => [
                'label' => 'Same Category Only',
                'type' => 'select',
                'options' => [
                    'no' => 'No',
                    'yes' => 'Yes'
                ],
                'default' => 'no'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'border_color' => [
                'label' => 'Border Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'border_width' => [
                'label' => 'Border Width',
                'type' => 'text',
                'default' => '1px'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'padding' => [
                'label' => 'Padding',
                'type' => 'text',
                'default' => '24px'
            ],
            'gap' => [
                'label' => 'Gap Between Items',
                'type' => 'text',
                'default' => '24px'
            ],
            'hover_bg_color' => [
                'label' => 'Hover Background',
                'type' => 'color',
                'default' => '#f9fafb'
            ],
            'thumbnail_width' => [
                'label' => 'Thumbnail Width',
                'type' => 'text',
                'default' => '80px'
            ],
            'thumbnail_height' => [
                'label' => 'Thumbnail Height',
                'type' => 'text',
                'default' => '80px'
            ],
            'thumbnail_radius' => [
                'label' => 'Thumbnail Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'label_font_size' => [
                'label' => 'Label Font Size',
                'type' => 'text',
                'default' => '13px'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'title_font_weight' => [
                'label' => 'Title Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold'
                ],
                'default' => '600'
            ],
            'title_hover_color' => [
                'label' => 'Title Hover Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'arrow_color' => [
                'label' => 'Arrow Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'arrow_size' => [
                'label' => 'Arrow Size',
                'type' => 'text',
                'default' => '24px'
            ],
            'divider_color' => [
                'label' => 'Divider Color',
                'type' => 'color',
                'default' => '#e5e7eb'
            ],
            'divider_style' => [
                'label' => 'Divider Style',
                'type' => 'select',
                'options' => [
                    'solid' => 'Solid Line',
                    'dashed' => 'Dashed',
                    'none' => 'None'
                ],
                'default' => 'solid'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return [
            'css_id' => [
                'label' => 'CSS ID',
                'type' => 'text',
                'default' => ''
            ],
            'css_class' => [
                'label' => 'CSS Class',
                'type' => 'text',
                'default' => ''
            ],
            'custom_css' => [
                'label' => 'Custom CSS',
                'type' => 'textarea',
                'default' => ''
            ]
        ];
    }

    /**
     * Get sample posts for preview
     */
    private function getSamplePosts(): array
    {
        return [
            'prev' => [
                'title' => 'Getting Started with Modern CSS Techniques',
                'thumbnail' => null
            ],
            'next' => [
                'title' => 'Building Responsive Layouts with Flexbox',
                'thumbnail' => null
            ]
        ];
    }

    public function render(array $settings): string
    {
        // Content fields
        $layout = $settings['layout'] ?? 'sides';
        $showThumbnail = ($settings['show_thumbnail'] ?? 'yes') === 'yes';
        $showLabel = ($settings['show_label'] ?? 'yes') === 'yes';
        $prevLabel = $settings['prev_label'] ?? 'Previous Post';
        $nextLabel = $settings['next_label'] ?? 'Next Post';
        $showTitle = ($settings['show_title'] ?? 'yes') === 'yes';
        $showArrow = ($settings['show_arrow'] ?? 'yes') === 'yes';

        // Design fields
        $bgColor = $settings['background_color'] ?? '#ffffff';
        $borderColor = $settings['border_color'] ?? '#e5e7eb';
        $borderWidth = $settings['border_width'] ?? '1px';
        $borderRadius = $settings['border_radius'] ?? '12px';
        $padding = $settings['padding'] ?? '24px';
        $gap = $settings['gap'] ?? '24px';
        $hoverBgColor = $settings['hover_bg_color'] ?? '#f9fafb';
        $thumbWidth = $settings['thumbnail_width'] ?? '80px';
        $thumbHeight = $settings['thumbnail_height'] ?? '80px';
        $thumbRadius = $settings['thumbnail_radius'] ?? '8px';
        $labelColor = $settings['label_color'] ?? '#6b7280';
        $labelSize = $settings['label_font_size'] ?? '13px';
        $titleColor = $settings['title_color'] ?? '#111827';
        $titleSize = $settings['title_font_size'] ?? '16px';
        $titleWeight = $settings['title_font_weight'] ?? '600';
        $titleHoverColor = $settings['title_hover_color'] ?? '#2563eb';
        $arrowColor = $settings['arrow_color'] ?? '#9ca3af';
        $arrowSize = $settings['arrow_size'] ?? '24px';
        $dividerColor = $settings['divider_color'] ?? '#e5e7eb';
        $dividerStyle = $settings['divider_style'] ?? 'solid';

        // Get sample posts
        $posts = $this->getSamplePosts();

        // Build unique ID for scoped styles
        $uniqueId = 'tb4-post-nav-' . uniqid();

        // Determine layout class and styles
        $layoutClass = ' layout-' . $layout;
        $flexDir = $layout === 'stacked' ? 'flex-direction:column;' : '';
        $justifyContent = $layout === 'minimal' ? 'justify-content:space-between;align-items:center;' : '';

        // Build HTML
        $html = '<div class="tb4-post-nav-preview" id="' . esc_attr($uniqueId) . '">';
        $html .= '<div class="tb4-post-nav-wrapper' . esc_attr($layoutClass) . '" style="display:flex;gap:' . esc_attr($gap) . ';' . $flexDir . $justifyContent . '">';

        // Previous post
        $itemStyle = 'flex:1;display:flex;align-items:center;gap:16px;padding:' . esc_attr($padding) . ';background:' . esc_attr($bgColor) . ';border:' . esc_attr($borderWidth) . ' solid ' . esc_attr($borderColor) . ';border-radius:' . esc_attr($borderRadius) . ';text-decoration:none;transition:all 0.2s;cursor:pointer;';

        if ($layout === 'minimal') {
            $itemStyle = 'flex:0 0 auto;display:flex;align-items:center;gap:16px;padding:12px 20px;background:' . esc_attr($bgColor) . ';border:' . esc_attr($borderWidth) . ' solid ' . esc_attr($borderColor) . ';border-radius:' . esc_attr($borderRadius) . ';text-decoration:none;transition:all 0.2s;cursor:pointer;';
        }

        $html .= '<a class="tb4-post-nav-item nav-prev" href="#" style="' . $itemStyle . 'flex-direction:row;">';

        // Arrow
        if ($showArrow) {
            $html .= '<span class="tb4-post-nav-arrow" style="flex-shrink:0;color:' . esc_attr($arrowColor) . ';">';
            $html .= '<svg width="' . esc_attr($arrowSize) . '" height="' . esc_attr($arrowSize) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>';
            $html .= '</span>';
        }

        // Thumbnail
        if ($showThumbnail && $layout !== 'minimal') {
            $html .= '<div class="tb4-post-nav-thumb" style="flex-shrink:0;width:' . esc_attr($thumbWidth) . ';height:' . esc_attr($thumbHeight) . ';background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);border-radius:' . esc_attr($thumbRadius) . ';display:flex;align-items:center;justify-content:center;color:#9ca3af;">';
            $html .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
            $html .= '</div>';
        }

        // Content
        $html .= '<div class="tb4-post-nav-content" style="flex:1;min-width:0;">';
        if ($showLabel) {
            $html .= '<div class="tb4-post-nav-label" style="font-size:' . esc_attr($labelSize) . ';color:' . esc_attr($labelColor) . ';margin-bottom:4px;">' . esc_html($prevLabel) . '</div>';
        }
        if ($showTitle) {
            $html .= '<div class="tb4-post-nav-title" style="font-size:' . esc_attr($titleSize) . ';font-weight:' . esc_attr($titleWeight) . ';color:' . esc_attr($titleColor) . ';line-height:1.4;">' . esc_html($posts['prev']['title']) . '</div>';
        }
        $html .= '</div></a>';

        // Divider
        if ($dividerStyle !== 'none' && $layout !== 'minimal') {
            $dividerBorder = $dividerStyle === 'dashed' ? 'dashed' : 'solid';
            if ($layout === 'stacked') {
                $html .= '<div class="tb4-post-nav-divider" style="width:100%;height:0;border-top:1px ' . $dividerBorder . ' ' . esc_attr($dividerColor) . ';"></div>';
            } else {
                $html .= '<div class="tb4-post-nav-divider" style="width:0;align-self:stretch;border-left:1px ' . $dividerBorder . ' ' . esc_attr($dividerColor) . ';"></div>';
            }
        }

        // Next post
        $html .= '<a class="tb4-post-nav-item nav-next" href="#" style="' . $itemStyle . 'flex-direction:row-reverse;text-align:right;">';

        // Arrow
        if ($showArrow) {
            $html .= '<span class="tb4-post-nav-arrow" style="flex-shrink:0;color:' . esc_attr($arrowColor) . ';">';
            $html .= '<svg width="' . esc_attr($arrowSize) . '" height="' . esc_attr($arrowSize) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>';
            $html .= '</span>';
        }

        // Thumbnail
        if ($showThumbnail && $layout !== 'minimal') {
            $html .= '<div class="tb4-post-nav-thumb" style="flex-shrink:0;width:' . esc_attr($thumbWidth) . ';height:' . esc_attr($thumbHeight) . ';background:linear-gradient(135deg,#e5e7eb 0%,#f3f4f6 100%);border-radius:' . esc_attr($thumbRadius) . ';display:flex;align-items:center;justify-content:center;color:#9ca3af;">';
            $html .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>';
            $html .= '</div>';
        }

        // Content
        $html .= '<div class="tb4-post-nav-content" style="flex:1;min-width:0;">';
        if ($showLabel) {
            $html .= '<div class="tb4-post-nav-label" style="font-size:' . esc_attr($labelSize) . ';color:' . esc_attr($labelColor) . ';margin-bottom:4px;">' . esc_html($nextLabel) . '</div>';
        }
        if ($showTitle) {
            $html .= '<div class="tb4-post-nav-title" style="font-size:' . esc_attr($titleSize) . ';font-weight:' . esc_attr($titleWeight) . ';color:' . esc_attr($titleColor) . ';line-height:1.4;">' . esc_html($posts['next']['title']) . '</div>';
        }
        $html .= '</div></a>';

        $html .= '</div>'; // Close wrapper

        // Scoped hover styles
        $html .= '<style>';
        $html .= '#' . $uniqueId . ' .tb4-post-nav-item:hover { background: ' . esc_attr($hoverBgColor) . ' !important; }';
        $html .= '#' . $uniqueId . ' .tb4-post-nav-item:hover .tb4-post-nav-title { color: ' . esc_attr($titleHoverColor) . ' !important; }';
        $html .= '</style>';

        $html .= '</div>'; // Close preview

        return $html;
    }
}
