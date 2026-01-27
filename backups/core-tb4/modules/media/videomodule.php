<?php
namespace Core\TB4\Modules\Media;

require_once dirname(__DIR__) . "/module.php";

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Video Module
 * Embed YouTube, Vimeo, or self-hosted videos
 * Features lazy loading, custom play buttons, and poster images
 */
class VideoModule extends Module
{
    public function __construct()
    {
        $this->name = "Video";
        $this->slug = "video";
        $this->icon = "PlayCircle";
        $this->category = "media";

        $this->elements = [
            "main" => ".tb4-video",
            "wrapper" => ".tb4-video__wrapper",
            "poster" => ".tb4-video__poster",
            "play_btn" => ".tb4-video__play-btn",
            "iframe" => ".tb4-video__iframe",
            "video" => ".tb4-video__player",
            "caption" => ".tb4-video__caption"
        ];
    }

    public function get_content_fields(): array
    {
        return [
            // Video source
            "video_source" => [
                "label" => "Video Source",
                "type" => "select",
                "options" => [
                    "youtube" => "YouTube",
                    "vimeo" => "Vimeo",
                    "self_hosted" => "Self-Hosted (MP4)"
                ],
                "default" => "youtube"
            ],

            // Video URL
            "video_url" => [
                "label" => "Video URL",
                "type" => "text",
                "description" => "YouTube/Vimeo URL or direct MP4 link",
                "default" => "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
            ],

            // Poster image
            "poster_image" => [
                "label" => "Poster Image (Thumbnail)",
                "type" => "text",
                "description" => "Image shown before video plays. Leave empty for auto-thumbnail (YouTube/Vimeo)",
                "default" => ""
            ],

            // Title for accessibility
            "title" => [
                "label" => "Video Title",
                "type" => "text",
                "description" => "Title for accessibility (screen readers)",
                "default" => "Embedded video"
            ],

            // Description/caption
            "description" => [
                "label" => "Caption",
                "type" => "textarea",
                "description" => "Optional caption shown below video",
                "default" => ""
            ],

            // Aspect ratio
            "aspect_ratio" => [
                "label" => "Aspect Ratio",
                "type" => "select",
                "options" => [
                    "16_9" => "16:9 (Widescreen)",
                    "4_3" => "4:3 (Standard)",
                    "21_9" => "21:9 (Ultrawide)",
                    "1_1" => "1:1 (Square)",
                    "9_16" => "9:16 (Vertical)"
                ],
                "default" => "16_9"
            ],

            // Max width
            "max_width" => [
                "label" => "Max Width",
                "type" => "select",
                "options" => [
                    "100%" => "Full Width",
                    "800px" => "800px",
                    "1000px" => "1000px",
                    "1200px" => "1200px"
                ],
                "default" => "100%"
            ],

            // Alignment
            "alignment" => [
                "label" => "Alignment",
                "type" => "select",
                "options" => [
                    "left" => "Left",
                    "center" => "Center",
                    "right" => "Right"
                ],
                "default" => "center"
            ],

            // Border radius
            "border_radius" => [
                "label" => "Border Radius",
                "type" => "text",
                "default" => "8px"
            ],

            // Box shadow
            "box_shadow" => [
                "label" => "Box Shadow",
                "type" => "toggle",
                "default" => false
            ],

            // Autoplay (muted)
            "autoplay" => [
                "label" => "Autoplay (Muted)",
                "type" => "toggle",
                "default" => false
            ],

            // Loop
            "loop" => [
                "label" => "Loop Video",
                "type" => "toggle",
                "default" => false
            ],

            // Show controls
            "controls" => [
                "label" => "Show Controls",
                "type" => "toggle",
                "default" => true
            ],

            // Muted
            "muted" => [
                "label" => "Muted",
                "type" => "toggle",
                "default" => false
            ],

            // Lazy load
            "lazy_load" => [
                "label" => "Lazy Load (Click to Play)",
                "type" => "toggle",
                "description" => "Show poster until user clicks to load video",
                "default" => true
            ],

            // Play button style
            "play_button_style" => [
                "label" => "Play Button Style",
                "type" => "select",
                "options" => [
                    "default" => "Default (Circle)",
                    "minimal" => "Minimal",
                    "youtube" => "YouTube Style"
                ],
                "default" => "default"
            ],

            // Play button color
            "play_button_color" => [
                "label" => "Play Button Color",
                "type" => "color",
                "default" => "#ffffff"
            ],

            // Overlay color
            "overlay_color" => [
                "label" => "Overlay Color",
                "type" => "color",
                "default" => "rgba(0,0,0,0.3)"
            ],

            // Caption styling
            "caption_alignment" => [
                "label" => "Caption Alignment",
                "type" => "select",
                "options" => [
                    "left" => "Left",
                    "center" => "Center",
                    "right" => "Right"
                ],
                "default" => "center"
            ],
            "caption_color" => [
                "label" => "Caption Color",
                "type" => "color",
                "default" => "#6b7280"
            ],
            "caption_font_size" => [
                "label" => "Caption Font Size",
                "type" => "text",
                "default" => "14px"
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Extract video ID from YouTube URL
     */
    private function extractYouTubeId(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract video ID from Vimeo URL
     */
    private function extractVimeoId(string $url): ?string
    {
        $patterns = [
            '/vimeo\.com\/(\d+)/',
            '/vimeo\.com\/video\/(\d+)/',
            '/player\.vimeo\.com\/video\/(\d+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Get aspect ratio padding value
     */
    private function getAspectRatioPadding(string $ratio): string
    {
        return match($ratio) {
            "16_9" => "56.25%",
            "4_3" => "75%",
            "21_9" => "42.86%",
            "1_1" => "100%",
            "9_16" => "177.78%",
            default => "56.25%"
        };
    }

    /**
     * Get YouTube thumbnail URL
     */
    private function getYouTubeThumbnail(string $videoId): string
    {
        return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }

    /**
     * Build YouTube embed URL
     */
    private function buildYouTubeEmbedUrl(string $videoId, array $settings): string
    {
        // Use privacy-enhanced mode
        $baseUrl = "https://www.youtube-nocookie.com/embed/{$videoId}";

        $params = [];

        if ($settings["autoplay"] ?? false) {
            $params[] = "autoplay=1";
            $params[] = "mute=1"; // Required for autoplay
        }

        if ($settings["muted"] ?? false) {
            $params[] = "mute=1";
        }

        if ($settings["loop"] ?? false) {
            $params[] = "loop=1";
            $params[] = "playlist={$videoId}"; // Required for loop
        }

        if (!($settings["controls"] ?? true)) {
            $params[] = "controls=0";
        }

        // Disable related videos from other channels
        $params[] = "rel=0";
        $params[] = "modestbranding=1";

        return $baseUrl . (!empty($params) ? "?" . implode("&", $params) : "");
    }

    /**
     * Build Vimeo embed URL
     */
    private function buildVimeoEmbedUrl(string $videoId, array $settings): string
    {
        $baseUrl = "https://player.vimeo.com/video/{$videoId}";

        $params = [];

        if ($settings["autoplay"] ?? false) {
            $params[] = "autoplay=1";
            $params[] = "muted=1";
        }

        if ($settings["muted"] ?? false) {
            $params[] = "muted=1";
        }

        if ($settings["loop"] ?? false) {
            $params[] = "loop=1";
        }

        // Vimeo doesn't have a controls parameter, but we can use background mode
        if (!($settings["controls"] ?? true)) {
            $params[] = "background=1";
        }

        $params[] = "dnt=1"; // Do not track

        return $baseUrl . (!empty($params) ? "?" . implode("&", $params) : "");
    }

    /**
     * Render play button SVG
     */
    private function renderPlayButton(string $style, string $color): string
    {
        $colorEsc = esc_attr($color);

        if ($style === "minimal") {
            return <<<SVG
<svg class="tb4-video__play-icon tb4-video__play-icon--minimal" width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
    <polygon points="24,18 48,32 24,46" fill="{$colorEsc}" />
</svg>
SVG;
        } elseif ($style === "youtube") {
            return <<<SVG
<svg class="tb4-video__play-icon tb4-video__play-icon--youtube" width="68" height="48" viewBox="0 0 68 48" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M66.52 7.74c-.78-2.93-3.08-5.24-6-6.02C55.22 0 34 0 34 0S12.78 0 7.48 1.72c-2.92.78-5.22 3.09-6 6.02C0 13.05 0 24 0 24s0 10.95 1.48 16.26c.78 2.93 3.08 5.24 6 6.02C12.78 48 34 48 34 48s21.22 0 26.52-1.72c2.92-.78 5.22-3.09 6-6.02C68 34.95 68 24 68 24s0-10.95-1.48-16.26z" fill="#FF0000"/>
    <polygon points="27,17 45,24 27,31" fill="{$colorEsc}"/>
</svg>
SVG;
        }

        // Default circle style
        return <<<SVG
<svg class="tb4-video__play-icon tb4-video__play-icon--default" width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="40" cy="40" r="38" fill="rgba(0,0,0,0.6)" stroke="{$colorEsc}" stroke-width="3"/>
    <polygon points="32,24 56,40 32,56" fill="{$colorEsc}"/>
</svg>
SVG;
    }

    public function render(array $settings): string
    {
        // Parse settings
        $videoSource = $settings["video_source"] ?? "youtube";
        $videoUrl = trim($settings["video_url"] ?? "");
        $posterImage = trim($settings["poster_image"] ?? "");
        $title = $settings["title"] ?? "Embedded video";
        $description = $settings["description"] ?? "";
        $aspectRatio = $settings["aspect_ratio"] ?? "16_9";
        $maxWidth = $settings["max_width"] ?? "100%";
        $alignment = $settings["alignment"] ?? "center";
        $borderRadius = $settings["border_radius"] ?? "8px";
        $boxShadow = $settings["box_shadow"] ?? false;
        $autoplay = $settings["autoplay"] ?? false;
        $loop = $settings["loop"] ?? false;
        $controls = $settings["controls"] ?? true;
        $muted = $settings["muted"] ?? false;
        $lazyLoad = $settings["lazy_load"] ?? true;
        $playButtonStyle = $settings["play_button_style"] ?? "default";
        $playButtonColor = $settings["play_button_color"] ?? "#ffffff";
        $overlayColor = $settings["overlay_color"] ?? "rgba(0,0,0,0.3)";
        $captionAlignment = $settings["caption_alignment"] ?? "center";
        $captionColor = $settings["caption_color"] ?? "#6b7280";
        $captionFontSize = $settings["caption_font_size"] ?? "14px";

        // Validate URL
        if (empty($videoUrl)) {
            return '<div class="tb4-video tb4-video--empty"><p>No video URL provided. Add a video URL in the settings panel.</p></div>';
        }

        // Extract video ID
        $videoId = null;
        $embedUrl = "";

        if ($videoSource === "youtube") {
            $videoId = $this->extractYouTubeId($videoUrl);
            if (!$videoId) {
                return '<div class="tb4-video tb4-video--error"><p>Invalid YouTube URL. Please check the video URL.</p></div>';
            }
            $embedUrl = $this->buildYouTubeEmbedUrl($videoId, $settings);
            // Auto-generate poster if not provided
            if (empty($posterImage)) {
                $posterImage = $this->getYouTubeThumbnail($videoId);
            }
        } elseif ($videoSource === "vimeo") {
            $videoId = $this->extractVimeoId($videoUrl);
            if (!$videoId) {
                return '<div class="tb4-video tb4-video--error"><p>Invalid Vimeo URL. Please check the video URL.</p></div>';
            }
            $embedUrl = $this->buildVimeoEmbedUrl($videoId, $settings);
        } elseif ($videoSource === "self_hosted") {
            // Validate MP4 URL
            if (!filter_var($videoUrl, FILTER_VALIDATE_URL) && strpos($videoUrl, '/') !== 0) {
                return '<div class="tb4-video tb4-video--error"><p>Invalid video URL. Please provide a valid MP4 URL.</p></div>';
            }
        }

        // Generate unique ID
        $moduleId = "tb4-video-" . uniqid();

        // Calculate alignment margin
        $marginStyle = match($alignment) {
            "left" => "margin-right: auto;",
            "right" => "margin-left: auto;",
            default => "margin: 0 auto;"
        };

        // Container styles
        $containerStyles = [
            "max-width: " . esc_attr($maxWidth),
            $marginStyle
        ];

        // Wrapper styles (for aspect ratio)
        $wrapperStyles = [
            "position: relative",
            "width: 100%",
            "padding-bottom: " . $this->getAspectRatioPadding($aspectRatio),
            "border-radius: " . esc_attr($borderRadius),
            "overflow: hidden"
        ];

        if ($boxShadow) {
            $wrapperStyles[] = "box-shadow: 0 10px 40px rgba(0,0,0,0.3)";
        }

        // Data attributes for lazy loading
        $dataAttrs = [
            'data-video-id="' . esc_attr($videoId ?? '') . '"',
            'data-video-source="' . esc_attr($videoSource) . '"',
            'data-embed-url="' . esc_attr($embedUrl) . '"',
            'data-video-url="' . esc_attr($videoUrl) . '"',
            'data-lazy="' . ($lazyLoad ? 'true' : 'false') . '"',
            'data-autoplay="' . ($autoplay ? 'true' : 'false') . '"',
            'data-loop="' . ($loop ? 'true' : 'false') . '"',
            'data-controls="' . ($controls ? 'true' : 'false') . '"',
            'data-muted="' . ($muted || $autoplay ? 'true' : 'false') . '"'
        ];

        // Build HTML
        $html = sprintf(
            '<div id="%s" class="tb4-video tb4-video--%s%s" %s style="%s">',
            esc_attr($moduleId),
            esc_attr($videoSource),
            $lazyLoad ? ' tb4-video--lazy' : '',
            implode(' ', $dataAttrs),
            implode('; ', $containerStyles)
        );

        $html .= sprintf('<div class="tb4-video__wrapper" style="%s">', implode('; ', $wrapperStyles));

        // Determine what to render based on lazy load setting
        if ($lazyLoad && !$autoplay) {
            // Render poster with play button
            $html .= $this->renderPosterOverlay($posterImage, $title, $overlayColor, $playButtonStyle, $playButtonColor);
        } else {
            // Render video directly
            if ($videoSource === "self_hosted") {
                $html .= $this->renderSelfHostedVideo($videoUrl, $posterImage, $title, $autoplay, $loop, $controls, $muted);
            } else {
                $html .= $this->renderIframe($embedUrl, $title);
            }
        }

        $html .= '</div>'; // Close wrapper

        // Caption
        if (!empty($description)) {
            $html .= sprintf(
                '<p class="tb4-video__caption" style="text-align: %s; color: %s; font-size: %s; margin-top: 12px;">%s</p>',
                esc_attr($captionAlignment),
                esc_attr($captionColor),
                esc_attr($captionFontSize),
                esc_html($description)
            );
        }

        $html .= '</div>'; // Close main container

        return $html;
    }

    /**
     * Render poster overlay with play button
     */
    private function renderPosterOverlay(string $posterImage, string $title, string $overlayColor, string $playButtonStyle, string $playButtonColor): string
    {
        $posterStyle = "position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;";
        $overlayStyle = sprintf(
            "position: absolute; top: 0; left: 0; width: 100%%; height: 100%%; background: %s; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s ease;",
            esc_attr($overlayColor)
        );

        $html = sprintf(
            '<div class="tb4-video__poster-container" style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%;">',
        );

        // Poster image
        if (!empty($posterImage)) {
            $html .= sprintf(
                '<img class="tb4-video__poster" src="%s" alt="%s" loading="lazy" style="%s">',
                esc_attr($posterImage),
                esc_attr($title),
                $posterStyle
            );
        } else {
            // Placeholder gradient if no poster
            $html .= '<div class="tb4-video__poster-placeholder" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #1e293b, #0f172a);"></div>';
        }

        // Overlay with play button
        $html .= sprintf('<div class="tb4-video__overlay" style="%s">', $overlayStyle);
        $html .= sprintf(
            '<button type="button" class="tb4-video__play-btn" aria-label="Play video: %s" style="background: none; border: none; cursor: pointer; transition: transform 0.2s ease;">',
            esc_attr($title)
        );
        $html .= $this->renderPlayButton($playButtonStyle, $playButtonColor);
        $html .= '</button>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render iframe embed
     */
    private function renderIframe(string $embedUrl, string $title): string
    {
        return sprintf(
            '<iframe class="tb4-video__iframe" src="%s" title="%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%; border: 0;"></iframe>',
            esc_attr($embedUrl),
            esc_attr($title)
        );
    }

    /**
     * Render self-hosted video player
     */
    private function renderSelfHostedVideo(string $videoUrl, string $posterImage, string $title, bool $autoplay, bool $loop, bool $controls, bool $muted): string
    {
        $attrs = ['playsinline'];

        if ($autoplay) {
            $attrs[] = 'autoplay';
            $attrs[] = 'muted'; // Required for autoplay
        } elseif ($muted) {
            $attrs[] = 'muted';
        }

        if ($loop) {
            $attrs[] = 'loop';
        }

        if ($controls) {
            $attrs[] = 'controls';
        }

        $posterAttr = !empty($posterImage) ? sprintf(' poster="%s"', esc_attr($posterImage)) : '';

        return sprintf(
            '<video class="tb4-video__player" title="%s"%s %s style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%; object-fit: cover;"><source src="%s" type="video/mp4">Your browser does not support the video tag.</video>',
            esc_attr($title),
            $posterAttr,
            implode(' ', $attrs),
            esc_attr($videoUrl)
        );
    }
}
