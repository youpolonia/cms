<?php
/**
 * Archive Title Module
 * Displays archive/category/tag/search page title
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Archive_Title extends JTB_Element
{
    public string $slug = 'archive_title';
    public string $name = 'Archive Title';
    public string $icon = 'archive';
    public string $category = 'dynamic';

    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_typography = true;

    protected string $module_prefix = 'archive_title';

    protected array $style_config = [
        'title_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-archive-heading',
            'unit' => 'px',
            'responsive' => true
        ],
        'prefix_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-archive-prefix',
            'unit' => 'px',
            'responsive' => true
        ],
        'title_color' => [
            'property' => 'color',
            'selector' => '.jtb-archive-heading'
        ],
        'prefix_color' => [
            'property' => 'color',
            'selector' => '.jtb-archive-prefix'
        ],
        'description_color' => [
            'property' => 'color',
            'selector' => '.jtb-archive-description'
        ],
        'count_color' => [
            'property' => 'color',
            'selector' => '.jtb-archive-count'
        ],
        'text_alignment' => [
            'property' => 'text-align',
            'responsive' => true
        ],
        'description_max_width' => [
            'property' => 'max-width',
            'selector' => '.jtb-archive-description',
            'unit' => 'px'
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
            'show_prefix' => [
                'label' => 'Show Prefix Label',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Show "Category:", "Tag:", etc. before title'
            ],
            'prefix_category' => [
                'label' => 'Category Prefix',
                'type' => 'text',
                'default' => 'Category:'
            ],
            'prefix_tag' => [
                'label' => 'Tag Prefix',
                'type' => 'text',
                'default' => 'Tag:'
            ],
            'prefix_author' => [
                'label' => 'Author Prefix',
                'type' => 'text',
                'default' => 'Posts by:'
            ],
            'prefix_search' => [
                'label' => 'Search Prefix',
                'type' => 'text',
                'default' => 'Search Results for:'
            ],
            'prefix_date' => [
                'label' => 'Date Archive Prefix',
                'type' => 'text',
                'default' => 'Archives:'
            ],
            'show_description' => [
                'label' => 'Show Description',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Display category/tag description if available'
            ],
            'show_count' => [
                'label' => 'Show Post Count',
                'type' => 'toggle',
                'default' => false
            ],
            'title_level' => [
                'label' => 'Title Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3'
                ],
                'default' => 'h1'
            ],
            'title_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 24,
                'max' => 72,
                'step' => 2,
                'default' => 42,
                'unit' => 'px',
                'responsive' => true
            ],
            'prefix_size' => [
                'label' => 'Prefix Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 24,
                'step' => 1,
                'default' => 16,
                'unit' => 'px',
                'responsive' => true
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#1f2937'
            ],
            'prefix_color' => [
                'label' => 'Prefix Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'description_color' => [
                'label' => 'Description Color',
                'type' => 'color',
                'default' => '#4b5563'
            ],
            'count_color' => [
                'label' => 'Count Color',
                'type' => 'color',
                'default' => '#9ca3af'
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
            ],
            'description_max_width' => [
                'label' => 'Description Max Width',
                'type' => 'range',
                'min' => 300,
                'max' => 900,
                'step' => 50,
                'default' => 600,
                'unit' => 'px'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $id = $attrs['id'] ?? 'archive_title_' . uniqid();
        $showPrefix = $attrs['show_prefix'] ?? true;
        $showDescription = $attrs['show_description'] ?? true;
        $showCount = $attrs['show_count'] ?? false;
        $titleLevel = $attrs['title_level'] ?? 'h1';

        // Get prefix settings
        $prefixCategory = $attrs['prefix_category'] ?? 'Category:';
        $prefixTag = $attrs['prefix_tag'] ?? 'Tag:';
        $prefixAuthor = $attrs['prefix_author'] ?? 'Posts by:';
        $prefixSearch = $attrs['prefix_search'] ?? 'Search Results for:';
        $prefixDate = $attrs['prefix_date'] ?? 'Archives:';

        // Validate title level
        $allowedLevels = ['h1', 'h2', 'h3'];
        if (!in_array($titleLevel, $allowedLevels)) {
            $titleLevel = 'h1';
        }

        // Check preview mode
        $isPreview = JTB_Dynamic_Context::isPreviewMode();

        // Get dynamic data
        $prefix = '';
        $title = '';
        $description = '';
        $postCount = 0;

        if ($isPreview) {
            // Show placeholder in preview/builder
            $prefix = 'Category:';
            $title = 'Sample Archive';
            $description = 'This is where the archive description will appear. Categories and tags can have descriptions that provide context about the content they contain.';
            $postCount = 12;
        } else {
            // Get real archive data
            $archive = JTB_Dynamic_Context::getArchive();
            $archiveType = $archive['type'] ?? '';

            switch ($archiveType) {
                case 'category':
                    $prefix = $prefixCategory;
                    $title = $archive['title'] ?? 'Category';
                    $description = $archive['description'] ?? '';
                    $postCount = $archive['count'] ?? 0;
                    break;
                case 'tag':
                    $prefix = $prefixTag;
                    $title = $archive['title'] ?? 'Tag';
                    $description = $archive['description'] ?? '';
                    $postCount = $archive['count'] ?? 0;
                    break;
                case 'author':
                    $prefix = $prefixAuthor;
                    $author = JTB_Dynamic_Context::getAuthor();
                    $title = $author['name'] ?? 'Author';
                    $description = $author['bio'] ?? '';
                    break;
                case 'search':
                    $prefix = $prefixSearch;
                    $title = $_GET['q'] ?? $_GET['s'] ?? 'Search';
                    break;
                case 'date':
                    $prefix = $prefixDate;
                    $title = $archive['title'] ?? 'Archives';
                    break;
                case 'blog':
                    $prefix = '';
                    $title = 'Blog';
                    break;
                default:
                    $title = JTB_Dynamic_Context::getArchiveTitle();
                    break;
            }
        }

        $classes = ['jtb-archive-title'];

        $html = '<div id="' . $this->esc($id) . '" class="' . implode(' ', $classes) . '">';

        if ($showPrefix && !empty($prefix)) {
            $html .= '<span class="jtb-archive-prefix">' . $this->esc($prefix) . '</span>';
        }

        $html .= '<' . $titleLevel . ' class="jtb-archive-heading">';
        $html .= $this->esc($title);
        if ($showCount && $postCount > 0) {
            $html .= ' <span class="jtb-archive-count">(' . intval($postCount) . ' posts)</span>';
        }
        $html .= '</' . $titleLevel . '>';

        if ($showDescription && !empty($description)) {
            $html .= '<p class="jtb-archive-description">';
            $html .= $this->esc($description);
            $html .= '</p>';
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = parent::generateCss($attrs, $selector);
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $titleSize = $attrs['title_size'] ?? 42;
        $prefixSize = $attrs['prefix_size'] ?? 16;
        $titleColor = $attrs['title_color'] ?? '#1f2937';
        $prefixColor = $attrs['prefix_color'] ?? '#6b7280';
        $descColor = $attrs['description_color'] ?? '#4b5563';
        $countColor = $attrs['count_color'] ?? '#9ca3af';
        $alignment = $attrs['text_alignment'] ?? 'left';
        $descMaxWidth = $attrs['description_max_width'] ?? 600;

        // Container
        $css .= $selector . ' { text-align: ' . $alignment . '; }' . "\n";

        // Prefix
        $css .= $selector . ' .jtb-archive-prefix { ';
        $css .= 'display: block; ';
        $css .= 'font-size: ' . intval($prefixSize) . 'px; ';
        $css .= 'color: ' . $prefixColor . '; ';
        $css .= 'margin-bottom: 8px; ';
        $css .= 'text-transform: uppercase; ';
        $css .= 'letter-spacing: 1px; ';
        $css .= 'font-weight: 500; ';
        $css .= '}' . "\n";

        // Heading
        $css .= $selector . ' .jtb-archive-heading { ';
        $css .= 'margin: 0; ';
        $css .= 'font-size: ' . intval($titleSize) . 'px; ';
        $css .= 'color: ' . $titleColor . '; ';
        $css .= 'font-weight: 700; ';
        $css .= 'line-height: 1.2; ';
        $css .= '}' . "\n";

        // Count
        $css .= $selector . ' .jtb-archive-count { ';
        $css .= 'font-weight: 400; ';
        $css .= 'font-size: 0.6em; ';
        $css .= 'color: ' . $countColor . '; ';
        $css .= '}' . "\n";

        // Description
        $css .= $selector . ' .jtb-archive-description { ';
        $css .= 'margin: 16px 0 0; ';
        $css .= 'font-size: 16px; ';
        $css .= 'color: ' . $descColor . '; ';
        $css .= 'line-height: 1.6; ';
        $css .= 'max-width: ' . intval($descMaxWidth) . 'px; ';
        if ($alignment === 'center') {
            $css .= 'margin-left: auto; margin-right: auto; ';
        } elseif ($alignment === 'right') {
            $css .= 'margin-left: auto; ';
        }
        $css .= '}' . "\n";

        // Responsive - Title
        if (!empty($attrs['title_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-archive-heading { font-size: ' . intval($attrs['title_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        } else {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-archive-heading { font-size: ' . intval($titleSize * 0.8) . 'px; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['title_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-archive-heading { font-size: ' . intval($attrs['title_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        } else {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-archive-heading { font-size: ' . intval($titleSize * 0.65) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive - Prefix
        if (!empty($attrs['prefix_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' .jtb-archive-prefix { font-size: ' . intval($attrs['prefix_size__tablet']) . 'px; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['prefix_size__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' .jtb-archive-prefix { font-size: ' . intval($attrs['prefix_size__phone']) . 'px; }';
            $css .= ' }' . "\n";
        }

        // Responsive - Alignment
        if (!empty($attrs['text_alignment__tablet'])) {
            $css .= '@media (max-width: 980px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['text_alignment__tablet'] . '; }';
            $marginTablet = $attrs['text_alignment__tablet'] === 'center' ? 'auto' : ($attrs['text_alignment__tablet'] === 'right' ? 'auto 0 0 auto' : '16px 0 0');
            $css .= $selector . ' .jtb-archive-description { margin: ' . $marginTablet . '; }';
            $css .= ' }' . "\n";
        }

        if (!empty($attrs['text_alignment__phone'])) {
            $css .= '@media (max-width: 767px) { ';
            $css .= $selector . ' { text-align: ' . $attrs['text_alignment__phone'] . '; }';
            $marginPhone = $attrs['text_alignment__phone'] === 'center' ? 'auto' : ($attrs['text_alignment__phone'] === 'right' ? 'auto 0 0 auto' : '16px 0 0');
            $css .= $selector . ' .jtb-archive-description { margin: ' . $marginPhone . '; }';
            $css .= ' }' . "\n";
        }

        return $css;
    }
}

JTB_Registry::register('archive_title', JTB_Module_Archive_Title::class);
