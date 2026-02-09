<?php
/**
 * Testimonial Module
 * Customer testimonial with avatar, quote and author info
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Testimonial extends JTB_Element
{
    public string $icon = 'quote';
    public string $category = 'content';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = true;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'testimonial';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        // Text alignment
        'text_orientation' => [
            'property' => 'text-align',
            'selector' => '.jtb-testimonial-container',
            'responsive' => true
        ],
        // Portrait
        'portrait_width' => [
            'property' => 'width',
            'selector' => '.jtb-testimonial-portrait img',
            'unit' => 'px'
        ],
        'portrait_height' => [
            'property' => 'height',
            'selector' => '.jtb-testimonial-portrait img',
            'unit' => 'px'
        ],
        'portrait_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-testimonial-portrait img',
            'unit' => '%'
        ],
        // Quote icon
        'quote_icon_color' => [
            'property' => 'color',
            'selector' => '.jtb-testimonial-quote-icon'
        ],
        'quote_icon_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-testimonial-quote-icon',
            'unit' => 'px'
        ],
        // Typography colors
        'author_name_color' => [
            'property' => 'color',
            'selector' => '.jtb-testimonial-author-name'
        ],
        'position_color' => [
            'property' => 'color',
            'selector' => '.jtb-testimonial-position'
        ],
        'company_color' => [
            'property' => 'color',
            'selector' => '.jtb-testimonial-company'
        ],
        'body_color' => [
            'property' => 'color',
            'selector' => '.jtb-testimonial-content'
        ]
    ];

    public function getSlug(): string
    {
        return 'testimonial';
    }

    public function getName(): string
    {
        return 'Testimonial';
    }

    public function getFields(): array
    {
        return [
            'author' => [
                'label' => 'Author Name',
                'type' => 'text',
                'default' => 'John Doe'
            ],
            'job_title' => [
                'label' => 'Job Title',
                'type' => 'text',
                'default' => 'CEO'
            ],
            'company' => [
                'label' => 'Company',
                'type' => 'text',
                'default' => 'Company Name'
            ],
            'link_url' => [
                'label' => 'Author/Company URL',
                'type' => 'text'
            ],
            'link_target' => [
                'label' => 'Open in New Tab',
                'type' => 'toggle',
                'default' => false
            ],
            'portrait_url' => [
                'label' => 'Portrait Image',
                'type' => 'upload'
            ],
            'quote_icon' => [
                'label' => 'Quote Icon',
                'type' => 'select',
                'options' => [
                    'on' => 'Show',
                    'off' => 'Hide'
                ],
                'default' => 'on'
            ],
            'content' => [
                'label' => 'Testimonial Text',
                'type' => 'richtext',
                'default' => '<p>Your testimonial text goes here.</p>'
            ],
            'text_orientation' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'left',
                'responsive' => true
            ],
            'portrait_width' => [
                'label' => 'Portrait Width',
                'type' => 'range',
                'min' => 20,
                'max' => 200,
                'unit' => 'px',
                'default' => 90
            ],
            'portrait_height' => [
                'label' => 'Portrait Height',
                'type' => 'range',
                'min' => 20,
                'max' => 200,
                'unit' => 'px',
                'default' => 90
            ],
            'portrait_border_radius' => [
                'label' => 'Portrait Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'unit' => '%',
                'default' => 50
            ],
            'quote_icon_color' => [
                'label' => 'Quote Icon Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ],
            'quote_icon_size' => [
                'label' => 'Quote Icon Size',
                'type' => 'range',
                'min' => 10,
                'max' => 100,
                'unit' => 'px',
                'default' => 32
            ],
            'author_name_color' => [
                'label' => 'Author Name Color',
                'type' => 'color'
            ],
            'position_color' => [
                'label' => 'Position Color',
                'type' => 'color'
            ],
            'company_color' => [
                'label' => 'Company Color',
                'type' => 'color'
            ],
            'body_color' => [
                'label' => 'Body Text Color',
                'type' => 'color'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $author = $this->esc($attrs['author'] ?? 'John Doe');
        $jobTitle = $this->esc($attrs['job_title'] ?? '');
        $company = $this->esc($attrs['company'] ?? '');
        $url = $attrs['link_url'] ?? '';
        $newWindow = !empty($attrs['link_target']) ? ' target="_blank" rel="noopener"' : '';
        $portrait = $attrs['portrait_url'] ?? '';
        $showQuote = ($attrs['quote_icon'] ?? 'on') === 'on';
        $bodyContent = $attrs['content'] ?? '<p>Your testimonial text goes here.</p>';

        $innerHtml = '<div class="jtb-testimonial-container">';

        // Portrait
        if (!empty($portrait)) {
            $innerHtml .= '<div class="jtb-testimonial-portrait">';
            $innerHtml .= '<img src="' . $this->esc($portrait) . '" alt="' . $author . '" />';
            $innerHtml .= '</div>';
        }

        // Quote icon
        if ($showQuote) {
            $innerHtml .= '<div class="jtb-testimonial-quote-icon">"</div>';
        }

        // Content
        $innerHtml .= '<div class="jtb-testimonial-content">' . $bodyContent . '</div>';

        // Author info
        $innerHtml .= '<div class="jtb-testimonial-author">';

        // Author name with optional link
        if (!empty($url)) {
            $innerHtml .= '<a class="jtb-testimonial-author-name" href="' . $this->esc($url) . '"' . $newWindow . '>' . $author . '</a>';
        } else {
            $innerHtml .= '<span class="jtb-testimonial-author-name">' . $author . '</span>';
        }

        // Job title and company
        $meta = [];
        if (!empty($jobTitle)) {
            $meta[] = '<span class="jtb-testimonial-position">' . $jobTitle . '</span>';
        }
        if (!empty($company)) {
            if (!empty($url)) {
                $meta[] = '<a class="jtb-testimonial-company" href="' . $this->esc($url) . '"' . $newWindow . '>' . $company . '</a>';
            } else {
                $meta[] = '<span class="jtb-testimonial-company">' . $company . '</span>';
            }
        }

        if (!empty($meta)) {
            $innerHtml .= '<div class="jtb-testimonial-meta">' . implode(', ', $meta) . '</div>';
        }

        $innerHtml .= '</div>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Testimonial module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Portrait object-fit (always needed for proper image display)
        $css .= $selector . ' .jtb-testimonial-portrait img { object-fit: cover; }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('testimonial', JTB_Module_Testimonial::class);
