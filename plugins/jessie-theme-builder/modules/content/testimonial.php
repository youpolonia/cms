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
            'url' => [
                'label' => 'Author/Company URL',
                'type' => 'text'
            ],
            'url_new_window' => [
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
        $author = $this->esc($attrs['author'] ?? 'John Doe');
        $jobTitle = $this->esc($attrs['job_title'] ?? '');
        $company = $this->esc($attrs['company'] ?? '');
        $url = $attrs['url'] ?? '';
        $newWindow = !empty($attrs['url_new_window']) ? ' target="_blank" rel="noopener"' : '';
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

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Text alignment
        if (!empty($attrs['text_orientation'])) {
            $css .= $selector . ' .jtb-testimonial-container { text-align: ' . $attrs['text_orientation'] . '; }' . "\n";
        }

        // Portrait styling
        $portraitWidth = $attrs['portrait_width'] ?? 90;
        $portraitHeight = $attrs['portrait_height'] ?? 90;
        $portraitRadius = $attrs['portrait_border_radius'] ?? 50;

        $css .= $selector . ' .jtb-testimonial-portrait img { ';
        $css .= 'width: ' . $portraitWidth . 'px; ';
        $css .= 'height: ' . $portraitHeight . 'px; ';
        $css .= 'border-radius: ' . $portraitRadius . '%; ';
        $css .= 'object-fit: cover; ';
        $css .= '}' . "\n";

        // Quote icon
        if (!empty($attrs['quote_icon_color'])) {
            $css .= $selector . ' .jtb-testimonial-quote-icon { color: ' . $attrs['quote_icon_color'] . '; }' . "\n";
        }

        $quoteSize = $attrs['quote_icon_size'] ?? 32;
        $css .= $selector . ' .jtb-testimonial-quote-icon { font-size: ' . $quoteSize . 'px; font-family: Georgia, serif; line-height: 1; }' . "\n";

        // Author name
        if (!empty($attrs['author_name_color'])) {
            $css .= $selector . ' .jtb-testimonial-author-name { color: ' . $attrs['author_name_color'] . '; }' . "\n";
        }

        $css .= $selector . ' .jtb-testimonial-author-name { font-weight: bold; display: block; }' . "\n";

        // Position
        if (!empty($attrs['position_color'])) {
            $css .= $selector . ' .jtb-testimonial-position { color: ' . $attrs['position_color'] . '; }' . "\n";
        }

        // Company
        if (!empty($attrs['company_color'])) {
            $css .= $selector . ' .jtb-testimonial-company { color: ' . $attrs['company_color'] . '; }' . "\n";
        }

        // Body
        if (!empty($attrs['body_color'])) {
            $css .= $selector . ' .jtb-testimonial-content { color: ' . $attrs['body_color'] . '; }' . "\n";
        }

        $css .= $selector . ' .jtb-testimonial-content { margin: 15px 0; font-style: italic; }' . "\n";
        $css .= $selector . ' .jtb-testimonial-meta { font-size: 0.9em; opacity: 0.8; }' . "\n";

        // Responsive
        if (!empty($attrs['text_orientation__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-testimonial-container { text-align: ' . $attrs['text_orientation__tablet'] . '; } }' . "\n";
        }
        if (!empty($attrs['text_orientation__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-testimonial-container { text-align: ' . $attrs['text_orientation__phone'] . '; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('testimonial', JTB_Module_Testimonial::class);
