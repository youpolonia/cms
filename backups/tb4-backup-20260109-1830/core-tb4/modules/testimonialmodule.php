<?php
namespace Core\TB4\Modules;

require_once __DIR__ . "/module.php";

/**
 * TB 4.0 Testimonial Module
 * Social proof testimonials with multiple layout styles, ratings, and author info
 */
class TestimonialModule extends Module {

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
            // Content fields
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
            "link_url" => [
                "label" => "Link to Full Review (optional)",
                "type" => "text",
                "default" => ""
            ],
            "link_target" => [
                "label" => "Link Target",
                "type" => "select",
                "options" => [
                    "_self" => "Same Window",
                    "_blank" => "New Window"
                ],
                "default" => "_self"
            ],

            // Layout fields
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

            // Background & Colors
            "background_color" => [
                "label" => "Background Color",
                "type" => "color",
                "default" => "#ffffff"
            ],
            "text_color" => [
                "label" => "Text Color",
                "type" => "color",
                "default" => "#374151"
            ],

            // Quote styling
            "quote_font_size" => [
                "label" => "Quote Font Size",
                "type" => "text",
                "default" => "18px"
            ],
            "quote_font_style" => [
                "label" => "Quote Font Style",
                "type" => "select",
                "options" => [
                    "normal" => "Normal",
                    "italic" => "Italic"
                ],
                "default" => "normal"
            ],
            "quote_line_height" => [
                "label" => "Quote Line Height",
                "type" => "text",
                "default" => "1.6"
            ],

            // Quote marks
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

            // Author image styling
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

            // Author text styling
            "author_name_color" => [
                "label" => "Author Name Color",
                "type" => "color",
                "default" => "#111827"
            ],
            "author_name_size" => [
                "label" => "Author Name Size",
                "type" => "text",
                "default" => "16px"
            ],
            "author_title_color" => [
                "label" => "Title/Company Color",
                "type" => "color",
                "default" => "#6b7280"
            ],
            "author_title_size" => [
                "label" => "Title/Company Size",
                "type" => "text",
                "default" => "14px"
            ],

            // Rating styling
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

            // Spacing & borders
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
        // Content fields
        $quote = $settings["quote"] ?? "This product has completely transformed how we work.";
        $authorName = $settings["author_name"] ?? "Jane Smith";
        $authorTitle = $settings["author_title"] ?? "";
        $authorCompany = $settings["author_company"] ?? "";
        $authorImage = $settings["author_image"] ?? "";
        $rating = (int)($settings["rating"] ?? 5);
        $linkUrl = $settings["link_url"] ?? "";
        $linkTarget = $settings["link_target"] ?? "_self";

        // Layout
        $layout = $settings["layout"] ?? "card";

        // Colors
        $backgroundColor = $settings["background_color"] ?? "#ffffff";
        $textColor = $settings["text_color"] ?? "#374151";

        // Quote styling
        $quoteFontSize = $settings["quote_font_size"] ?? "18px";
        $quoteFontStyle = $settings["quote_font_style"] ?? "normal";
        $quoteLineHeight = $settings["quote_line_height"] ?? "1.6";

        // Quote marks
        $showQuoteMarks = $settings["show_quote_marks"] ?? true;
        $quoteMarkColor = $settings["quote_mark_color"] ?? "#3b82f6";
        $quoteMarkSize = $settings["quote_mark_size"] ?? "48px";

        // Author image
        $authorImageSize = $settings["author_image_size"] ?? "64px";
        $authorImgStyle = $settings["author_image_style"] ?? "circle";

        // Author text
        $authorNameColor = $settings["author_name_color"] ?? "#111827";
        $authorNameSize = $settings["author_name_size"] ?? "16px";
        $authorTitleColor = $settings["author_title_color"] ?? "#6b7280";
        $authorTitleSize = $settings["author_title_size"] ?? "14px";

        // Rating
        $starColor = $settings["star_color"] ?? "#fbbf24";
        $starSize = $settings["star_size"] ?? "20px";

        // Spacing & borders
        $padding = $settings["padding"] ?? "32px";
        $borderRadius = $settings["border_radius"] ?? "12px";
        $boxShadow = $settings["box_shadow"] ?? true;
        $borderColor = $settings["border_color"] ?? "#e5e7eb";
        $borderWidth = $settings["border_width"] ?? "1px";

        // Calculate image border radius based on style
        $imageBorderRadius = "0";
        if ($authorImgStyle === "circle") {
            $imageBorderRadius = "50%";
        } elseif ($authorImgStyle === "rounded") {
            $imageBorderRadius = "8px";
        }

        // Build main wrapper styles
        $wrapperStyles = [
            "position: relative",
            "background: " . esc_attr($backgroundColor),
            "padding: " . esc_attr($padding),
            "border-radius: " . esc_attr($borderRadius)
        ];

        // Layout-specific styles
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

        // Build quote text styles
        $quoteTextStyle = sprintf(
            "color:%s;font-size:%s;font-style:%s;line-height:%s;margin:0",
            esc_attr($textColor),
            esc_attr($quoteFontSize),
            esc_attr($quoteFontStyle),
            esc_attr($quoteLineHeight)
        );

        // Build quote marks styles
        $quoteMarkStyle = sprintf(
            "color:%s;font-size:%s;line-height:1;font-family:Georgia,serif;opacity:0.8",
            esc_attr($quoteMarkColor),
            esc_attr($quoteMarkSize)
        );

        // Build author image styles
        $authorImgCss = sprintf(
            "width:%s;height:%s;border-radius:%s;object-fit:cover;flex-shrink:0",
            esc_attr($authorImageSize),
            esc_attr($authorImageSize),
            $imageBorderRadius
        );

        // Build author name styles
        $authorNameStyle = sprintf(
            "color:%s;font-size:%s;font-weight:600;margin:0",
            esc_attr($authorNameColor),
            esc_attr($authorNameSize)
        );

        // Build author title/company styles
        $authorMetaStyle = sprintf(
            "color:%s;font-size:%s;margin:0",
            esc_attr($authorTitleColor),
            esc_attr($authorTitleSize)
        );

        // Start building HTML
        $html = sprintf(
            "<div class=\"tb4-testimonial tb4-testimonial--%s\" style=\"%s\">",
            esc_attr($layout),
            $wrapperStyle
        );

        // Quote marks (opening) for large-quote layout
        if ($showQuoteMarks && $layout === "large-quote") {
            $html .= sprintf(
                "<div class=\"tb4-testimonial__marks tb4-testimonial__marks--open\" style=\"%s;margin-bottom:16px\">&ldquo;</div>",
                $quoteMarkStyle
            );
        }

        // Quote section
        $html .= "<div class=\"tb4-testimonial__quote\">";

        // Quote marks (opening) for card/minimal layouts
        if ($showQuoteMarks && $layout !== "large-quote") {
            $html .= sprintf(
                "<span class=\"tb4-testimonial__marks tb4-testimonial__marks--open\" style=\"%s;display:inline\">&ldquo;</span>",
                $quoteMarkStyle
            );
        }

        // Quote text
        $html .= sprintf(
            "<p class=\"tb4-testimonial__text\" style=\"%s;display:inline\">%s</p>",
            $quoteTextStyle,
            esc_html($quote)
        );

        // Quote marks (closing)
        if ($showQuoteMarks && $layout !== "large-quote") {
            $html .= sprintf(
                "<span class=\"tb4-testimonial__marks tb4-testimonial__marks--close\" style=\"%s;display:inline\">&rdquo;</span>",
                $quoteMarkStyle
            );
        }

        $html .= "</div>";

        // Quote marks (closing) for large-quote layout
        if ($showQuoteMarks && $layout === "large-quote") {
            $html .= sprintf(
                "<div class=\"tb4-testimonial__marks tb4-testimonial__marks--close\" style=\"%s;margin-top:16px\">&rdquo;</div>",
                $quoteMarkStyle
            );
        }

        // Rating stars
        if ($rating > 0) {
            $starsHtml = "<div class=\"tb4-testimonial__rating\" style=\"display:flex;gap:4px;margin-top:16px;" . ($layout === "large-quote" ? "justify-content:center" : "") . "\">";
            for ($i = 1; $i <= 5; $i++) {
                $starFill = $i <= $rating ? $starColor : "#d1d5db";
                $starsHtml .= sprintf(
                    "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"%s\" height=\"%s\" viewBox=\"0 0 24 24\" fill=\"%s\" stroke=\"%s\" stroke-width=\"1\"><polygon points=\"12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\"/></svg>",
                    esc_attr($starSize),
                    esc_attr($starSize),
                    esc_attr($starFill),
                    esc_attr($starFill)
                );
            }
            $starsHtml .= "</div>";
            $html .= $starsHtml;
        }

        // Author section
        $authorStyles = "display:flex;align-items:center;gap:16px;margin-top:24px";
        if ($layout === "large-quote") {
            $authorStyles .= ";justify-content:center";
        }

        $html .= sprintf("<div class=\"tb4-testimonial__author\" style=\"%s\">", $authorStyles);

        // Author image
        if ($authorImage) {
            $html .= sprintf(
                "<img class=\"tb4-testimonial__avatar\" src=\"%s\" alt=\"%s\" style=\"%s\">",
                esc_attr($authorImage),
                esc_attr($authorName),
                $authorImgCss
            );
        } else {
            // Placeholder avatar with initials
            $initials = "";
            $nameParts = explode(" ", $authorName);
            foreach ($nameParts as $part) {
                if (!empty($part)) {
                    $initials .= strtoupper($part[0]);
                }
            }
            $initials = substr($initials, 0, 2);

            $placeholderStyle = sprintf(
                "width:%s;height:%s;border-radius:%s;background:%s;color:white;display:flex;align-items:center;justify-content:center;font-size:calc(%s / 2.5);font-weight:600;flex-shrink:0",
                esc_attr($authorImageSize),
                esc_attr($authorImageSize),
                $imageBorderRadius,
                esc_attr($quoteMarkColor),
                esc_attr($authorImageSize)
            );

            $html .= sprintf(
                "<div class=\"tb4-testimonial__avatar tb4-testimonial__avatar--placeholder\" style=\"%s\">%s</div>",
                $placeholderStyle,
                esc_html($initials)
            );
        }

        // Author info
        $html .= "<div class=\"tb4-testimonial__info\">";

        // Author name
        $html .= sprintf(
            "<p class=\"tb4-testimonial__name\" style=\"%s\">%s</p>",
            $authorNameStyle,
            esc_html($authorName)
        );

        // Author title and company
        if ($authorTitle || $authorCompany) {
            $titleCompany = [];
            if ($authorTitle) {
                $titleCompany[] = $authorTitle;
            }
            if ($authorCompany) {
                $titleCompany[] = $authorCompany;
            }
            $html .= sprintf(
                "<p class=\"tb4-testimonial__title\" style=\"%s\">%s</p>",
                $authorMetaStyle,
                esc_html(implode(" at ", $titleCompany))
            );
        }

        $html .= "</div>"; // Close author info
        $html .= "</div>"; // Close author

        // Link wrapper if URL is provided
        if ($linkUrl) {
            $html .= sprintf(
                "<a href=\"%s\" target=\"%s\" class=\"tb4-testimonial__link\" style=\"position:absolute;top:0;left:0;right:0;bottom:0;z-index:1\"></a>",
                esc_attr($linkUrl),
                esc_attr($linkTarget)
            );
        }

        $html .= "</div>"; // Close main wrapper

        return $html;
    }
}
