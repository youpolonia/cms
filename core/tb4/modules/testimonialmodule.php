<?php
namespace Core\TB4\Modules;

require_once __DIR__ . "/module.php";

/**
 * TB 4.0 Testimonial Module
 * Social proof testimonials with multiple layout styles, ratings, and author info
 */
class TestimonialModule extends Module {

    /**
     * Typography fields configuration for quote and author elements
     */
    protected array $typography_fields = [
        'quote' => [
            'label' => 'Quote Typography',
            'selector' => '.tb4-testimonial__text',
            'defaults' => [
                'font_size' => ['desktop' => '18px', 'tablet' => '16px', 'mobile' => '15px'],
                'font_style' => 'normal',
                'line_height' => ['desktop' => '1.6'],
                'color' => '#374151'
            ]
        ],
        'author_name' => [
            'label' => 'Author Name Typography',
            'selector' => '.tb4-testimonial__name',
            'defaults' => [
                'font_size' => ['desktop' => '16px', 'tablet' => '15px', 'mobile' => '14px'],
                'font_weight' => '600',
                'color' => '#111827'
            ]
        ],
        'author_title' => [
            'label' => 'Author Title Typography',
            'selector' => '.tb4-testimonial__title',
            'defaults' => [
                'font_size' => ['desktop' => '14px', 'tablet' => '13px', 'mobile' => '12px'],
                'color' => '#6b7280'
            ]
        ]
    ];

    /**
     * Custom CSS fields for per-element CSS targeting
     */
    protected array $custom_css_fields = [
        'testimonial_container' => [
            'label' => 'Testimonial Container',
            'selector' => '.tb4-testimonial',
            'description' => 'Main testimonial wrapper element'
        ],
        'testimonial_quote' => [
            'label' => 'Quote Block',
            'selector' => '.tb4-testimonial__quote',
            'description' => 'Quote container element'
        ],
        'testimonial_text' => [
            'label' => 'Quote Text',
            'selector' => '.tb4-testimonial__text',
            'description' => 'The actual quote text content'
        ],
        'testimonial_marks' => [
            'label' => 'Quote Marks',
            'selector' => '.tb4-testimonial__marks',
            'description' => 'Decorative quotation marks'
        ],
        'testimonial_author' => [
            'label' => 'Author Section',
            'selector' => '.tb4-testimonial__author',
            'description' => 'Author information container'
        ],
        'testimonial_avatar' => [
            'label' => 'Author Avatar',
            'selector' => '.tb4-testimonial__avatar',
            'description' => 'Author image or initials'
        ],
        'testimonial_name' => [
            'label' => 'Author Name',
            'selector' => '.tb4-testimonial__name',
            'description' => 'Author name text'
        ],
        'testimonial_title' => [
            'label' => 'Author Title',
            'selector' => '.tb4-testimonial__title',
            'description' => 'Author job title/position'
        ],
        'testimonial_rating' => [
            'label' => 'Star Rating',
            'selector' => '.tb4-testimonial__rating',
            'description' => 'Star rating container'
        ]
    ];

    public function __construct() {
        $this->name = "Testimonial";
        $this->slug = "testimonial";
        $this->icon = "Quote";
        $this->category = "content";

        $this->elements = [
            "main" => ".tb4-testimonial",
            "quote" => ".tb4-testimonial__quote",
            "quote_text" => ".tb4-testimonial__text",
            "quote_marks" => ".tb4-testimonial__marks",
            "author" => ".tb4-testimonial__author",
            "author_image" => ".tb4-testimonial__avatar",
            "author_info" => ".tb4-testimonial__info",
            "author_name" => ".tb4-testimonial__name",
            "author_title" => ".tb4-testimonial__title",
            "author_company" => ".tb4-testimonial__company",
            "rating" => ".tb4-testimonial__rating"
        ];
    }

    public function get_content_fields(): array {
        return [
            "quote" => [
                "label" => "Testimonial Quote",
                "type" => "textarea",
                "default" => "This product has completely transformed how we work. The team is more productive, and our customers are happier than ever."
            ],
            "author_name" => [
                "label" => "Author Name",
                "type" => "text",
                "default" => "Jane Smith"
            ],
            "author_title" => [
                "label" => "Job Title / Position",
                "type" => "text",
                "default" => "CEO"
            ],
            "author_company" => [
                "label" => "Company Name",
                "type" => "text",
                "default" => "Acme Corp"
            ],
            "author_image" => [
                "label" => "Author Photo",
                "type" => "image",
                "default" => ""
            ],
            "rating" => [
                "label" => "Star Rating (0-5)",
                "type" => "select",
                "options" => [
                    "0" => "No Rating",
                    "1" => "1 Star",
                    "2" => "2 Stars",
                    "3" => "3 Stars",
                    "4" => "4 Stars",
                    "5" => "5 Stars"
                ],
                "default" => "5"
            ],
            "layout" => [
                "label" => "Layout Style",
                "type" => "select",
                "options" => [
                    "card" => "Card (Boxed with Shadow)",
                    "minimal" => "Minimal (Simple Text)",
                    "large-quote" => "Large Quote (Centered)"
                ],
                "default" => "card"
            ],
            "background_color" => [
                "label" => "Background Color",
                "type" => "color",
                "default" => "#ffffff"
            ],
            "show_quote_marks" => [
                "label" => "Show Quote Marks",
                "type" => "toggle",
                "default" => true
            ],
            "quote_mark_color" => [
                "label" => "Quote Mark Color",
                "type" => "color",
                "default" => "#3b82f6"
            ],
            "quote_mark_size" => [
                "label" => "Quote Mark Size",
                "type" => "text",
                "default" => "48px"
            ],
            "author_image_size" => [
                "label" => "Author Image Size",
                "type" => "text",
                "default" => "64px"
            ],
            "author_image_style" => [
                "label" => "Author Image Style",
                "type" => "select",
                "options" => [
                    "circle" => "Circle",
                    "rounded" => "Rounded Square",
                    "square" => "Square"
                ],
                "default" => "circle"
            ],
            "star_color" => [
                "label" => "Star Color",
                "type" => "color",
                "default" => "#fbbf24"
            ],
            "star_size" => [
                "label" => "Star Size",
                "type" => "text",
                "default" => "20px"
            ],
            "padding" => [
                "label" => "Padding",
                "type" => "text",
                "default" => "32px"
            ],
            "border_radius" => [
                "label" => "Border Radius",
                "type" => "text",
                "default" => "12px"
            ],
            "box_shadow" => [
                "label" => "Enable Box Shadow",
                "type" => "toggle",
                "default" => true
            ],
            "border_color" => [
                "label" => "Border Color",
                "type" => "color",
                "default" => "#e5e7eb"
            ],
            "border_width" => [
                "label" => "Border Width",
                "type" => "text",
                "default" => "1px"
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        $quote = $settings["quote"] ?? "This product has completely transformed how we work.";
        $authorName = $settings["author_name"] ?? "Jane Smith";
        $authorTitle = $settings["author_title"] ?? "";
        $authorCompany = $settings["author_company"] ?? "";
        $authorImage = $settings["author_image"] ?? "";
        $rating = (int)($settings["rating"] ?? 5);
        $layout = $settings["layout"] ?? "card";
        $backgroundColor = $settings["background_color"] ?? "#ffffff";
        $showQuoteMarks = $settings["show_quote_marks"] ?? true;
        $quoteMarkColor = $settings["quote_mark_color"] ?? "#3b82f6";
        $quoteMarkSize = $settings["quote_mark_size"] ?? "48px";
        $authorImageSize = $settings["author_image_size"] ?? "64px";
        $authorImgStyle = $settings["author_image_style"] ?? "circle";
        $starColor = $settings["star_color"] ?? "#fbbf24";
        $starSize = $settings["star_size"] ?? "20px";
        $padding = $settings["padding"] ?? "32px";
        $borderRadius = $settings["border_radius"] ?? "12px";
        $boxShadow = $settings["box_shadow"] ?? true;
        $borderColor = $settings["border_color"] ?? "#e5e7eb";
        $borderWidth = $settings["border_width"] ?? "1px";

        $imageBorderRadius = $authorImgStyle === "circle" ? "50%" : ($authorImgStyle === "rounded" ? "8px" : "0");

        $wrapperStyles = ["position: relative", "background: " . esc_attr($backgroundColor), "padding: " . esc_attr($padding), "border-radius: " . esc_attr($borderRadius)];

        if ($layout === "card") {
            $wrapperStyles[] = "border: " . esc_attr($borderWidth) . " solid " . esc_attr($borderColor);
            if ($boxShadow) {
                $wrapperStyles[] = "box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08)";
            }
        } elseif ($layout === "minimal") {
            $wrapperStyles[] = "background: transparent";
        } elseif ($layout === "large-quote") {
            $wrapperStyles[] = "text-align: center";
            if ($boxShadow) {
                $wrapperStyles[] = "box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08)";
            }
        }

        $wrapperStyle = implode("; ", $wrapperStyles);
        $quoteMarkStyle = sprintf("color:%s;font-size:%s;line-height:1;font-family:Georgia,serif;opacity:0.8", esc_attr($quoteMarkColor), esc_attr($quoteMarkSize));

        $html = sprintf("<div class=\"tb4-testimonial tb4-testimonial--%s\" style=\"%s\">", esc_attr($layout), $wrapperStyle);

        if ($showQuoteMarks && $layout === "large-quote") {
            $html .= sprintf("<div class=\"tb4-testimonial__marks\" style=\"%s;margin-bottom:16px\">&ldquo;</div>", $quoteMarkStyle);
        }

        $html .= "<div class=\"tb4-testimonial__quote\">";

        if ($showQuoteMarks && $layout !== "large-quote") {
            $html .= sprintf("<span class=\"tb4-testimonial__marks\" style=\"%s;display:inline\">&ldquo;</span>", $quoteMarkStyle);
        }

        $html .= sprintf("<p class=\"tb4-testimonial__text\" style=\"margin:0;display:inline\">%s</p>", esc_html($quote));

        if ($showQuoteMarks && $layout !== "large-quote") {
            $html .= sprintf("<span class=\"tb4-testimonial__marks\" style=\"%s;display:inline\">&rdquo;</span>", $quoteMarkStyle);
        }

        $html .= "</div>";

        if ($showQuoteMarks && $layout === "large-quote") {
            $html .= sprintf("<div class=\"tb4-testimonial__marks\" style=\"%s;margin-top:16px\">&rdquo;</div>", $quoteMarkStyle);
        }

        if ($rating > 0) {
            $starsHtml = "<div class=\"tb4-testimonial__rating\" style=\"display:flex;gap:4px;margin-top:16px;" . ($layout === "large-quote" ? "justify-content:center" : "") . "\">";
            for ($i = 1; $i <= 5; $i++) {
                $starFill = $i <= $rating ? $starColor : "#d1d5db";
                $starsHtml .= sprintf("<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"%s\" height=\"%s\" viewBox=\"0 0 24 24\" fill=\"%s\" stroke=\"%s\" stroke-width=\"1\"><polygon points=\"12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\"/></svg>", esc_attr($starSize), esc_attr($starSize), esc_attr($starFill), esc_attr($starFill));
            }
            $starsHtml .= "</div>";
            $html .= $starsHtml;
        }

        $authorStyles = "display:flex;align-items:center;gap:16px;margin-top:24px";
        if ($layout === "large-quote") {
            $authorStyles .= ";justify-content:center";
        }

        $html .= sprintf("<div class=\"tb4-testimonial__author\" style=\"%s\">", $authorStyles);

        if ($authorImage) {
            $html .= sprintf("<img class=\"tb4-testimonial__avatar\" src=\"%s\" alt=\"%s\" style=\"width:%s;height:%s;border-radius:%s;object-fit:cover;flex-shrink:0\">", esc_attr($authorImage), esc_attr($authorName), esc_attr($authorImageSize), esc_attr($authorImageSize), $imageBorderRadius);
        } else {
            $initials = "";
            $nameParts = explode(" ", $authorName);
            foreach ($nameParts as $part) {
                if (!empty($part)) {
                    $initials .= strtoupper($part[0]);
                }
            }
            $initials = substr($initials, 0, 2);

            $html .= sprintf("<div class=\"tb4-testimonial__avatar\" style=\"width:%s;height:%s;border-radius:%s;background:%s;color:white;display:flex;align-items:center;justify-content:center;font-size:calc(%s / 2.5);font-weight:600;flex-shrink:0\">%s</div>", esc_attr($authorImageSize), esc_attr($authorImageSize), $imageBorderRadius, esc_attr($quoteMarkColor), esc_attr($authorImageSize), esc_html($initials));
        }

        $html .= "<div class=\"tb4-testimonial__info\">";
        $html .= sprintf("<p class=\"tb4-testimonial__name\" style=\"margin:0\">%s</p>", esc_html($authorName));

        if ($authorTitle || $authorCompany) {
            $titleCompany = [];
            if ($authorTitle) $titleCompany[] = $authorTitle;
            if ($authorCompany) $titleCompany[] = $authorCompany;
            $html .= sprintf("<p class=\"tb4-testimonial__title\" style=\"margin:0\">%s</p>", esc_html(implode(" at ", $titleCompany)));
        }

        $html .= "</div></div></div>";

        return $html;
    }
}