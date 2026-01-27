<?php
namespace Core\TB4\Modules;

require_once __DIR__ . "/module.php";

/**
 * TB 4.0 CTA (Call to Action) Module
 * High-conversion banners with flexible layouts, gradient backgrounds, and prominent buttons
 */
class CtaModule extends Module {

    public function __construct() {
        $this->name = "CTA";
        $this->slug = "cta";
        $this->icon = "Megaphone";
        $this->category = "content";

        $this->elements = [
            "main" => ".tb4-cta",
            "container" => ".tb4-cta__container",
            "content" => ".tb4-cta__content",
            "title" => ".tb4-cta__title",
            "description" => ".tb4-cta__description",
            "buttons" => ".tb4-cta__buttons",
            "primary_button" => ".tb4-cta__btn--primary",
            "secondary_button" => ".tb4-cta__btn--secondary"
        ];
    }

    public function get_content_fields(): array {
        return [
            // Content fields
            "title" => [
                "label" => "Title",
                "type" => "text",
                "default" => "Ready to Get Started?"
            ],
            "description" => [
                "label" => "Description",
                "type" => "textarea",
                "default" => "Join thousands of satisfied customers and take your business to the next level today."
            ],
            "button_text" => [
                "label" => "Primary Button Text",
                "type" => "text",
                "default" => "Get Started"
            ],
            "button_url" => [
                "label" => "Primary Button URL",
                "type" => "text",
                "default" => "#"
            ],
            "button_target" => [
                "label" => "Primary Button Target",
                "type" => "select",
                "options" => [
                    "_self" => "Same Window",
                    "_blank" => "New Window"
                ],
                "default" => "_self"
            ],
            "secondary_button_text" => [
                "label" => "Secondary Button Text",
                "type" => "text",
                "default" => ""
            ],
            "secondary_button_url" => [
                "label" => "Secondary Button URL",
                "type" => "text",
                "default" => "#"
            ],
            "secondary_button_target" => [
                "label" => "Secondary Button Target",
                "type" => "select",
                "options" => [
                    "_self" => "Same Window",
                    "_blank" => "New Window"
                ],
                "default" => "_self"
            ],

            // Layout fields
            "layout" => [
                "label" => "Layout",
                "type" => "select",
                "options" => [
                    "horizontal" => "Horizontal (Text Left, Buttons Right)",
                    "stacked" => "Stacked (All Centered)"
                ],
                "default" => "horizontal"
            ],
            "text_align" => [
                "label" => "Text Alignment",
                "type" => "select",
                "options" => [
                    "left" => "Left",
                    "center" => "Center",
                    "right" => "Right"
                ],
                "default" => "left"
            ],

            // Background fields
            "background_color" => [
                "label" => "Background Color",
                "type" => "color",
                "default" => "#2563eb"
            ],
            "gradient_enabled" => [
                "label" => "Enable Gradient",
                "type" => "toggle",
                "default" => false
            ],
            "gradient_color_start" => [
                "label" => "Gradient Start Color",
                "type" => "color",
                "default" => "#2563eb"
            ],
            "gradient_color_end" => [
                "label" => "Gradient End Color",
                "type" => "color",
                "default" => "#7c3aed"
            ],
            "gradient_direction" => [
                "label" => "Gradient Direction",
                "type" => "select",
                "options" => [
                    "to right" => "Left to Right",
                    "to left" => "Right to Left",
                    "to bottom" => "Top to Bottom",
                    "to top" => "Bottom to Top",
                    "135deg" => "Diagonal (↘)",
                    "45deg" => "Diagonal (↗)"
                ],
                "default" => "to right"
            ],

            // Typography fields
            "title_color" => [
                "label" => "Title Color",
                "type" => "color",
                "default" => "#ffffff"
            ],
            "title_font_size" => [
                "label" => "Title Font Size",
                "type" => "text",
                "default" => "28px"
            ],
            "title_font_weight" => [
                "label" => "Title Font Weight",
                "type" => "select",
                "options" => [
                    "400" => "Normal",
                    "500" => "Medium",
                    "600" => "Semi-Bold",
                    "700" => "Bold",
                    "800" => "Extra Bold"
                ],
                "default" => "700"
            ],
            "description_color" => [
                "label" => "Description Color",
                "type" => "color",
                "default" => "rgba(255, 255, 255, 0.9)"
            ],
            "description_font_size" => [
                "label" => "Description Font Size",
                "type" => "text",
                "default" => "16px"
            ],

            // Spacing fields
            "padding_top" => [
                "label" => "Padding Top",
                "type" => "text",
                "default" => "40px"
            ],
            "padding_bottom" => [
                "label" => "Padding Bottom",
                "type" => "text",
                "default" => "40px"
            ],
            "padding_horizontal" => [
                "label" => "Horizontal Padding",
                "type" => "text",
                "default" => "32px"
            ],
            "border_radius" => [
                "label" => "Border Radius",
                "type" => "text",
                "default" => "12px"
            ],

            // Button styling
            "button_style" => [
                "label" => "Primary Button Style",
                "type" => "select",
                "options" => [
                    "filled" => "Filled",
                    "outline" => "Outline"
                ],
                "default" => "filled"
            ],
            "button_bg_color" => [
                "label" => "Primary Button Background",
                "type" => "color",
                "default" => "#ffffff"
            ],
            "button_text_color" => [
                "label" => "Primary Button Text Color",
                "type" => "color",
                "default" => "#2563eb"
            ],
            "button_border_radius" => [
                "label" => "Button Border Radius",
                "type" => "text",
                "default" => "6px"
            ],
            "secondary_button_style" => [
                "label" => "Secondary Button Style",
                "type" => "select",
                "options" => [
                    "filled" => "Filled",
                    "outline" => "Outline"
                ],
                "default" => "outline"
            ],
            "secondary_button_bg" => [
                "label" => "Secondary Button Background",
                "type" => "color",
                "default" => "transparent"
            ],
            "secondary_button_text_color" => [
                "label" => "Secondary Button Text Color",
                "type" => "color",
                "default" => "#ffffff"
            ],
            "secondary_button_border_color" => [
                "label" => "Secondary Button Border Color",
                "type" => "color",
                "default" => "#ffffff"
            ]
        ];
    }

    public function get_advanced_fields(): array {
        return $this->advanced_fields;
    }

    public function render(array $settings): string {
        // Content fields
        $title = $settings["title"] ?? "Ready to Get Started?";
        $description = $settings["description"] ?? "";
        $buttonText = $settings["button_text"] ?? "Get Started";
        $buttonUrl = $settings["button_url"] ?? "#";
        $buttonTarget = $settings["button_target"] ?? "_self";
        $secondaryButtonText = $settings["secondary_button_text"] ?? "";
        $secondaryButtonUrl = $settings["secondary_button_url"] ?? "#";
        $secondaryButtonTarget = $settings["secondary_button_target"] ?? "_self";

        // Layout fields
        $layout = $settings["layout"] ?? "horizontal";
        $textAlign = $settings["text_align"] ?? "left";

        // Background fields
        $backgroundColor = $settings["background_color"] ?? "#2563eb";
        $gradientEnabled = $settings["gradient_enabled"] ?? false;
        $gradientStart = $settings["gradient_color_start"] ?? "#2563eb";
        $gradientEnd = $settings["gradient_color_end"] ?? "#7c3aed";
        $gradientDirection = $settings["gradient_direction"] ?? "to right";

        // Typography fields
        $titleColor = $settings["title_color"] ?? "#ffffff";
        $titleFontSize = $settings["title_font_size"] ?? "28px";
        $titleFontWeight = $settings["title_font_weight"] ?? "700";
        $descriptionColor = $settings["description_color"] ?? "rgba(255, 255, 255, 0.9)";
        $descriptionFontSize = $settings["description_font_size"] ?? "16px";

        // Spacing fields
        $paddingTop = $settings["padding_top"] ?? "40px";
        $paddingBottom = $settings["padding_bottom"] ?? "40px";
        $paddingHorizontal = $settings["padding_horizontal"] ?? "32px";
        $borderRadius = $settings["border_radius"] ?? "12px";

        // Button styling
        $buttonStyle = $settings["button_style"] ?? "filled";
        $buttonBgColor = $settings["button_bg_color"] ?? "#ffffff";
        $buttonTextColor = $settings["button_text_color"] ?? "#2563eb";
        $buttonBorderRadius = $settings["button_border_radius"] ?? "6px";
        $secondaryButtonStyle = $settings["secondary_button_style"] ?? "outline";
        $secondaryButtonBg = $settings["secondary_button_bg"] ?? "transparent";
        $secondaryButtonTextColor = $settings["secondary_button_text_color"] ?? "#ffffff";
        $secondaryButtonBorderColor = $settings["secondary_button_border_color"] ?? "#ffffff";

        // Build CTA wrapper styles
        $ctaStyles = [
            "position: relative",
            "border-radius: " . esc_attr($borderRadius),
            "padding: " . esc_attr($paddingTop) . " " . esc_attr($paddingHorizontal) . " " . esc_attr($paddingBottom),
            "overflow: hidden"
        ];

        // Background
        if ($gradientEnabled) {
            $ctaStyles[] = sprintf(
                "background: linear-gradient(%s, %s, %s)",
                esc_attr($gradientDirection),
                esc_attr($gradientStart),
                esc_attr($gradientEnd)
            );
        } else {
            $ctaStyles[] = "background: " . esc_attr($backgroundColor);
        }

        $ctaStyle = implode("; ", $ctaStyles);

        // Build container styles based on layout
        $containerStyles = ["width: 100%", "max-width: 1200px", "margin: 0 auto"];

        if ($layout === "horizontal") {
            $containerStyles[] = "display: flex";
            $containerStyles[] = "align-items: center";
            $containerStyles[] = "justify-content: space-between";
            $containerStyles[] = "flex-wrap: wrap";
            $containerStyles[] = "gap: 24px";
        } else {
            $containerStyles[] = "text-align: center";
        }

        $containerStyle = implode("; ", $containerStyles);

        // Build content area styles
        $contentStyles = [];
        if ($layout === "horizontal") {
            $contentStyles[] = "flex: 1";
            $contentStyles[] = "min-width: 280px";
            $contentStyles[] = "text-align: " . esc_attr($textAlign);
        }
        $contentStyle = implode("; ", $contentStyles);

        // Build title styles
        $titleStyle = sprintf(
            "color:%s;font-size:%s;font-weight:%s;margin:0 0 8px 0;line-height:1.3",
            esc_attr($titleColor),
            esc_attr($titleFontSize),
            esc_attr($titleFontWeight)
        );

        // Build description styles
        $descriptionStyle = sprintf(
            "color:%s;font-size:%s;margin:0;line-height:1.6",
            esc_attr($descriptionColor),
            esc_attr($descriptionFontSize)
        );

        // Build primary button styles
        if ($buttonStyle === "outline") {
            $primaryBtnStyle = sprintf(
                "display:inline-flex;align-items:center;justify-content:center;padding:12px 24px;background:transparent;color:%s;border:2px solid %s;border-radius:%s;font-size:15px;font-weight:600;text-decoration:none;cursor:pointer;transition:all 0.2s",
                esc_attr($buttonTextColor),
                esc_attr($buttonBgColor),
                esc_attr($buttonBorderRadius)
            );
        } else {
            $primaryBtnStyle = sprintf(
                "display:inline-flex;align-items:center;justify-content:center;padding:12px 24px;background:%s;color:%s;border:none;border-radius:%s;font-size:15px;font-weight:600;text-decoration:none;cursor:pointer;transition:all 0.2s",
                esc_attr($buttonBgColor),
                esc_attr($buttonTextColor),
                esc_attr($buttonBorderRadius)
            );
        }

        // Build secondary button styles
        if ($secondaryButtonStyle === "outline") {
            $secondaryBtnStyle = sprintf(
                "display:inline-flex;align-items:center;justify-content:center;padding:12px 24px;background:transparent;color:%s;border:2px solid %s;border-radius:%s;font-size:15px;font-weight:600;text-decoration:none;cursor:pointer;transition:all 0.2s",
                esc_attr($secondaryButtonTextColor),
                esc_attr($secondaryButtonBorderColor),
                esc_attr($buttonBorderRadius)
            );
        } else {
            $secondaryBtnStyle = sprintf(
                "display:inline-flex;align-items:center;justify-content:center;padding:12px 24px;background:%s;color:%s;border:none;border-radius:%s;font-size:15px;font-weight:600;text-decoration:none;cursor:pointer;transition:all 0.2s",
                esc_attr($secondaryButtonBg),
                esc_attr($secondaryButtonTextColor),
                esc_attr($buttonBorderRadius)
            );
        }

        // Build buttons section
        $buttonsHtml = "";
        if ($buttonText || $secondaryButtonText) {
            $buttonsStyles = ["display:flex", "flex-wrap:wrap", "gap:12px"];
            if ($layout === "stacked") {
                $buttonsStyles[] = "justify-content:center";
                $buttonsStyles[] = "margin-top:24px";
            } else {
                $buttonsStyles[] = "flex-shrink:0";
            }

            $buttonsHtml = sprintf(
                "<div class=\"tb4-cta__buttons\" style=\"%s\">",
                implode(";", $buttonsStyles)
            );

            if ($buttonText) {
                $buttonsHtml .= sprintf(
                    "<a href=\"%s\" target=\"%s\" class=\"tb4-cta__btn tb4-cta__btn--primary\" style=\"%s\">%s</a>",
                    esc_attr($buttonUrl),
                    esc_attr($buttonTarget),
                    $primaryBtnStyle,
                    esc_html($buttonText)
                );
            }

            if ($secondaryButtonText) {
                $buttonsHtml .= sprintf(
                    "<a href=\"%s\" target=\"%s\" class=\"tb4-cta__btn tb4-cta__btn--secondary\" style=\"%s\">%s</a>",
                    esc_attr($secondaryButtonUrl),
                    esc_attr($secondaryButtonTarget),
                    $secondaryBtnStyle,
                    esc_html($secondaryButtonText)
                );
            }

            $buttonsHtml .= "</div>";
        }

        // Assemble the CTA
        $html = sprintf(
            "<div class=\"tb4-cta\" style=\"%s\"><div class=\"tb4-cta__container\" style=\"%s\">",
            $ctaStyle,
            $containerStyle
        );

        // Content section
        if ($layout === "horizontal") {
            $html .= sprintf("<div class=\"tb4-cta__content\" style=\"%s\">", $contentStyle);
        } else {
            $html .= "<div class=\"tb4-cta__content\">";
        }

        // Add title
        if ($title) {
            $html .= sprintf(
                "<h2 class=\"tb4-cta__title\" style=\"%s\">%s</h2>",
                $titleStyle,
                esc_html($title)
            );
        }

        // Add description
        if ($description) {
            $html .= sprintf(
                "<p class=\"tb4-cta__description\" style=\"%s\">%s</p>",
                $descriptionStyle,
                esc_html($description)
            );
        }

        $html .= "</div>";

        // Add buttons
        $html .= $buttonsHtml;

        // Close container and CTA
        $html .= "</div></div>";

        return $html;
    }
}
