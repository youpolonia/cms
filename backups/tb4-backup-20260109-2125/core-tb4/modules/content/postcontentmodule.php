<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Post Content Module
 * Displays dynamic post/page content with typography controls
 */
class PostContentModule extends Module
{
    public function __construct()
    {
        $this->name = 'Post Content';
        $this->slug = 'post_content';
        $this->icon = 'file-text';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-post-content-preview',
            'wrapper' => '.tb4-post-content-wrapper',
            'body' => '.tb4-post-content-body',
            'heading' => '.tb4-post-content-body h2, .tb4-post-content-body h3, .tb4-post-content-body h4',
            'paragraph' => '.tb4-post-content-body p',
            'link' => '.tb4-post-content-body a',
            'blockquote' => '.tb4-post-content-body blockquote',
            'list' => '.tb4-post-content-body ul, .tb4-post-content-body ol'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'content_source' => [
                'label' => 'Content Source',
                'type' => 'select',
                'options' => [
                    'dynamic' => 'Dynamic (from post)',
                    'custom' => 'Custom Content'
                ],
                'default' => 'dynamic'
            ],
            'custom_content' => [
                'label' => 'Custom Content',
                'type' => 'textarea',
                'default' => ''
            ],
            'show_drop_cap' => [
                'label' => 'Show Drop Cap',
                'type' => 'select',
                'options' => [
                    'no' => 'No',
                    'yes' => 'Yes'
                ],
                'default' => 'no'
            ],
            'columns' => [
                'label' => 'Text Columns',
                'type' => 'select',
                'options' => [
                    '1' => '1 Column',
                    '2' => '2 Columns',
                    '3' => '3 Columns'
                ],
                'default' => '1'
            ],
            'column_gap' => [
                'label' => 'Column Gap',
                'type' => 'text',
                'default' => '40px'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'text',
                'default' => '16px'
            ],
            'line_height' => [
                'label' => 'Line Height',
                'type' => 'text',
                'default' => '1.8'
            ],
            'font_weight' => [
                'label' => 'Font Weight',
                'type' => 'select',
                'options' => [
                    '300' => 'Light',
                    '400' => 'Normal',
                    '500' => 'Medium'
                ],
                'default' => '400'
            ],
            'paragraph_spacing' => [
                'label' => 'Paragraph Spacing',
                'type' => 'text',
                'default' => '24px'
            ],
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right',
                    'justify' => 'Justify'
                ],
                'default' => 'left'
            ],
            'max_width' => [
                'label' => 'Max Width',
                'type' => 'text',
                'default' => '100%'
            ],
            'heading_color' => [
                'label' => 'Headings Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'h2_font_size' => [
                'label' => 'H2 Font Size',
                'type' => 'text',
                'default' => '28px'
            ],
            'h3_font_size' => [
                'label' => 'H3 Font Size',
                'type' => 'text',
                'default' => '22px'
            ],
            'h4_font_size' => [
                'label' => 'H4 Font Size',
                'type' => 'text',
                'default' => '18px'
            ],
            'heading_margin_top' => [
                'label' => 'Heading Margin Top',
                'type' => 'text',
                'default' => '32px'
            ],
            'heading_margin_bottom' => [
                'label' => 'Heading Margin Bottom',
                'type' => 'text',
                'default' => '16px'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'link_hover_color' => [
                'label' => 'Link Hover Color',
                'type' => 'color',
                'default' => '#1d4ed8'
            ],
            'drop_cap_color' => [
                'label' => 'Drop Cap Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'drop_cap_size' => [
                'label' => 'Drop Cap Size',
                'type' => 'text',
                'default' => '64px'
            ],
            'blockquote_border_color' => [
                'label' => 'Blockquote Border',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'blockquote_bg_color' => [
                'label' => 'Blockquote Background',
                'type' => 'color',
                'default' => '#f9fafb'
            ],
            'list_style' => [
                'label' => 'List Style',
                'type' => 'select',
                'options' => [
                    'disc' => 'Disc',
                    'circle' => 'Circle',
                    'square' => 'Square',
                    'decimal' => 'Numbers',
                    'none' => 'None'
                ],
                'default' => 'disc'
            ],
            'list_color' => [
                'label' => 'List Marker Color',
                'type' => 'color',
                'default' => '#2563eb'
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

    public function render(array $settings): string
    {
        // Content fields
        $contentSource = $settings['content_source'] ?? 'dynamic';
        $customContent = $settings['custom_content'] ?? '';
        $showDropCap = ($settings['show_drop_cap'] ?? 'no') === 'yes';
        $columns = $settings['columns'] ?? '1';
        $columnGap = $settings['column_gap'] ?? '40px';

        // Design fields
        $textColor = $settings['text_color'] ?? '#374151';
        $fontSize = $settings['font_size'] ?? '16px';
        $lineHeight = $settings['line_height'] ?? '1.8';
        $fontWeight = $settings['font_weight'] ?? '400';
        $paragraphSpacing = $settings['paragraph_spacing'] ?? '24px';
        $textAlign = $settings['text_align'] ?? 'left';
        $maxWidth = $settings['max_width'] ?? '100%';
        $headingColor = $settings['heading_color'] ?? '#111827';
        $h2FontSize = $settings['h2_font_size'] ?? '28px';
        $h3FontSize = $settings['h3_font_size'] ?? '22px';
        $h4FontSize = $settings['h4_font_size'] ?? '18px';
        $headingMarginTop = $settings['heading_margin_top'] ?? '32px';
        $headingMarginBottom = $settings['heading_margin_bottom'] ?? '16px';
        $linkColor = $settings['link_color'] ?? '#2563eb';
        $linkHoverColor = $settings['link_hover_color'] ?? '#1d4ed8';
        $dropCapColor = $settings['drop_cap_color'] ?? '#2563eb';
        $dropCapSize = $settings['drop_cap_size'] ?? '64px';
        $blockquoteBorder = $settings['blockquote_border_color'] ?? '#2563eb';
        $blockquoteBg = $settings['blockquote_bg_color'] ?? '#f9fafb';
        $listStyle = $settings['list_style'] ?? 'disc';
        $listColor = $settings['list_color'] ?? '#2563eb';

        // Build wrapper styles
        $wrapperStyle = 'max-width:' . esc_attr($maxWidth) . ';';
        if ($columns !== '1') {
            $wrapperStyle .= 'column-count:' . esc_attr($columns) . ';';
            $wrapperStyle .= 'column-gap:' . esc_attr($columnGap) . ';';
        }

        // Build body styles
        $bodyStyle = 'font-size:' . esc_attr($fontSize) . ';';
        $bodyStyle .= 'line-height:' . esc_attr($lineHeight) . ';';
        $bodyStyle .= 'font-weight:' . esc_attr($fontWeight) . ';';
        $bodyStyle .= 'color:' . esc_attr($textColor) . ';';
        $bodyStyle .= 'text-align:' . esc_attr($textAlign) . ';';

        $dropCapClass = $showDropCap ? ' drop-cap' : '';

        // Sample content for builder preview
        $sampleContent = '
            <p>This is a sample article content that demonstrates how your post will look when published. The typography settings you choose will be applied to the actual content from your posts.</p>

            <h2 style="font-size:' . esc_attr($h2FontSize) . ';font-weight:700;color:' . esc_attr($headingColor) . ';margin:' . esc_attr($headingMarginTop) . ' 0 ' . esc_attr($headingMarginBottom) . ' 0;">Understanding Web Design Principles</h2>

            <p style="margin:0 0 ' . esc_attr($paragraphSpacing) . ' 0;">Good web design is essential for creating engaging user experiences. It combines aesthetics with functionality to deliver content that resonates with your audience and achieves your business goals.</p>

            <blockquote style="margin:24px 0;padding:20px 24px;background:' . esc_attr($blockquoteBg) . ';border-left:4px solid ' . esc_attr($blockquoteBorder) . ';font-style:italic;color:#4b5563;">
                "Design is not just what it looks like and feels like. Design is how it works." — Steve Jobs
            </blockquote>

            <h3 style="font-size:' . esc_attr($h3FontSize) . ';font-weight:600;color:' . esc_attr($headingColor) . ';margin:28px 0 12px 0;">Key Elements to Consider</h3>

            <p style="margin:0 0 ' . esc_attr($paragraphSpacing) . ' 0;">When designing a website, there are several important factors to keep in mind:</p>

            <ul style="margin:16px 0;padding-left:24px;list-style-type:' . esc_attr($listStyle) . ';">
                <li style="margin-bottom:8px;color:' . esc_attr($textColor) . ';">Visual hierarchy and typography</li>
                <li style="margin-bottom:8px;color:' . esc_attr($textColor) . ';">Color scheme and branding</li>
                <li style="margin-bottom:8px;color:' . esc_attr($textColor) . ';">Navigation and user flow</li>
                <li style="margin-bottom:8px;color:' . esc_attr($textColor) . ';">Responsive design for all devices</li>
            </ul>

            <p style="margin:0 0 ' . esc_attr($paragraphSpacing) . ' 0;">By focusing on these elements, you can create websites that not only look great but also provide excellent user experiences. Remember that good design is invisible — users should focus on content, not be distracted by the interface.</p>
        ';

        // Build HTML
        $html = '<div class="tb4-post-content-preview">';

        // Add drop cap styles if enabled
        if ($showDropCap) {
            $html .= '<style>';
            $html .= '.tb4-post-content-body.drop-cap p:first-of-type::first-letter {';
            $html .= 'float:left;font-size:' . esc_attr($dropCapSize) . ';line-height:1;font-weight:700;';
            $html .= 'margin-right:12px;color:' . esc_attr($dropCapColor) . ';';
            $html .= '}';
            $html .= '</style>';
        }

        // Add link hover styles
        $html .= '<style>';
        $html .= '.tb4-post-content-body a { color:' . esc_attr($linkColor) . ';text-decoration:underline; }';
        $html .= '.tb4-post-content-body a:hover { color:' . esc_attr($linkHoverColor) . '; }';
        $html .= '</style>';

        $html .= '<div class="tb4-post-content-wrapper" style="' . $wrapperStyle . '">';
        $html .= '<div class="tb4-post-content-body' . $dropCapClass . '" style="' . $bodyStyle . '">';
        $html .= $sampleContent;
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
