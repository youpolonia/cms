<?php
/**
 * Post Meta Module
 * Displays post metadata (author, date, categories, tags)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Post_Meta extends JTB_Element
{
    public string $slug = 'post_meta';
    public string $name = 'Post Meta';
    public string $icon = 'info';
    public string $category = 'dynamic';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'post_meta';

    protected array $style_config = [
        'text_color' => [
            'property' => 'color'
        ],
        'link_color' => [
            'property' => 'color',
            'selector' => '.jtb-meta-link',
            'hover' => true
        ],
        'icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-meta-icon'
        ],
        'font_size' => [
            'property' => 'font-size',
            'unit' => 'px',
            'responsive' => true
        ]
    ];

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFields(): array
    {
        return [
            'show_author' => [
                'label' => 'Show Author',
                'type' => 'toggle',
                'default' => true
            ],
            'show_author_avatar' => [
                'label' => 'Show Author Avatar',
                'type' => 'toggle',
                'default' => false
            ],
            'show_date' => [
                'label' => 'Show Date',
                'type' => 'toggle',
                'default' => true
            ],
            'date_format' => [
                'label' => 'Date Format',
                'type' => 'select',
                'options' => [
                    'F j, Y' => 'January 1, 2026',
                    'Y-m-d' => '2026-01-01',
                    'd/m/Y' => '01/01/2026',
                    'm/d/Y' => '01/01/2026',
                    'M j, Y' => 'Jan 1, 2026',
                    'j F Y' => '1 January 2026'
                ],
                'default' => 'F j, Y'
            ],
            'show_categories' => [
                'label' => 'Show Categories',
                'type' => 'toggle',
                'default' => true
            ],
            'show_tags' => [
                'label' => 'Show Tags',
                'type' => 'toggle',
                'default' => false
            ],
            'show_comments_count' => [
                'label' => 'Show Comments Count',
                'type' => 'toggle',
                'default' => false
            ],
            'show_reading_time' => [
                'label' => 'Show Reading Time',
                'type' => 'toggle',
                'default' => false
            ],
            'separator' => [
                'label' => 'Separator',
                'type' => 'text',
                'default' => '|',
                'description' => 'Character between meta items'
            ],
            'text_color' => [
                'label' => 'Text Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#7c3aed',
                'hover' => true
            ],
            'icon_color' => [
                'label' => 'Icon Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'font_size' => [
                'label' => 'Font Size',
                'type' => 'range',
                'min' => 10,
                'max' => 20,
                'step' => 1,
                'default' => 14,
                'unit' => 'px',
                'responsive' => true
            ],
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'inline' => 'Inline',
                    'stacked' => 'Stacked'
                ],
                'default' => 'inline'
            ],
            'text_alignment' => [
                'label' => 'Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'left',
                'responsive' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'post_meta_' . uniqid();
        $showAuthor = $attrs['show_author'] ?? true;
        $showAvatar = $attrs['show_author_avatar'] ?? false;
        $showDate = $attrs['show_date'] ?? true;
        $dateFormat = $attrs['date_format'] ?? 'F j, Y';
        $showCategories = $attrs['show_categories'] ?? true;
        $showTags = $attrs['show_tags'] ?? false;
        $showComments = $attrs['show_comments_count'] ?? false;
        $showReadingTime = $attrs['show_reading_time'] ?? false;
        $separator = $attrs['separator'] ?? '|';
        $layout = $attrs['layout'] ?? 'inline';
        $alignment = $attrs['text_alignment'] ?? 'left';

        $classes = ['jtb-post-meta', 'jtb-meta-' . $this->esc($layout), 'jtb-align-' . $this->esc($alignment)];

        // SVG icons
        $userIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
        $calendarIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>';
        $folderIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>';
        $tagIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>';
        $commentIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
        $clockIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';

        $metaItems = [];

        if ($showAuthor) {
            $authorHtml = '<span class="jtb-meta-icon">' . $userIcon . '</span>';
            if ($showAvatar) {
                $avatarSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle fill="#e5e7eb" cx="12" cy="12" r="12"/><circle fill="#9ca3af" cx="12" cy="9" r="4"/><path fill="#9ca3af" d="M12 14c-4 0-7 2-7 5v1h14v-1c0-3-3-5-7-5z"/></svg>';
                $authorHtml .= '<img src="data:image/svg+xml,' . rawurlencode($avatarSvg) . '" alt="" class="jtb-meta-avatar" />';
            }
            $authorHtml .= '<span class="jtb-meta-label">By </span><a href="#" class="jtb-meta-link">Author Name</a>';
            $metaItems[] = '<span class="jtb-meta-item jtb-meta-author">' . $authorHtml . '</span>';
        }

        if ($showDate) {
            $metaItems[] = '<span class="jtb-meta-item jtb-meta-date"><span class="jtb-meta-icon">' . $calendarIcon . '</span>' . date($dateFormat) . '</span>';
        }

        if ($showCategories) {
            $metaItems[] = '<span class="jtb-meta-item jtb-meta-categories"><span class="jtb-meta-icon">' . $folderIcon . '</span>In <a href="#" class="jtb-meta-link">Category</a></span>';
        }

        if ($showTags) {
            $metaItems[] = '<span class="jtb-meta-item jtb-meta-tags"><span class="jtb-meta-icon">' . $tagIcon . '</span><a href="#" class="jtb-meta-link">Tag1</a>, <a href="#" class="jtb-meta-link">Tag2</a></span>';
        }

        if ($showComments) {
            $metaItems[] = '<span class="jtb-meta-item jtb-meta-comments"><span class="jtb-meta-icon">' . $commentIcon . '</span>5 Comments</span>';
        }

        if ($showReadingTime) {
            $metaItems[] = '<span class="jtb-meta-item jtb-meta-reading-time"><span class="jtb-meta-icon">' . $clockIcon . '</span>5 min read</span>';
        }

        $separatorHtml = $layout === 'inline' ? '<span class="jtb-meta-sep">' . $this->esc($separator) . '</span>' : '';

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';
        $html .= implode($separatorHtml, $metaItems);
        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $textColor = $attrs['text_color'] ?? '#6b7280';
        $linkColor = $attrs['link_color'] ?? '#7c3aed';
        $iconColor = $attrs['icon_color'] ?? '#9ca3af';
        $fontSize = $attrs['font_size'] ?? 14;
        $layout = $attrs['layout'] ?? 'inline';
        $alignment = $attrs['text_alignment'] ?? 'left';

        // Container
        $css .= $selector . ' { ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'font-size: ' . intval($fontSize) . 'px; ';
        $css .= 'text-align: ' . $alignment . '; ';
        if ($layout === 'inline') {
            $css .= 'display: flex; ';
            $css .= 'flex-wrap: wrap; ';
            $css .= 'align-items: center; ';
            $css .= 'gap: 8px; ';
            if ($alignment === 'center') {
                $css .= 'justify-content: center; ';
            } elseif ($alignment === 'right') {
                $css .= 'justify-content: flex-end; ';
            }
        }
        $css .= '}' . "\n";

        // Meta item
        $css .= $selector . ' .jtb-meta-item { ';
        $css .= 'display: inline-flex; ';
        $css .= 'align-items: center; ';
        $css .= 'gap: 4px; ';
        if ($layout === 'stacked') {
            $css .= 'display: block; ';
            $css .= 'margin-bottom: 8px; ';
        }
        $css .= '}' . "\n";

        // Icon
        $css .= $selector . ' .jtb-meta-icon { ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'display: inline-flex; ';
        $css .= 'margin-right: 4px; ';
        $css .= '}' . "\n";

        // Avatar
        $css .= $selector . ' .jtb-meta-avatar { ';
        $css .= 'width: 24px; ';
        $css .= 'height: 24px; ';
        $css .= 'border-radius: 50%; ';
        $css .= 'margin-right: 6px; ';
        $css .= 'vertical-align: middle; ';
        $css .= '}' . "\n";

        // Links
        $css .= $selector . ' .jtb-meta-link { ';
        $css .= 'color: ' . $linkColor . '; ';
        $css .= 'text-decoration: none; ';
        $css .= 'transition: color 0.3s ease; ';
        $css .= '}' . "\n";

        // Link hover
        if (!empty($attrs['link_color__hover'])) {
            $css .= $selector . ' .jtb-meta-link:hover { color: ' . $attrs['link_color__hover'] . '; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-meta-link:hover { text-decoration: underline; }' . "\n";
        }

        // Separator
        $css .= $selector . ' .jtb-meta-sep { ';
        $css .= 'color: ' . $iconColor . '; ';
        $css .= 'margin: 0 4px; ';
        $css .= '}' . "\n";

        // Responsive
        if (!empty($attrs['font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { font-size: ' . intval($attrs['font_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { font-size: ' . intval($attrs['font_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive alignment
        if (!empty($attrs['text_alignment__tablet'])) {
            $justifyTablet = $attrs['text_alignment__tablet'] === 'center' ? 'center' : ($attrs['text_alignment__tablet'] === 'right' ? 'flex-end' : 'flex-start');
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['text_alignment__tablet'] . '; justify-content: ' . $justifyTablet . '; }';
            $css .= ' }' . "\n";
        }
        if (!empty($attrs['text_alignment__phone'])) {
            $justifyPhone = $attrs['text_alignment__phone'] === 'center' ? 'center' : ($attrs['text_alignment__phone'] === 'right' ? 'flex-end' : 'flex-start');
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['text_alignment__phone'] . '; justify-content: ' . $justifyPhone . '; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('post_meta', JTB_Module_Post_Meta::class);
