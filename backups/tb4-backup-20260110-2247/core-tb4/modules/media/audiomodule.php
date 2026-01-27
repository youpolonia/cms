<?php
namespace Core\TB4\Modules\Media;

require_once dirname(__DIR__) . "/module.php";

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Audio Module
 * Embed self-hosted audio, SoundCloud, or Spotify tracks
 * Features custom player styles, cover art, and metadata display
 */
class AudioModule extends Module
{
    public function __construct()
    {
        $this->name = "Audio";
        $this->slug = "audio";
        $this->icon = "Volume2";
        $this->category = "media";

        $this->elements = [
            "main" => ".tb4-audio",
            "wrapper" => ".tb4-audio__wrapper",
            "cover" => ".tb4-audio__cover",
            "info" => ".tb4-audio__info",
            "title" => ".tb4-audio__title",
            "artist" => ".tb4-audio__artist",
            "player" => ".tb4-audio__player",
            "controls" => ".tb4-audio__controls",
            "progress" => ".tb4-audio__progress",
            "time" => ".tb4-audio__time"
        ];
    }

    public function get_content_fields(): array
    {
        return [
            // Audio source
            "audio_source" => [
                "label" => "Audio Source",
                "type" => "select",
                "options" => [
                    "self_hosted" => "Self-Hosted (MP3/WAV)",
                    "soundcloud" => "SoundCloud",
                    "spotify" => "Spotify"
                ],
                "default" => "self_hosted"
            ],

            // Audio URL
            "audio_url" => [
                "label" => "Audio URL",
                "type" => "text",
                "description" => "Direct MP3/WAV URL, SoundCloud track URL, or Spotify embed URL",
                "default" => ""
            ],

            // Track title
            "title" => [
                "label" => "Track Title",
                "type" => "text",
                "description" => "Title of the track or episode",
                "default" => "Audio Track"
            ],

            // Artist/Author
            "artist" => [
                "label" => "Artist / Author",
                "type" => "text",
                "description" => "Artist, band, or podcast author",
                "default" => ""
            ],

            // Album/Podcast name
            "album" => [
                "label" => "Album / Podcast Name",
                "type" => "text",
                "description" => "Optional album or podcast series name",
                "default" => ""
            ],

            // Cover image
            "cover_image" => [
                "label" => "Cover Image",
                "type" => "text",
                "description" => "Album art or episode cover image URL",
                "default" => ""
            ],

            // Description
            "description" => [
                "label" => "Description",
                "type" => "textarea",
                "description" => "Optional track or episode description",
                "default" => ""
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            // Player style
            "player_style" => [
                "label" => "Player Style",
                "type" => "select",
                "options" => [
                    "standard" => "Standard (Native Player)",
                    "minimal" => "Minimal (Thin Bar)",
                    "card" => "Card (Full Layout)"
                ],
                "default" => "card"
            ],

            // Background color
            "background_color" => [
                "label" => "Background Color",
                "type" => "color",
                "default" => "#1e293b"
            ],

            // Accent color
            "accent_color" => [
                "label" => "Accent Color",
                "type" => "color",
                "description" => "Color for progress bar and buttons",
                "default" => "#3b82f6"
            ],

            // Text color
            "text_color" => [
                "label" => "Text Color",
                "type" => "color",
                "default" => "#e2e8f0"
            ],

            // Show cover
            "show_cover" => [
                "label" => "Show Cover Art",
                "type" => "toggle",
                "default" => true
            ],

            // Cover size
            "cover_size" => [
                "label" => "Cover Size",
                "type" => "select",
                "options" => [
                    "60px" => "Small (60px)",
                    "80px" => "Medium (80px)",
                    "100px" => "Large (100px)",
                    "120px" => "Extra Large (120px)"
                ],
                "default" => "80px"
            ],

            // Cover border radius
            "cover_border_radius" => [
                "label" => "Cover Border Radius",
                "type" => "text",
                "default" => "8px"
            ],

            // Show title
            "show_title" => [
                "label" => "Show Title",
                "type" => "toggle",
                "default" => true
            ],

            // Show artist
            "show_artist" => [
                "label" => "Show Artist",
                "type" => "toggle",
                "default" => true
            ],

            // Show duration
            "show_duration" => [
                "label" => "Show Duration",
                "type" => "toggle",
                "default" => true
            ],

            // Show download button
            "show_download" => [
                "label" => "Show Download Button",
                "type" => "toggle",
                "description" => "Only for self-hosted audio",
                "default" => false
            ],

            // Autoplay
            "autoplay" => [
                "label" => "Autoplay",
                "type" => "toggle",
                "default" => false
            ],

            // Loop
            "loop" => [
                "label" => "Loop Audio",
                "type" => "toggle",
                "default" => false
            ],

            // Muted
            "muted" => [
                "label" => "Start Muted",
                "type" => "toggle",
                "default" => false
            ],

            // Padding
            "padding" => [
                "label" => "Padding",
                "type" => "text",
                "default" => "16px"
            ],

            // Border radius
            "border_radius" => [
                "label" => "Border Radius",
                "type" => "text",
                "default" => "12px"
            ],

            // Box shadow
            "box_shadow" => [
                "label" => "Box Shadow",
                "type" => "toggle",
                "default" => true
            ],

            // Max width
            "max_width" => [
                "label" => "Max Width",
                "type" => "select",
                "options" => [
                    "100%" => "Full Width",
                    "400px" => "400px",
                    "500px" => "500px",
                    "600px" => "600px",
                    "800px" => "800px"
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
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Extract SoundCloud track URL for embedding
     */
    private function buildSoundCloudEmbed(string $url): string
    {
        $encodedUrl = urlencode($url);
        return "https://w.soundcloud.com/player/?url={$encodedUrl}&color=%233b82f6&auto_play=false&hide_related=true&show_comments=false&show_user=true&show_reposts=false&show_teaser=false";
    }

    /**
     * Extract Spotify embed URL
     */
    private function buildSpotifyEmbed(string $url): ?string
    {
        // Convert spotify:track:ID or open.spotify.com/track/ID to embed URL
        $patterns = [
            '/spotify\.com\/track\/([a-zA-Z0-9]+)/',
            '/spotify\.com\/episode\/([a-zA-Z0-9]+)/',
            '/spotify:track:([a-zA-Z0-9]+)/',
            '/spotify:episode:([a-zA-Z0-9]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                $type = strpos($pattern, 'episode') !== false ? 'episode' : 'track';
                return "https://open.spotify.com/embed/{$type}/{$matches[1]}?utm_source=generator&theme=0";
            }
        }

        // Already an embed URL
        if (strpos($url, 'open.spotify.com/embed/') !== false) {
            return $url;
        }

        return null;
    }

    /**
     * Render play/pause button SVG
     */
    private function renderPlayButton(string $color): string
    {
        return <<<SVG
<svg class="tb4-audio__play-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <polygon class="tb4-audio__play-shape" points="6,4 20,12 6,20" fill="{$color}" />
</svg>
SVG;
    }

    /**
     * Render pause button SVG
     */
    private function renderPauseButton(string $color): string
    {
        return <<<SVG
<svg class="tb4-audio__pause-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:none;">
    <rect class="tb4-audio__pause-shape" x="5" y="4" width="4" height="16" fill="{$color}" />
    <rect class="tb4-audio__pause-shape" x="15" y="4" width="4" height="16" fill="{$color}" />
</svg>
SVG;
    }

    /**
     * Render volume icon SVG
     */
    private function renderVolumeIcon(string $color): string
    {
        return <<<SVG
<svg class="tb4-audio__volume-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="{$color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
</svg>
SVG;
    }

    /**
     * Render download icon SVG
     */
    private function renderDownloadIcon(string $color): string
    {
        return <<<SVG
<svg class="tb4-audio__download-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{$color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
    <polyline points="7 10 12 15 17 10"></polyline>
    <line x1="12" y1="15" x2="12" y2="3"></line>
</svg>
SVG;
    }

    public function render(array $settings): string
    {
        // Parse content settings
        $audioSource = $settings["audio_source"] ?? "self_hosted";
        $audioUrl = trim($settings["audio_url"] ?? "");
        $title = $settings["title"] ?? "Audio Track";
        $artist = $settings["artist"] ?? "";
        $album = $settings["album"] ?? "";
        $coverImage = trim($settings["cover_image"] ?? "");
        $description = $settings["description"] ?? "";

        // Parse design settings
        $playerStyle = $settings["player_style"] ?? "card";
        $backgroundColor = $settings["background_color"] ?? "#1e293b";
        $accentColor = $settings["accent_color"] ?? "#3b82f6";
        $textColor = $settings["text_color"] ?? "#e2e8f0";
        $showCover = $settings["show_cover"] ?? true;
        $coverSize = $settings["cover_size"] ?? "80px";
        $coverBorderRadius = $settings["cover_border_radius"] ?? "8px";
        $showTitle = $settings["show_title"] ?? true;
        $showArtist = $settings["show_artist"] ?? true;
        $showDuration = $settings["show_duration"] ?? true;
        $showDownload = $settings["show_download"] ?? false;
        $autoplay = $settings["autoplay"] ?? false;
        $loop = $settings["loop"] ?? false;
        $muted = $settings["muted"] ?? false;
        $padding = $settings["padding"] ?? "16px";
        $borderRadius = $settings["border_radius"] ?? "12px";
        $boxShadow = $settings["box_shadow"] ?? true;
        $maxWidth = $settings["max_width"] ?? "100%";
        $alignment = $settings["alignment"] ?? "center";

        // Validate URL
        if (empty($audioUrl)) {
            return '<div class="tb4-audio tb4-audio--empty"><p>No audio URL provided. Add an audio URL in the settings panel.</p></div>';
        }

        // Generate unique ID
        $moduleId = "tb4-audio-" . uniqid();

        // Alignment margin
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

        // Data attributes
        $dataAttrs = [
            'data-audio-source="' . esc_attr($audioSource) . '"',
            'data-audio-url="' . esc_attr($audioUrl) . '"',
            'data-autoplay="' . ($autoplay ? 'true' : 'false') . '"',
            'data-loop="' . ($loop ? 'true' : 'false') . '"',
            'data-muted="' . ($muted ? 'true' : 'false') . '"'
        ];

        // Render based on source type
        if ($audioSource === "soundcloud") {
            return $this->renderSoundCloud($moduleId, $audioUrl, $containerStyles, $dataAttrs, $settings);
        } elseif ($audioSource === "spotify") {
            return $this->renderSpotify($moduleId, $audioUrl, $containerStyles, $dataAttrs, $settings);
        } else {
            // Self-hosted audio with custom player
            return $this->renderSelfHosted($moduleId, $audioUrl, $containerStyles, $dataAttrs, $settings);
        }
    }

    /**
     * Render SoundCloud embed
     */
    private function renderSoundCloud(string $moduleId, string $audioUrl, array $containerStyles, array $dataAttrs, array $settings): string
    {
        $embedUrl = $this->buildSoundCloudEmbed($audioUrl);
        $borderRadius = $settings["border_radius"] ?? "12px";

        $html = sprintf(
            '<div id="%s" class="tb4-audio tb4-audio--soundcloud" %s style="%s">',
            esc_attr($moduleId),
            implode(' ', $dataAttrs),
            implode('; ', $containerStyles)
        );

        $html .= sprintf(
            '<iframe class="tb4-audio__embed" width="100%%" height="166" scrolling="no" frameborder="no" allow="autoplay" src="%s" style="border-radius: %s;"></iframe>',
            esc_attr($embedUrl),
            esc_attr($borderRadius)
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Render Spotify embed
     */
    private function renderSpotify(string $moduleId, string $audioUrl, array $containerStyles, array $dataAttrs, array $settings): string
    {
        $embedUrl = $this->buildSpotifyEmbed($audioUrl);
        $borderRadius = $settings["border_radius"] ?? "12px";

        if (!$embedUrl) {
            return '<div class="tb4-audio tb4-audio--error"><p>Invalid Spotify URL. Please provide a valid track or episode URL.</p></div>';
        }

        $html = sprintf(
            '<div id="%s" class="tb4-audio tb4-audio--spotify" %s style="%s">',
            esc_attr($moduleId),
            implode(' ', $dataAttrs),
            implode('; ', $containerStyles)
        );

        $html .= sprintf(
            '<iframe class="tb4-audio__embed" src="%s" width="100%%" height="152" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" style="border-radius: %s;"></iframe>',
            esc_attr($embedUrl),
            esc_attr($borderRadius)
        );

        $html .= '</div>';

        return $html;
    }

    /**
     * Render self-hosted audio with custom player
     */
    private function renderSelfHosted(string $moduleId, string $audioUrl, array $containerStyles, array $dataAttrs, array $settings): string
    {
        // Extract settings
        $title = $settings["title"] ?? "Audio Track";
        $artist = $settings["artist"] ?? "";
        $album = $settings["album"] ?? "";
        $coverImage = trim($settings["cover_image"] ?? "");
        $description = $settings["description"] ?? "";
        $playerStyle = $settings["player_style"] ?? "card";
        $backgroundColor = $settings["background_color"] ?? "#1e293b";
        $accentColor = $settings["accent_color"] ?? "#3b82f6";
        $textColor = $settings["text_color"] ?? "#e2e8f0";
        $showCover = $settings["show_cover"] ?? true;
        $coverSize = $settings["cover_size"] ?? "80px";
        $coverBorderRadius = $settings["cover_border_radius"] ?? "8px";
        $showTitle = $settings["show_title"] ?? true;
        $showArtist = $settings["show_artist"] ?? true;
        $showDuration = $settings["show_duration"] ?? true;
        $showDownload = $settings["show_download"] ?? false;
        $autoplay = $settings["autoplay"] ?? false;
        $loop = $settings["loop"] ?? false;
        $muted = $settings["muted"] ?? false;
        $padding = $settings["padding"] ?? "16px";
        $borderRadius = $settings["border_radius"] ?? "12px";
        $boxShadow = $settings["box_shadow"] ?? true;

        // Secondary text color (slightly muted)
        $secondaryColor = $this->adjustColorOpacity($textColor, 0.7);

        // Wrapper styles based on player style
        $wrapperStyles = [
            "background-color: " . esc_attr($backgroundColor),
            "border-radius: " . esc_attr($borderRadius),
            "padding: " . esc_attr($padding),
            "color: " . esc_attr($textColor)
        ];

        if ($boxShadow) {
            $wrapperStyles[] = "box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3)";
        }

        // Build HTML based on player style
        $html = sprintf(
            '<div id="%s" class="tb4-audio tb4-audio--self-hosted tb4-audio--%s" %s style="%s">',
            esc_attr($moduleId),
            esc_attr($playerStyle),
            implode(' ', $dataAttrs),
            implode('; ', $containerStyles)
        );

        $html .= sprintf('<div class="tb4-audio__wrapper" style="%s">', implode('; ', $wrapperStyles));

        // Card and Standard style layout
        if ($playerStyle === "card" || $playerStyle === "standard") {
            // Top section with cover and info
            if ($playerStyle === "card" && ($showCover || $showTitle || $showArtist)) {
                $html .= '<div class="tb4-audio__header" style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px;">';

                // Cover art
                if ($showCover && !empty($coverImage)) {
                    $html .= sprintf(
                        '<div class="tb4-audio__cover" style="flex-shrink: 0; width: %s; height: %s;">
                            <img src="%s" alt="%s" style="width: 100%%; height: 100%%; object-fit: cover; border-radius: %s;">
                        </div>',
                        esc_attr($coverSize),
                        esc_attr($coverSize),
                        esc_attr($coverImage),
                        esc_attr($title),
                        esc_attr($coverBorderRadius)
                    );
                } elseif ($showCover) {
                    // Placeholder cover
                    $html .= sprintf(
                        '<div class="tb4-audio__cover tb4-audio__cover--placeholder" style="flex-shrink: 0; width: %s; height: %s; background: linear-gradient(135deg, %s, %s); border-radius: %s; display: flex; align-items: center; justify-content: center;">
                            %s
                        </div>',
                        esc_attr($coverSize),
                        esc_attr($coverSize),
                        esc_attr($accentColor),
                        esc_attr($this->adjustColorLightness($accentColor, -20)),
                        esc_attr($coverBorderRadius),
                        $this->renderMusicIcon($textColor)
                    );
                }

                // Track info
                $html .= '<div class="tb4-audio__info" style="flex: 1; min-width: 0;">';

                if ($showTitle) {
                    $html .= sprintf(
                        '<div class="tb4-audio__title" style="font-weight: 600; font-size: 16px; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">%s</div>',
                        esc_html($title)
                    );
                }

                if ($showArtist && !empty($artist)) {
                    $html .= sprintf(
                        '<div class="tb4-audio__artist" style="font-size: 14px; color: %s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">%s</div>',
                        esc_attr($secondaryColor),
                        esc_html($artist)
                    );
                }

                if (!empty($album)) {
                    $html .= sprintf(
                        '<div class="tb4-audio__album" style="font-size: 12px; color: %s; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">%s</div>',
                        esc_attr($secondaryColor),
                        esc_html($album)
                    );
                }

                $html .= '</div>'; // Close info
                $html .= '</div>'; // Close header
            }

            // Controls section
            $html .= '<div class="tb4-audio__controls" style="display: flex; align-items: center; gap: 12px;">';

            // Play/Pause button
            $html .= sprintf(
                '<button type="button" class="tb4-audio__play-btn" aria-label="Play" style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%%; background: %s; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.2s, opacity 0.2s;">
                    %s%s
                </button>',
                esc_attr($accentColor),
                $this->renderPlayButton($textColor),
                $this->renderPauseButton($textColor)
            );

            // Progress section
            $html .= '<div class="tb4-audio__progress-container" style="flex: 1; display: flex; align-items: center; gap: 8px;">';

            // Current time
            if ($showDuration) {
                $html .= '<span class="tb4-audio__time-current" style="font-size: 12px; min-width: 40px; color: ' . esc_attr($secondaryColor) . ';">0:00</span>';
            }

            // Progress bar
            $html .= sprintf(
                '<div class="tb4-audio__progress" style="flex: 1; height: 6px; background: rgba(255,255,255,0.2); border-radius: 3px; cursor: pointer; position: relative;">
                    <div class="tb4-audio__progress-bar" style="height: 100%%; width: 0%%; background: %s; border-radius: 3px; transition: width 0.1s;"></div>
                    <div class="tb4-audio__progress-handle" style="position: absolute; top: 50%%; transform: translate(-50%%, -50%%); width: 12px; height: 12px; background: %s; border-radius: 50%%; left: 0%%; opacity: 0; transition: opacity 0.2s;"></div>
                </div>',
                esc_attr($accentColor),
                esc_attr($textColor)
            );

            // Duration
            if ($showDuration) {
                $html .= '<span class="tb4-audio__time-duration" style="font-size: 12px; min-width: 40px; color: ' . esc_attr($secondaryColor) . ';">0:00</span>';
            }

            $html .= '</div>'; // Close progress container

            // Volume control
            $html .= sprintf(
                '<div class="tb4-audio__volume" style="display: flex; align-items: center; gap: 6px;">
                    <button type="button" class="tb4-audio__volume-btn" aria-label="Toggle mute" style="background: none; border: none; cursor: pointer; padding: 4px; display: flex;">
                        %s
                    </button>
                    <input type="range" class="tb4-audio__volume-slider" min="0" max="1" step="0.1" value="1" style="width: 60px; accent-color: %s;">
                </div>',
                $this->renderVolumeIcon($textColor),
                esc_attr($accentColor)
            );

            // Download button
            if ($showDownload) {
                $html .= sprintf(
                    '<a href="%s" download class="tb4-audio__download-btn" aria-label="Download" style="background: none; border: none; cursor: pointer; padding: 4px; display: flex; text-decoration: none;">
                        %s
                    </a>',
                    esc_attr($audioUrl),
                    $this->renderDownloadIcon($textColor)
                );
            }

            $html .= '</div>'; // Close controls

        } elseif ($playerStyle === "minimal") {
            // Minimal style - just a thin progress bar
            $html .= '<div class="tb4-audio__minimal" style="display: flex; align-items: center; gap: 12px;">';

            // Play button
            $html .= sprintf(
                '<button type="button" class="tb4-audio__play-btn" aria-label="Play" style="flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%%; background: %s; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    %s%s
                </button>',
                esc_attr($accentColor),
                $this->renderPlayButton($textColor),
                $this->renderPauseButton($textColor)
            );

            // Title (if shown)
            if ($showTitle) {
                $html .= sprintf(
                    '<span class="tb4-audio__title" style="font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">%s</span>',
                    esc_html($title)
                );
            }

            // Progress bar
            $html .= sprintf(
                '<div class="tb4-audio__progress" style="flex: 1; height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; cursor: pointer; position: relative;">
                    <div class="tb4-audio__progress-bar" style="height: 100%%; width: 0%%; background: %s; border-radius: 2px;"></div>
                </div>',
                esc_attr($accentColor)
            );

            // Duration
            if ($showDuration) {
                $html .= '<span class="tb4-audio__time-duration" style="font-size: 11px; color: ' . esc_attr($secondaryColor) . ';">0:00</span>';
            }

            $html .= '</div>'; // Close minimal
        }

        // Hidden audio element
        $audioAttrs = ['preload="metadata"'];
        if ($autoplay) {
            $audioAttrs[] = 'autoplay';
        }
        if ($loop) {
            $audioAttrs[] = 'loop';
        }
        if ($muted) {
            $audioAttrs[] = 'muted';
        }

        $html .= sprintf(
            '<audio class="tb4-audio__element" %s style="display: none;">
                <source src="%s" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>',
            implode(' ', $audioAttrs),
            esc_attr($audioUrl)
        );

        $html .= '</div>'; // Close wrapper

        // Description
        if (!empty($description)) {
            $html .= sprintf(
                '<p class="tb4-audio__description" style="margin-top: 12px; font-size: 14px; color: %s; line-height: 1.5;">%s</p>',
                esc_attr($secondaryColor),
                esc_html($description)
            );
        }

        $html .= '</div>'; // Close main container

        return $html;
    }

    /**
     * Render music note icon for placeholder
     */
    private function renderMusicIcon(string $color): string
    {
        return <<<SVG
<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="{$color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M9 18V5l12-2v13"></path>
    <circle cx="6" cy="18" r="3"></circle>
    <circle cx="18" cy="16" r="3"></circle>
</svg>
SVG;
    }

    /**
     * Adjust color opacity (simple implementation)
     */
    private function adjustColorOpacity(string $color, float $opacity): string
    {
        // If already rgba, adjust opacity
        if (strpos($color, 'rgba') === 0) {
            return preg_replace('/,\s*[\d.]+\)$/', ", {$opacity})", $color);
        }

        // If rgb, convert to rgba
        if (strpos($color, 'rgb') === 0) {
            return str_replace('rgb(', 'rgba(', rtrim($color, ')')) . ", {$opacity})";
        }

        // If hex, convert to rgba
        if (strpos($color, '#') === 0) {
            $hex = ltrim($color, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba({$r}, {$g}, {$b}, {$opacity})";
        }

        return $color;
    }

    /**
     * Adjust color lightness (simple implementation)
     */
    private function adjustColorLightness(string $color, int $amount): string
    {
        if (strpos($color, '#') !== 0) {
            return $color;
        }

        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $amount));
        $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $amount));
        $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $amount));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
