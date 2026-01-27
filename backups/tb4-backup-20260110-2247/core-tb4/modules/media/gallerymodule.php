<?php
namespace Core\TB4\Modules\Media;

require_once dirname(__DIR__) . "/module.php";

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Gallery Module
 * Image gallery with grid, masonry, and carousel layouts
 * Features lightbox, hover effects, and captions
 */
class GalleryModule extends Module
{
    public function __construct()
    {
        $this->name = "Gallery";
        $this->slug = "gallery";
        $this->icon = "Grid3x3";
        $this->category = "media";

        $this->elements = [
            "main" => ".tb4-gallery",
            "grid" => ".tb4-gallery__grid",
            "item" => ".tb4-gallery__item",
            "image" => ".tb4-gallery__image",
            "overlay" => ".tb4-gallery__overlay",
            "caption" => ".tb4-gallery__caption",
            "lightbox" => ".tb4-gallery__lightbox",
            "nav" => ".tb4-gallery__nav"
        ];
    }

    public function get_content_fields(): array
    {
        return [
            // Images input - textarea for JSON array or comma-separated URLs
            "images_json" => [
                "label" => "Images (JSON Array)",
                "type" => "textarea",
                "description" => "Enter images as JSON array: [{\"src\":\"url\",\"alt\":\"text\",\"caption\":\"text\",\"link_url\":\"\"}] or one URL per line",
                "default" => "[\n  {\"src\": \"/uploads/sample1.jpg\", \"alt\": \"Sample Image 1\", \"caption\": \"Beautiful landscape\"},\n  {\"src\": \"/uploads/sample2.jpg\", \"alt\": \"Sample Image 2\", \"caption\": \"City skyline\"},\n  {\"src\": \"/uploads/sample3.jpg\", \"alt\": \"Sample Image 3\", \"caption\": \"Nature close-up\"},\n  {\"src\": \"/uploads/sample4.jpg\", \"alt\": \"Sample Image 4\", \"caption\": \"Abstract art\"}\n]"
            ],

            // Layout settings
            "layout" => [
                "label" => "Layout Style",
                "type" => "select",
                "options" => [
                    "grid" => "Grid (Equal Size)",
                    "masonry" => "Masonry (Pinterest Style)",
                    "carousel" => "Carousel (Horizontal Slider)"
                ],
                "default" => "grid"
            ],
            "columns" => [
                "label" => "Columns",
                "type" => "select",
                "options" => [
                    "2" => "2 Columns",
                    "3" => "3 Columns",
                    "4" => "4 Columns",
                    "5" => "5 Columns",
                    "6" => "6 Columns"
                ],
                "default" => "3"
            ],
            "gap" => [
                "label" => "Gap Between Images (px)",
                "type" => "text",
                "default" => "16"
            ],

            // Image styling
            "image_aspect_ratio" => [
                "label" => "Image Aspect Ratio",
                "type" => "select",
                "options" => [
                    "square" => "Square (1:1)",
                    "4_3" => "Landscape (4:3)",
                    "16_9" => "Widescreen (16:9)",
                    "3_4" => "Portrait (3:4)",
                    "original" => "Original"
                ],
                "default" => "square"
            ],
            "border_radius" => [
                "label" => "Border Radius",
                "type" => "text",
                "default" => "8px"
            ],

            // Hover effects
            "hover_effect" => [
                "label" => "Hover Effect",
                "type" => "select",
                "options" => [
                    "none" => "None",
                    "zoom" => "Zoom In",
                    "darken" => "Darken",
                    "caption-slide" => "Caption Slide Up"
                ],
                "default" => "zoom"
            ],

            // Caption settings
            "show_captions" => [
                "label" => "Show Captions",
                "type" => "select",
                "options" => [
                    "below" => "Below Image",
                    "on-hover" => "On Hover (Overlay)",
                    "hidden" => "Hidden"
                ],
                "default" => "on-hover"
            ],
            "caption_background" => [
                "label" => "Caption Background",
                "type" => "color",
                "default" => "rgba(0,0,0,0.7)"
            ],
            "caption_color" => [
                "label" => "Caption Text Color",
                "type" => "color",
                "default" => "#ffffff"
            ],
            "caption_font_size" => [
                "label" => "Caption Font Size",
                "type" => "text",
                "default" => "14px"
            ],

            // Lightbox
            "lightbox_enabled" => [
                "label" => "Enable Lightbox",
                "type" => "toggle",
                "default" => true
            ],

            // Carousel specific
            "pagination_style" => [
                "label" => "Pagination Style (Carousel)",
                "type" => "select",
                "options" => [
                    "dots" => "Dots",
                    "numbers" => "Numbers",
                    "none" => "None"
                ],
                "default" => "dots"
            ],
            "autoplay" => [
                "label" => "Autoplay (Carousel)",
                "type" => "toggle",
                "default" => false
            ],
            "autoplay_speed" => [
                "label" => "Autoplay Speed (ms)",
                "type" => "text",
                "default" => "5000"
            ],

            // Container styling
            "max_width" => [
                "label" => "Max Width",
                "type" => "text",
                "default" => "100%"
            ],
            "background_color" => [
                "label" => "Background Color",
                "type" => "color",
                "default" => "transparent"
            ],
            "padding" => [
                "label" => "Padding",
                "type" => "text",
                "default" => "0"
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Parse images input (JSON or line-separated URLs)
     */
    private function parseImages(string $input): array
    {
        $input = trim($input);
        if (empty($input)) {
            return [];
        }

        // Try JSON first
        if (strpos($input, '[') === 0 || strpos($input, '{') === 0) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                // Normalize to array of objects
                $images = [];
                foreach ($decoded as $item) {
                    if (is_string($item)) {
                        $images[] = ["src" => $item, "alt" => "", "caption" => "", "link_url" => ""];
                    } elseif (is_array($item)) {
                        $images[] = [
                            "src" => $item["src"] ?? "",
                            "alt" => $item["alt"] ?? "",
                            "caption" => $item["caption"] ?? "",
                            "link_url" => $item["link_url"] ?? ""
                        ];
                    }
                }
                return $images;
            }
        }

        // Fallback: line-separated URLs
        $lines = preg_split('/[\r\n]+/', $input);
        $images = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && (filter_var($line, FILTER_VALIDATE_URL) !== false || strpos($line, '/') === 0)) {
                $images[] = ["src" => $line, "alt" => "", "caption" => "", "link_url" => ""];
            }
        }

        return $images;
    }

    /**
     * Get aspect ratio padding value
     */
    private function getAspectRatioPadding(string $ratio): string
    {
        return match($ratio) {
            "square" => "100%",
            "4_3" => "75%",
            "16_9" => "56.25%",
            "3_4" => "133.33%",
            default => "0"
        };
    }

    public function render(array $settings): string
    {
        // Parse settings
        $imagesJson = $settings["images_json"] ?? "";
        $images = $this->parseImages($imagesJson);

        if (empty($images)) {
            return '<div class="tb4-gallery tb4-gallery--empty"><p>No images added yet. Add images in the settings panel.</p></div>';
        }

        $layout = $settings["layout"] ?? "grid";
        $columns = (int)($settings["columns"] ?? 3);
        $gap = $settings["gap"] ?? "16";
        $aspectRatio = $settings["image_aspect_ratio"] ?? "square";
        $borderRadius = $settings["border_radius"] ?? "8px";
        $hoverEffect = $settings["hover_effect"] ?? "zoom";
        $showCaptions = $settings["show_captions"] ?? "on-hover";
        $captionBg = $settings["caption_background"] ?? "rgba(0,0,0,0.7)";
        $captionColor = $settings["caption_color"] ?? "#ffffff";
        $captionFontSize = $settings["caption_font_size"] ?? "14px";
        $lightboxEnabled = $settings["lightbox_enabled"] ?? true;
        $paginationStyle = $settings["pagination_style"] ?? "dots";
        $autoplay = $settings["autoplay"] ?? false;
        $autoplaySpeed = $settings["autoplay_speed"] ?? "5000";
        $maxWidth = $settings["max_width"] ?? "100%";
        $backgroundColor = $settings["background_color"] ?? "transparent";
        $padding = $settings["padding"] ?? "0";

        // Generate unique ID for this gallery instance
        $galleryId = "tb4-gallery-" . uniqid();

        // Build container styles
        $containerStyles = [
            "max-width: " . esc_attr($maxWidth),
            "background: " . esc_attr($backgroundColor),
            "padding: " . esc_attr($padding),
            "margin: 0 auto"
        ];
        $containerStyle = implode("; ", $containerStyles);

        // Build grid styles
        $gridStyles = ["gap: " . esc_attr($gap) . "px"];

        if ($layout === "grid") {
            $gridStyles[] = "display: grid";
            $gridStyles[] = "grid-template-columns: repeat(" . $columns . ", 1fr)";
        } elseif ($layout === "masonry") {
            $gridStyles[] = "display: grid";
            $gridStyles[] = "grid-template-columns: repeat(" . $columns . ", 1fr)";
            $gridStyles[] = "grid-auto-rows: 10px";
        } elseif ($layout === "carousel") {
            $gridStyles[] = "display: flex";
            $gridStyles[] = "overflow-x: auto";
            $gridStyles[] = "scroll-snap-type: x mandatory";
            $gridStyles[] = "scroll-behavior: smooth";
            $gridStyles[] = "-webkit-overflow-scrolling: touch";
        }

        $gridStyle = implode("; ", $gridStyles);

        // Build item styles
        $itemStyles = [
            "border-radius: " . esc_attr($borderRadius),
            "overflow: hidden",
            "position: relative"
        ];

        if ($layout === "carousel") {
            $itemStyles[] = "flex: 0 0 calc(" . (100 / min($columns, 3)) . "% - " . $gap . "px)";
            $itemStyles[] = "scroll-snap-align: start";
        }

        $itemStyle = implode("; ", $itemStyles);

        // Build image wrapper styles for aspect ratio
        $imageWrapperStyles = ["position: relative", "overflow: hidden"];
        if ($aspectRatio !== "original") {
            $imageWrapperStyles[] = "padding-bottom: " . $this->getAspectRatioPadding($aspectRatio);
        }
        $imageWrapperStyle = implode("; ", $imageWrapperStyles);

        // Build image styles
        $imageStyles = ["display: block", "width: 100%", "height: auto", "transition: transform 0.3s ease"];
        if ($aspectRatio !== "original") {
            $imageStyles[] = "position: absolute";
            $imageStyles[] = "top: 0";
            $imageStyles[] = "left: 0";
            $imageStyles[] = "width: 100%";
            $imageStyles[] = "height: 100%";
            $imageStyles[] = "object-fit: cover";
        }
        $imageStyle = implode("; ", $imageStyles);

        // Build overlay styles
        $overlayStyles = [
            "position: absolute",
            "top: 0",
            "left: 0",
            "right: 0",
            "bottom: 0",
            "background: rgba(0,0,0,0)",
            "transition: background 0.3s ease",
            "display: flex",
            "align-items: flex-end",
            "justify-content: center"
        ];
        $overlayStyle = implode("; ", $overlayStyles);

        // Build caption styles
        $captionStyles = [
            "background: " . esc_attr($captionBg),
            "color: " . esc_attr($captionColor),
            "font-size: " . esc_attr($captionFontSize),
            "padding: 12px 16px",
            "width: 100%",
            "text-align: center",
            "transition: transform 0.3s ease, opacity 0.3s ease"
        ];

        if ($showCaptions === "on-hover") {
            $captionStyles[] = "transform: translateY(100%)";
            $captionStyles[] = "opacity: 0";
        } elseif ($showCaptions === "hidden") {
            $captionStyles[] = "display: none";
        }

        $captionStyle = implode("; ", $captionStyles);

        // Data attributes for JavaScript
        $dataAttrs = [
            'data-layout="' . esc_attr($layout) . '"',
            'data-hover="' . esc_attr($hoverEffect) . '"',
            'data-captions="' . esc_attr($showCaptions) . '"',
            'data-lightbox="' . ($lightboxEnabled ? 'true' : 'false') . '"',
            'data-pagination="' . esc_attr($paginationStyle) . '"',
            'data-autoplay="' . ($autoplay ? 'true' : 'false') . '"',
            'data-autoplay-speed="' . esc_attr($autoplaySpeed) . '"'
        ];

        // Start building HTML
        $html = sprintf(
            '<div id="%s" class="tb4-gallery tb4-gallery--%s tb4-gallery--hover-%s tb4-gallery--captions-%s" %s style="%s">',
            esc_attr($galleryId),
            esc_attr($layout),
            esc_attr($hoverEffect),
            esc_attr($showCaptions),
            implode(' ', $dataAttrs),
            $containerStyle
        );

        // Grid container
        $html .= sprintf('<div class="tb4-gallery__grid" style="%s">', $gridStyle);

        // Render each image
        foreach ($images as $index => $image) {
            $src = $image["src"] ?? "";
            $alt = $image["alt"] ?? "";
            $caption = $image["caption"] ?? "";
            $linkUrl = $image["link_url"] ?? "";

            if (empty($src)) {
                continue;
            }

            // Masonry random span for visual variety
            $masonrySpan = "";
            if ($layout === "masonry") {
                $spans = [20, 25, 30, 35, 40];
                $randomSpan = $spans[array_rand($spans)];
                $masonrySpan = " style=\"grid-row-end: span {$randomSpan};\"";
            }

            $html .= sprintf(
                '<div class="tb4-gallery__item" data-index="%d"%s style="%s">',
                $index,
                $masonrySpan ? $masonrySpan : '',
                $layout !== "masonry" ? $itemStyle : "border-radius: " . esc_attr($borderRadius) . "; overflow: hidden; position: relative"
            );

            // Image wrapper
            $html .= sprintf('<div class="tb4-gallery__image-wrapper" style="%s">', $aspectRatio !== "original" && $layout !== "masonry" ? $imageWrapperStyle : "position: relative; overflow: hidden");

            // Image
            $html .= sprintf(
                '<img class="tb4-gallery__image" src="%s" alt="%s" loading="lazy" style="%s">',
                esc_attr($src),
                esc_attr($alt),
                $aspectRatio !== "original" && $layout !== "masonry" ? $imageStyle : "display: block; width: 100%; height: auto"
            );

            // Overlay (for hover effects and captions)
            $html .= sprintf('<div class="tb4-gallery__overlay" style="%s">', $overlayStyle);

            // Caption
            if (!empty($caption) && $showCaptions !== "hidden") {
                $html .= sprintf(
                    '<div class="tb4-gallery__caption" style="%s">%s</div>',
                    $captionStyle,
                    esc_html($caption)
                );
            }

            $html .= '</div>'; // Close overlay

            // Lightbox trigger or external link
            if ($lightboxEnabled) {
                $html .= sprintf(
                    '<button type="button" class="tb4-gallery__lightbox-trigger" data-src="%s" data-alt="%s" data-caption="%s" aria-label="Open image in lightbox" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: transparent; border: none; cursor: pointer;"></button>',
                    esc_attr($src),
                    esc_attr($alt),
                    esc_attr($caption)
                );
            } elseif (!empty($linkUrl)) {
                $html .= sprintf(
                    '<a href="%s" class="tb4-gallery__link" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;"></a>',
                    esc_attr($linkUrl)
                );
            }

            $html .= '</div>'; // Close image wrapper
            $html .= '</div>'; // Close item
        }

        $html .= '</div>'; // Close grid

        // Carousel navigation
        if ($layout === "carousel") {
            $html .= '<div class="tb4-gallery__nav" style="display: flex; justify-content: center; gap: 8px; margin-top: 16px;">';
            $html .= '<button type="button" class="tb4-gallery__nav-prev" aria-label="Previous" style="padding: 8px 16px; background: #334155; border: none; border-radius: 6px; color: #e2e8f0; cursor: pointer;">';
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>';
            $html .= '</button>';

            // Pagination
            if ($paginationStyle !== "none") {
                $html .= '<div class="tb4-gallery__pagination" style="display: flex; align-items: center; gap: 6px;">';
                $imageCount = count($images);
                for ($i = 0; $i < $imageCount; $i++) {
                    if ($paginationStyle === "dots") {
                        $html .= sprintf(
                            '<button type="button" class="tb4-gallery__dot%s" data-slide="%d" aria-label="Go to slide %d" style="width: 10px; height: 10px; border-radius: 50%%; background: %s; border: none; cursor: pointer; transition: background 0.2s;"></button>',
                            $i === 0 ? ' active' : '',
                            $i,
                            $i + 1,
                            $i === 0 ? '#3b82f6' : '#475569'
                        );
                    } else {
                        $html .= sprintf(
                            '<button type="button" class="tb4-gallery__page-num%s" data-slide="%d" style="min-width: 28px; padding: 4px 8px; background: %s; border: none; border-radius: 4px; color: %s; font-size: 12px; cursor: pointer;">%d</button>',
                            $i === 0 ? ' active' : '',
                            $i,
                            $i === 0 ? '#3b82f6' : '#475569',
                            '#ffffff',
                            $i + 1
                        );
                    }
                }
                $html .= '</div>';
            }

            $html .= '<button type="button" class="tb4-gallery__nav-next" aria-label="Next" style="padding: 8px 16px; background: #334155; border: none; border-radius: 6px; color: #e2e8f0; cursor: pointer;">';
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
            $html .= '</button>';
            $html .= '</div>'; // Close nav
        }

        $html .= '</div>'; // Close main container

        return $html;
    }
}
