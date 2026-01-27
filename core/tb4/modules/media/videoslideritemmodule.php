<?php
namespace Core\TB4\Modules\Media;

require_once dirname(__DIR__) . '/childmodule.php';

use Core\TB4\Modules\ChildModule;

/**
 * TB 4.0 Video Slider Item Module
 * Child module for Video Slider - represents a single video slide
 */
class VideoSliderItemModule extends ChildModule
{
    protected string $name = 'Video Slide';
    protected string $slug = 'video_slider_item';
    protected string $icon = 'film';
    protected string $category = 'media';
    protected string $type = 'child';
    protected ?string $parent_slug = 'video_slider';
    protected ?string $child_title_var = 'video_title';

    public function get_content_fields(): array
    {
        return [
            'video_source' => [
                'label' => 'Video Source',
                'type' => 'select',
                'options' => [
                    'youtube' => 'YouTube',
                    'vimeo' => 'Vimeo',
                    'self' => 'Self Hosted'
                ],
                'default' => 'youtube'
            ],
            'video_url' => [
                'label' => 'Video URL',
                'type' => 'text',
                'default' => ''
            ],
            'video_title' => [
                'label' => 'Video Title',
                'type' => 'text',
                'default' => 'Video Title'
            ],
            'thumbnail' => [
                'label' => 'Custom Thumbnail',
                'type' => 'image',
                'default' => ''
            ],
            'autoplay' => [
                'label' => 'Autoplay',
                'type' => 'toggle',
                'default' => 'no'
            ],
            'muted' => [
                'label' => 'Muted',
                'type' => 'toggle',
                'default' => 'no'
            ],
            'loop' => [
                'label' => 'Loop',
                'type' => 'toggle',
                'default' => 'no'
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $source = $data['content']['video_source'] ?? $data['video_source'] ?? 'youtube';
        $url = $data['content']['video_url'] ?? $data['video_url'] ?? '';
        $title = $data['content']['video_title'] ?? $data['video_title'] ?? 'Video';
        $thumbnail = $data['content']['thumbnail'] ?? $data['thumbnail'] ?? '';

        $bgStyle = $thumbnail ? 'background-image:url(' . htmlspecialchars($thumbnail, ENT_QUOTES, 'UTF-8') . ');background-size:cover;background-position:center;' : 'background:linear-gradient(135deg,#1f2937,#374151);';

        $html = '<div class="tb4-video-slide" style="position:relative;aspect-ratio:16/9;' . $bgStyle . 'border-radius:8px;display:flex;align-items:center;justify-content:center;">';
        $html .= '<div style="text-align:center;color:white;">';
        $html .= '<div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;backdrop-filter:blur(4px);">';
        $html .= '<svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>';
        $html .= '</div>';
        $html .= '<div style="font-weight:500;text-shadow:0 1px 2px rgba(0,0,0,0.5);">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '<div style="font-size:12px;opacity:0.7;margin-top:4px;text-transform:uppercase;">' . htmlspecialchars($source, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
