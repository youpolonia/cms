<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Post Title Module
 * Displays dynamic post/page title with meta information
 */
class PostTitleModule extends Module
{
    public function __construct()
    {
        $this->name = 'Post Title';
        $this->slug = 'post_title';
        $this->icon = 'heading';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-post-title-preview',
            'wrapper' => '.tb4-post-title-wrapper',
            'heading' => '.tb4-post-title-heading',
            'meta' => '.tb4-post-title-meta',
            'meta_item' => '.tb4-post-title-meta-item',
            'category' => '.tb4-post-title-category',
            'author' => '.tb4-post-title-author',
            'avatar' => '.tb4-post-title-avatar',
            'author_name' => '.tb4-post-title-author-name',
            'separator' => '.tb4-post-title-separator'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'title_tag' => [
                'label' => 'Title HTML Tag',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'p' => 'Paragraph'
                ],
                'default' => 'h1'
            ],
            'show_meta' => [
                'label' => 'Show Meta Info',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_date' => [
                'label' => 'Show Date',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'date_format' => [
                'label' => 'Date Format',
                'type' => 'select',
                'options' => [
                    'full' => 'January 5, 2026',
                    'short' => 'Jan 5, 2026',
                    'numeric' => '01/05/2026'
                ],
                'default' => 'full'
            ],
            'show_author' => [
                'label' => 'Show Author',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_author_avatar' => [
                'label' => 'Show Author Avatar',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'show_category' => [
                'label' => 'Show Category',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_comments_count' => [
                'label' => 'Show Comments Count',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'show_reading_time' => [
                'label' => 'Show Reading Time',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'meta_separator' => [
                'label' => 'Meta Separator',
                'type' => 'select',
                'options' => [
                    'dot' => 'Dot (•)',
                    'dash' => 'Dash (—)',
                    'slash' => 'Slash (/)',
                    'pipe' => 'Pipe (|)'
                ],
                'default' => 'dot'
            ],
            'meta_position' => [
                'label' => 'Meta Position',
                'type' => 'select',
                'options' => [
                    'below' => 'Below Title',
                    'above' => 'Above Title'
                ],
                'default' => 'below'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'text_align' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'left'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '36px'
            ],
            'title_font_weight' => [
                'label' => 'Title Font Weight',
                'type' => 'select',
                'options' => [
                    '400' => 'Normal',
                    '500' => 'Medium',
                    '600' => 'Semi Bold',
                    '700' => 'Bold',
                    '800' => 'Extra Bold'
                ],
                'default' => '700'
            ],
            'title_line_height' => [
                'label' => 'Title Line Height',
                'type' => 'text',
                'default' => '1.2'
            ],
            'title_margin_bottom' => [
                'label' => 'Title Margin Bottom',
                'type' => 'text',
                'default' => '16px'
            ],
            'meta_color' => [
                'label' => 'Meta Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'meta_font_size' => [
                'label' => 'Meta Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'meta_gap' => [
                'label' => 'Meta Items Gap',
                'type' => 'text',
                'default' => '16px'
            ],
            'category_bg_color' => [
                'label' => 'Category Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'category_text_color' => [
                'label' => 'Category Text',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'category_border_radius' => [
                'label' => 'Category Border Radius',
                'type' => 'text',
                'default' => '4px'
            ],
            'author_link_color' => [
                'label' => 'Author Link Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'avatar_size' => [
                'label' => 'Avatar Size',
                'type' => 'text',
                'default' => '32px'
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
     * Get separator character from type
     */
    private function getSeparator(string $type): string
    {
        return match($type) {
            'dot' => '•',
            'dash' => '—',
            'slash' => '/',
            'pipe' => '|',
            default => '•'
        };
    }

    /**
     * Get formatted date based on format type
     */
    private function getFormattedDate(string $format): string
    {
        return match($format) {
            'full' => 'January 5, 2026',
            'short' => 'Jan 5, 2026',
            'numeric' => '01/05/2026',
            default => 'January 5, 2026'
        };
    }

    public function render(array $settings): string
    {
        // Content fields
        $titleTag = $settings['title_tag'] ?? 'h1';
        $showMeta = ($settings['show_meta'] ?? 'yes') === 'yes';
        $showDate = ($settings['show_date'] ?? 'yes') === 'yes';
        $dateFormat = $settings['date_format'] ?? 'full';
        $showAuthor = ($settings['show_author'] ?? 'yes') === 'yes';
        $showAvatar = ($settings['show_author_avatar'] ?? 'no') === 'yes';
        $showCategory = ($settings['show_category'] ?? 'yes') === 'yes';
        $showComments = ($settings['show_comments_count'] ?? 'no') === 'yes';
        $showReadingTime = ($settings['show_reading_time'] ?? 'no') === 'yes';
        $separatorType = $settings['meta_separator'] ?? 'dot';
        $metaPosition = $settings['meta_position'] ?? 'below';

        // Design fields
        $textAlign = $settings['text_align'] ?? 'left';
        $titleColor = $settings['title_color'] ?? '#111827';
        $titleSize = $settings['title_font_size'] ?? '36px';
        $titleWeight = $settings['title_font_weight'] ?? '700';
        $titleLineHeight = $settings['title_line_height'] ?? '1.2';
        $titleMargin = $settings['title_margin_bottom'] ?? '16px';
        $metaColor = $settings['meta_color'] ?? '#6b7280';
        $metaSize = $settings['meta_font_size'] ?? '14px';
        $metaGap = $settings['meta_gap'] ?? '16px';
        $categoryBg = $settings['category_bg_color'] ?? '#2563eb';
        $categoryTextColor = $settings['category_text_color'] ?? '#ffffff';
        $categoryRadius = $settings['category_border_radius'] ?? '4px';
        $authorColor = $settings['author_link_color'] ?? '#2563eb';
        $avatarSize = $settings['avatar_size'] ?? '32px';

        // Sample data for preview
        $sampleTitle = 'The Complete Guide to Modern Web Development';
        $sampleDate = $this->getFormattedDate($dateFormat);
        $sampleAuthor = 'John Doe';
        $sampleCategory = 'Technology';
        $sampleComments = '12 Comments';
        $sampleReadTime = '5 min read';

        $separator = $this->getSeparator($separatorType);
        $justify = match($textAlign) {
            'center' => 'center',
            'right' => 'flex-end',
            default => 'flex-start'
        };

        // Build HTML
        $html = '<div class="tb4-post-title-preview" style="text-align:' . esc_attr($textAlign) . ';">';

        $wrapperStyle = 'display:flex;flex-direction:column;';
        if ($metaPosition === 'above') {
            $wrapperStyle .= 'flex-direction:column-reverse;';
        }
        $html .= '<div class="tb4-post-title-wrapper" style="' . $wrapperStyle . '">';

        // Title
        $titleStyle = 'font-size:' . esc_attr($titleSize) . ';';
        $titleStyle .= 'font-weight:' . esc_attr($titleWeight) . ';';
        $titleStyle .= 'color:' . esc_attr($titleColor) . ';';
        $titleStyle .= 'margin:0 0 ' . esc_attr($titleMargin) . ' 0;';
        $titleStyle .= 'line-height:' . esc_attr($titleLineHeight) . ';';

        $html .= '<' . esc_attr($titleTag) . ' class="tb4-post-title-heading" style="' . $titleStyle . '">';
        $html .= esc_html($sampleTitle);
        $html .= '</' . esc_attr($titleTag) . '>';

        // Meta info
        if ($showMeta && ($showDate || $showAuthor || $showCategory || $showComments || $showReadingTime)) {
            $metaStyle = 'display:flex;flex-wrap:wrap;align-items:center;';
            $metaStyle .= 'gap:' . esc_attr($metaGap) . ';';
            $metaStyle .= 'font-size:' . esc_attr($metaSize) . ';';
            $metaStyle .= 'color:' . esc_attr($metaColor) . ';';
            $metaStyle .= 'justify-content:' . $justify . ';';
            if ($metaPosition === 'above') {
                $metaStyle .= 'margin-bottom:12px;';
            }

            $html .= '<div class="tb4-post-title-meta" style="' . $metaStyle . '">';

            $metaItems = [];

            // Category
            if ($showCategory) {
                $catStyle = 'display:inline-block;padding:4px 10px;';
                $catStyle .= 'background:' . esc_attr($categoryBg) . ';';
                $catStyle .= 'color:' . esc_attr($categoryTextColor) . ';';
                $catStyle .= 'font-size:12px;font-weight:600;text-transform:uppercase;';
                $catStyle .= 'border-radius:' . esc_attr($categoryRadius) . ';';
                $metaItems[] = '<span class="tb4-post-title-category" style="' . $catStyle . '">' . esc_html($sampleCategory) . '</span>';
            }

            // Author
            if ($showAuthor) {
                $authorHtml = '<span class="tb4-post-title-author" style="display:flex;align-items:center;gap:8px;">';
                if ($showAvatar) {
                    $avatarStyle = 'width:' . esc_attr($avatarSize) . ';height:' . esc_attr($avatarSize) . ';';
                    $avatarStyle .= 'border-radius:50%;background:#e5e7eb;display:flex;';
                    $avatarStyle .= 'align-items:center;justify-content:center;color:#9ca3af;font-size:12px;';
                    $authorHtml .= '<span class="tb4-post-title-avatar" style="' . $avatarStyle . '">JD</span>';
                }
                $authorHtml .= '<span class="tb4-post-title-author-name" style="color:' . esc_attr($authorColor) . ';font-weight:500;">' . esc_html($sampleAuthor) . '</span>';
                $authorHtml .= '</span>';
                $metaItems[] = $authorHtml;
            }

            // Date
            if ($showDate) {
                $metaItems[] = '<span class="tb4-post-title-meta-item" style="display:flex;align-items:center;gap:6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ' . esc_html($sampleDate) . '</span>';
            }

            // Comments
            if ($showComments) {
                $metaItems[] = '<span class="tb4-post-title-meta-item" style="display:flex;align-items:center;gap:6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> ' . esc_html($sampleComments) . '</span>';
            }

            // Reading time
            if ($showReadingTime) {
                $metaItems[] = '<span class="tb4-post-title-meta-item" style="display:flex;align-items:center;gap:6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ' . esc_html($sampleReadTime) . '</span>';
            }

            // Join with separator
            $separatorHtml = '<span class="tb4-post-title-separator" style="color:#d1d5db;"> ' . esc_html($separator) . ' </span>';
            $html .= implode($separatorHtml, $metaItems);

            $html .= '</div>';
        }

        $html .= '</div>'; // Close wrapper
        $html .= '</div>'; // Close preview

        return $html;
    }
}
