<?php
/**
 * Video Slider Item Module (Child)
 * Single video slide
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_VideoSliderItem extends JTB_Element
{
    public string $icon = 'video-slide';
    public string $category = 'media';
    public bool $is_child = true;

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = false;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'video_slider_item';
    }

    public function getName(): string
    {
        return 'Video Slide';
    }

    public function getFields(): array
    {
        return [
            'src' => [
                'label' => 'Video URL',
                'type' => 'text',
                'description' => 'YouTube, Vimeo URL or direct video file'
            ],
            'src_webm' => [
                'label' => 'WebM Fallback',
                'type' => 'upload',
                'accept' => 'video/webm'
            ],
            'image_src' => [
                'label' => 'Thumbnail Image',
                'type' => 'upload'
            ],
            'heading' => [
                'label' => 'Video Title',
                'type' => 'text'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $src = $attrs['src'] ?? '';
        $webm = $attrs['src_webm'] ?? '';
        $thumbnail = $attrs['image_src'] ?? '';
        $heading = $this->esc($attrs['heading'] ?? '');

        $html = '<div class="jtb-video-slider-item" data-src="' . $this->esc($src) . '" data-thumbnail="' . $this->esc($thumbnail) . '">';

        // Video container with thumbnail overlay
        $html .= '<div class="jtb-video-slide-wrap">';

        if (!empty($thumbnail)) {
            $html .= '<div class="jtb-video-thumbnail" style="background-image: url(' . $this->esc($thumbnail) . ')">';
            $html .= '<div class="jtb-video-play-overlay">▶</div>';
            $html .= '</div>';
        }

        // The actual video (hidden until clicked)
        $html .= '<div class="jtb-video-player-wrap" style="display: none;">';

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $src, $matches)) {
            $youtubeId = $matches[1];
            $html .= '<iframe src="https://www.youtube.com/embed/' . $youtubeId . '" frameborder="0" allowfullscreen></iframe>';
        } elseif (preg_match('/vimeo\.com\/(\d+)/', $src, $matches)) {
            $vimeoId = $matches[1];
            $html .= '<iframe src="https://player.vimeo.com/video/' . $vimeoId . '" frameborder="0" allowfullscreen></iframe>';
        } else {
            $html .= '<video controls>';
            $html .= '<source src="' . $this->esc($src) . '" type="video/mp4">';
            if (!empty($webm)) {
                $html .= '<source src="' . $this->esc($webm) . '" type="video/webm">';
            }
            $html .= '</video>';
        }

        $html .= '</div>';
        $html .= '</div>';

        if (!empty($heading)) {
            $html .= '<div class="jtb-video-slide-title">' . $heading . '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $css .= $selector . ' .jtb-video-slide-wrap { position: relative; padding-bottom: 56.25%; height: 0; }' . "\n";
        $css .= $selector . ' .jtb-video-thumbnail { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-size: cover; background-position: center; cursor: pointer; }' . "\n";
        $css .= $selector . ' .jtb-video-player-wrap { position: absolute; top: 0; left: 0; right: 0; bottom: 0; }' . "\n";
        $css .= $selector . ' .jtb-video-player-wrap iframe, ' . $selector . ' .jtb-video-player-wrap video { width: 100%; height: 100%; }' . "\n";

        return $css;
    }
}

JTB_Registry::register('video_slider_item', JTB_Module_VideoSliderItem::class);
