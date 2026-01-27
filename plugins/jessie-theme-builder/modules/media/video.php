<?php
/**
 * Video Module
 * HTML5/YouTube/Vimeo video player
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Video extends JTB_Element
{
    public string $icon = 'video';
    public string $category = 'media';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    public function getSlug(): string
    {
        return 'video';
    }

    public function getName(): string
    {
        return 'Video';
    }

    public function getFields(): array
    {
        return [
            'src' => [
                'label' => 'Video URL/File',
                'type' => 'text',
                'description' => 'YouTube, Vimeo URL or direct video file path'
            ],
            'src_webm' => [
                'label' => 'WebM Fallback',
                'type' => 'upload',
                'accept' => 'video/webm'
            ],
            'image_src' => [
                'label' => 'Poster/Thumbnail',
                'type' => 'upload'
            ],
            'play_icon_color' => [
                'label' => 'Play Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'autoplay' => [
                'label' => 'Autoplay',
                'type' => 'toggle',
                'default' => false
            ],
            'loop' => [
                'label' => 'Loop',
                'type' => 'toggle',
                'default' => false
            ],
            'muted' => [
                'label' => 'Muted',
                'type' => 'toggle',
                'default' => false
            ],
            'controls' => [
                'label' => 'Show Controls',
                'type' => 'toggle',
                'default' => true
            ],
            'aspect_ratio' => [
                'label' => 'Aspect Ratio',
                'type' => 'select',
                'options' => [
                    '16:9' => '16:9 (Widescreen)',
                    '4:3' => '4:3 (Standard)',
                    '21:9' => '21:9 (Cinematic)',
                    '1:1' => '1:1 (Square)',
                    '9:16' => '9:16 (Portrait)'
                ],
                'default' => '16:9'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $src = $attrs['src'] ?? '';
        $webm = $attrs['src_webm'] ?? '';
        $poster = $attrs['image_src'] ?? '';
        $autoplay = !empty($attrs['autoplay']);
        $loop = !empty($attrs['loop']);
        $muted = !empty($attrs['muted']);
        $controls = $attrs['controls'] ?? true;
        $aspectRatio = $attrs['aspect_ratio'] ?? '16:9';

        $innerHtml = '<div class="jtb-video-container jtb-aspect-' . str_replace(':', '-', $aspectRatio) . '">';

        if (empty($src)) {
            $innerHtml .= '<div class="jtb-video-placeholder">No video source specified</div>';
        } else {
            // Check if YouTube
            if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $src, $matches)) {
                $youtubeId = $matches[1];
                $params = [];
                if ($autoplay) $params[] = 'autoplay=1';
                if ($loop) $params[] = 'loop=1&playlist=' . $youtubeId;
                if ($muted) $params[] = 'mute=1';
                if (!$controls) $params[] = 'controls=0';

                $queryString = !empty($params) ? '?' . implode('&', $params) : '';

                $innerHtml .= '<iframe class="jtb-video-iframe" ';
                $innerHtml .= 'src="https://www.youtube.com/embed/' . $youtubeId . $queryString . '" ';
                $innerHtml .= 'frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
            }
            // Check if Vimeo
            elseif (preg_match('/vimeo\.com\/(\d+)/', $src, $matches)) {
                $vimeoId = $matches[1];
                $params = [];
                if ($autoplay) $params[] = 'autoplay=1';
                if ($loop) $params[] = 'loop=1';
                if ($muted) $params[] = 'muted=1';

                $queryString = !empty($params) ? '?' . implode('&', $params) : '';

                $innerHtml .= '<iframe class="jtb-video-iframe" ';
                $innerHtml .= 'src="https://player.vimeo.com/video/' . $vimeoId . $queryString . '" ';
                $innerHtml .= 'frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
            }
            // HTML5 video
            else {
                $videoAttrs = [];
                if ($controls) $videoAttrs[] = 'controls';
                if ($autoplay) $videoAttrs[] = 'autoplay';
                if ($loop) $videoAttrs[] = 'loop';
                if ($muted) $videoAttrs[] = 'muted';
                if (!empty($poster)) $videoAttrs[] = 'poster="' . $this->esc($poster) . '"';

                $innerHtml .= '<video class="jtb-video-player" ' . implode(' ', $videoAttrs) . '>';
                $innerHtml .= '<source src="' . $this->esc($src) . '" type="video/mp4">';
                if (!empty($webm)) {
                    $innerHtml .= '<source src="' . $this->esc($webm) . '" type="video/webm">';
                }
                $innerHtml .= 'Your browser does not support the video tag.';
                $innerHtml .= '</video>';
            }
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Aspect ratio container
        $css .= $selector . ' .jtb-video-container { position: relative; width: 100%; overflow: hidden; }' . "\n";

        // Aspect ratios
        $css .= $selector . ' .jtb-aspect-16-9 { padding-bottom: 56.25%; }' . "\n";
        $css .= $selector . ' .jtb-aspect-4-3 { padding-bottom: 75%; }' . "\n";
        $css .= $selector . ' .jtb-aspect-21-9 { padding-bottom: 42.86%; }' . "\n";
        $css .= $selector . ' .jtb-aspect-1-1 { padding-bottom: 100%; }' . "\n";
        $css .= $selector . ' .jtb-aspect-9-16 { padding-bottom: 177.78%; }' . "\n";

        // Video/iframe positioning
        $css .= $selector . ' .jtb-video-iframe, ' . $selector . ' .jtb-video-player { ';
        $css .= 'position: absolute; ';
        $css .= 'top: 0; ';
        $css .= 'left: 0; ';
        $css .= 'width: 100%; ';
        $css .= 'height: 100%; ';
        $css .= '}' . "\n";

        // Placeholder
        $css .= $selector . ' .jtb-video-placeholder { ';
        $css .= 'position: absolute; ';
        $css .= 'top: 50%; ';
        $css .= 'left: 50%; ';
        $css .= 'transform: translate(-50%, -50%); ';
        $css .= 'color: #999; ';
        $css .= '}' . "\n";

        // Play icon overlay
        if (!empty($attrs['play_icon_color'])) {
            $css .= $selector . ' .jtb-video-play-icon { color: ' . $attrs['play_icon_color'] . '; }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('video', JTB_Module_Video::class);
