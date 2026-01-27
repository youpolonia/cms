<?php
namespace Core\TB4\Modules\Media;

require_once dirname(__DIR__) . "/module.php";

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Video Slider Module
 * Video carousel with YouTube/Vimeo/self-hosted support
 * Features thumbnails, play buttons, navigation arrows and dots
 */
class VideoSliderModule extends Module
{
    public function __construct()
    {
        $this->name = 'Video Slider';
        $this->slug = 'video_slider';
        $this->icon = 'film';
        $this->category = 'media';

        $this->elements = [
            'main' => '.tb4-video-slider',
            'track' => '.tb4-video-slider-track',
            'slide' => '.tb4-video-slide',
            'arrows' => '.tb4-video-slider-arrows',
            'dots' => '.tb4-video-slider-dots',
            'play_btn' => '.tb4-video-play-btn'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'video1_type' => [
                'label' => 'Video 1 Type',
                'type' => 'select',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo' => 'Vimeo',
                    'self' => 'Self Hosted'
                ],
                'default' => 'youtube'
            ],
            'video1_url' => [
                'label' => 'Video 1 URL/ID',
                'type' => 'text',
                'default' => ''
            ],
            'video1_title' => [
                'label' => 'Video 1 Title',
                'type' => 'text',
                'default' => 'Video Title'
            ],
            'video1_description' => [
                'label' => 'Video 1 Description',
                'type' => 'textarea',
                'default' => ''
            ],
            'video1_thumbnail' => [
                'label' => 'Video 1 Thumbnail URL',
                'type' => 'text',
                'default' => ''
            ],
            'video2_type' => [
                'label' => 'Video 2 Type',
                'type' => 'select',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo' => 'Vimeo',
                    'self' => 'Self Hosted'
                ],
                'default' => 'youtube'
            ],
            'video2_url' => [
                'label' => 'Video 2 URL/ID',
                'type' => 'text',
                'default' => ''
            ],
            'video2_title' => [
                'label' => 'Video 2 Title',
                'type' => 'text',
                'default' => ''
            ],
            'video2_description' => [
                'label' => 'Video 2 Description',
                'type' => 'textarea',
                'default' => ''
            ],
            'video2_thumbnail' => [
                'label' => 'Video 2 Thumbnail URL',
                'type' => 'text',
                'default' => ''
            ],
            'video3_type' => [
                'label' => 'Video 3 Type',
                'type' => 'select',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo' => 'Vimeo',
                    'self' => 'Self Hosted'
                ],
                'default' => 'youtube'
            ],
            'video3_url' => [
                'label' => 'Video 3 URL/ID',
                'type' => 'text',
                'default' => ''
            ],
            'video3_title' => [
                'label' => 'Video 3 Title',
                'type' => 'text',
                'default' => ''
            ],
            'video3_description' => [
                'label' => 'Video 3 Description',
                'type' => 'textarea',
                'default' => ''
            ],
            'video3_thumbnail' => [
                'label' => 'Video 3 Thumbnail URL',
                'type' => 'text',
                'default' => ''
            ],
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_dots' => [
                'label' => 'Show Dots',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_description' => [
                'label' => 'Show Description',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'slider_height' => [
                'label' => 'Slider Height',
                'type' => 'text',
                'default' => '450px'
            ],
            'aspect_ratio' => [
                'label' => 'Video Aspect Ratio',
                'type' => 'select',
                'options' => [
                    '16:9' => '16:9',
                    '4:3' => '4:3',
                    '21:9' => '21:9'
                ],
                'default' => '16:9'
            ],
            'background_color' => [
                'label' => 'Background Color',
                'type' => 'color',
                'default' => '#000000'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '18px'
            ],
            'description_color' => [
                'label' => 'Description Color',
                'type' => 'color',
                'default' => '#9ca3af'
            ],
            'arrow_color' => [
                'label' => 'Arrow Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'arrow_bg_color' => [
                'label' => 'Arrow Background',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.5)'
            ],
            'dot_color' => [
                'label' => 'Dot Color',
                'type' => 'color',
                'default' => 'rgba(255,255,255,0.5)'
            ],
            'dot_active_color' => [
                'label' => 'Active Dot Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'play_icon_color' => [
                'label' => 'Play Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'play_icon_bg' => [
                'label' => 'Play Icon Background',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.6)'
            ],
            'border_radius' => [
                'label' => 'Border Radius',
                'type' => 'text',
                'default' => '12px'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Extract YouTube video ID from URL
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

        // If no pattern matches, assume it's just the ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        return null;
    }

    /**
     * Extract Vimeo video ID from URL
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

        // If no pattern matches, assume it's just the ID
        if (preg_match('/^\d+$/', $url)) {
            return $url;
        }

        return null;
    }

    /**
     * Get YouTube thumbnail URL
     */
    private function getYouTubeThumbnail(string $videoId): string
    {
        return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
    }

    /**
     * Get aspect ratio padding percentage
     */
    private function getAspectRatioPadding(string $ratio): string
    {
        return match($ratio) {
            '16:9' => '56.25%',
            '4:3' => '75%',
            '21:9' => '42.86%',
            default => '56.25%'
        };
    }

    /**
     * Build YouTube embed URL
     */
    private function buildYouTubeEmbedUrl(string $videoId): string
    {
        return "https://www.youtube-nocookie.com/embed/{$videoId}?rel=0&modestbranding=1";
    }

    /**
     * Build Vimeo embed URL
     */
    private function buildVimeoEmbedUrl(string $videoId): string
    {
        return "https://player.vimeo.com/video/{$videoId}?dnt=1";
    }

    public function render(array $attrs): string
    {
        // Collect videos from individual fields
        $videos = [];
        for ($i = 1; $i <= 3; $i++) {
            $type = $attrs['video' . $i . '_type'] ?? 'youtube';
            $url = trim($attrs['video' . $i . '_url'] ?? '');
            $title = trim($attrs['video' . $i . '_title'] ?? '');
            $description = trim($attrs['video' . $i . '_description'] ?? '');
            $thumbnail = trim($attrs['video' . $i . '_thumbnail'] ?? '');

            if (!empty($url) || !empty($title)) {
                // Extract video ID and determine thumbnail
                $videoId = null;
                $embedUrl = '';
                $thumbUrl = $thumbnail;

                if ($type === 'youtube' && !empty($url)) {
                    $videoId = $this->extractYouTubeId($url);
                    if ($videoId) {
                        $embedUrl = $this->buildYouTubeEmbedUrl($videoId);
                        if (empty($thumbUrl)) {
                            $thumbUrl = $this->getYouTubeThumbnail($videoId);
                        }
                    }
                } elseif ($type === 'vimeo' && !empty($url)) {
                    $videoId = $this->extractVimeoId($url);
                    if ($videoId) {
                        $embedUrl = $this->buildVimeoEmbedUrl($videoId);
                    }
                } elseif ($type === 'self' && !empty($url)) {
                    $embedUrl = $url;
                }

                $videos[] = [
                    'type' => $type,
                    'url' => $url,
                    'videoId' => $videoId,
                    'embedUrl' => $embedUrl,
                    'title' => $title,
                    'description' => $description,
                    'thumbnail' => $thumbUrl
                ];
            }
        }

        // If no videos, use defaults
        if (empty($videos)) {
            $videos = [
                [
                    'type' => 'youtube',
                    'url' => 'dQw4w9WgXcQ',
                    'videoId' => 'dQw4w9WgXcQ',
                    'embedUrl' => $this->buildYouTubeEmbedUrl('dQw4w9WgXcQ'),
                    'title' => 'Sample Video 1',
                    'description' => 'Video description here',
                    'thumbnail' => $this->getYouTubeThumbnail('dQw4w9WgXcQ')
                ],
                [
                    'type' => 'youtube',
                    'url' => 'dQw4w9WgXcQ',
                    'videoId' => 'dQw4w9WgXcQ',
                    'embedUrl' => $this->buildYouTubeEmbedUrl('dQw4w9WgXcQ'),
                    'title' => 'Sample Video 2',
                    'description' => '',
                    'thumbnail' => $this->getYouTubeThumbnail('dQw4w9WgXcQ')
                ]
            ];
        }

        // Settings
        $showArrows = ($attrs['show_arrows'] ?? 'yes') !== 'no';
        $showDots = ($attrs['show_dots'] ?? 'yes') !== 'no';
        $showTitle = ($attrs['show_title'] ?? 'yes') !== 'no';
        $showDescription = ($attrs['show_description'] ?? 'no') === 'yes';

        // Design settings
        $sliderHeight = $attrs['slider_height'] ?? '450px';
        $aspectRatio = $attrs['aspect_ratio'] ?? '16:9';
        $backgroundColor = $attrs['background_color'] ?? '#000000';
        $titleColor = $attrs['title_color'] ?? '#ffffff';
        $titleFontSize = $attrs['title_font_size'] ?? '18px';
        $descriptionColor = $attrs['description_color'] ?? '#9ca3af';
        $arrowColor = $attrs['arrow_color'] ?? '#ffffff';
        $arrowBgColor = $attrs['arrow_bg_color'] ?? 'rgba(0,0,0,0.5)';
        $dotColor = $attrs['dot_color'] ?? 'rgba(255,255,255,0.5)';
        $dotActiveColor = $attrs['dot_active_color'] ?? '#ffffff';
        $playIconColor = $attrs['play_icon_color'] ?? '#ffffff';
        $playIconBg = $attrs['play_icon_bg'] ?? 'rgba(0,0,0,0.6)';
        $borderRadius = $attrs['border_radius'] ?? '12px';

        $paddingTop = $this->getAspectRatioPadding($aspectRatio);

        // Generate unique ID
        $moduleId = 'tb4-video-slider-' . uniqid();

        // Build HTML
        $html = '<div class="tb4-video-slider" id="' . esc_attr($moduleId) . '" data-current="0" style="position:relative;overflow:hidden;border-radius:' . esc_attr($borderRadius) . ';background:' . esc_attr($backgroundColor) . ';">';

        // Track with slides
        $html .= '<div class="tb4-video-slider-track" style="display:flex;transition:transform 0.5s ease;">';

        foreach ($videos as $index => $video) {
            $thumbBg = !empty($video['thumbnail'])
                ? 'url(' . esc_attr($video['thumbnail']) . ')'
                : 'linear-gradient(135deg, #374151 0%, #1f2937 100%)';

            $html .= '<div class="tb4-video-slide" data-index="' . esc_attr($index) . '" data-embed-url="' . esc_attr($video['embedUrl']) . '" data-video-type="' . esc_attr($video['type']) . '" style="min-width:100%;">';

            // Video thumbnail container
            $html .= '<div class="tb4-video-slide-inner" style="position:relative;width:100%;padding-top:' . esc_attr($paddingTop) . ';background:' . $thumbBg . ';background-size:cover;background-position:center;">';

            // Play button
            $html .= '<div class="tb4-video-play-btn" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:72px;height:72px;border-radius:50%;background:' . esc_attr($playIconBg) . ';display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform 0.2s,background 0.2s;">';
            $html .= '<div style="width:0;height:0;border-style:solid;border-width:12px 0 12px 20px;border-color:transparent transparent transparent ' . esc_attr($playIconColor) . ';margin-left:4px;"></div>';
            $html .= '</div>';

            $html .= '</div>';

            // Info section
            if ($showTitle || $showDescription) {
                $html .= '<div class="tb4-video-slide-info" style="padding:16px;background:#111;">';

                if ($showTitle && !empty($video['title'])) {
                    $html .= '<h4 class="tb4-video-slide-title" style="font-size:' . esc_attr($titleFontSize) . ';font-weight:600;color:' . esc_attr($titleColor) . ';margin:0 0 4px 0;">' . esc_html($video['title']) . '</h4>';
                }

                if ($showDescription && !empty($video['description'])) {
                    $html .= '<p class="tb4-video-slide-desc" style="font-size:14px;color:' . esc_attr($descriptionColor) . ';margin:0;">' . esc_html($video['description']) . '</p>';
                }

                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        // Arrows
        if ($showArrows && count($videos) > 1) {
            $arrowTop = ($showTitle || $showDescription) ? 'calc(50% - 40px)' : '50%';
            $html .= '<div class="tb4-video-slider-arrows" style="position:absolute;top:' . $arrowTop . ';left:0;right:0;transform:translateY(-50%);display:flex;justify-content:space-between;padding:0 16px;pointer-events:none;z-index:10;">';
            $html .= '<button class="tb4-video-slider-arrow tb4-video-slider-prev" style="width:44px;height:44px;border-radius:50%;background:' . esc_attr($arrowBgColor) . ';color:' . esc_attr($arrowColor) . ';border:none;cursor:pointer;pointer-events:auto;font-size:20px;">&lsaquo;</button>';
            $html .= '<button class="tb4-video-slider-arrow tb4-video-slider-next" style="width:44px;height:44px;border-radius:50%;background:' . esc_attr($arrowBgColor) . ';color:' . esc_attr($arrowColor) . ';border:none;cursor:pointer;pointer-events:auto;font-size:20px;">&rsaquo;</button>';
            $html .= '</div>';
        }

        // Dots
        if ($showDots && count($videos) > 1) {
            $dotsBottom = ($showTitle || $showDescription) ? '80px' : '20px';
            $html .= '<div class="tb4-video-slider-dots" style="position:absolute;bottom:' . $dotsBottom . ';left:0;right:0;display:flex;justify-content:center;gap:8px;z-index:10;">';
            foreach ($videos as $index => $video) {
                $dotBg = $index === 0 ? $dotActiveColor : $dotColor;
                $html .= '<button class="tb4-video-slider-dot' . ($index === 0 ? ' active' : '') . '" data-slide="' . esc_attr($index) . '" style="width:10px;height:10px;border-radius:50%;background:' . esc_attr($dotBg) . ';border:none;cursor:pointer;"></button>';
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
