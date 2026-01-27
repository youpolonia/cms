<?php
/**
 * Video Slider Module (Parent)
 * Video carousel slider
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_VideoSlider extends JTB_Element
{
    public string $icon = 'video-slider';
    public string $category = 'media';
    public string $child_slug = 'video_slider_item';

    public bool $use_typography = false;
    public bool $use_background = false;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'video_slider';
    }

    public function getName(): string
    {
        return 'Video Slider';
    }

    public function getFields(): array
    {
        return [
            'show_arrows' => [
                'label' => 'Show Arrows',
                'type' => 'toggle',
                'default' => true
            ],
            'show_thumbnails' => [
                'label' => 'Show Thumbnails',
                'type' => 'toggle',
                'default' => true
            ],
            'arrow_color' => [
                'label' => 'Arrow Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'thumbnail_width' => [
                'label' => 'Thumbnail Width',
                'type' => 'range',
                'min' => 50,
                'max' => 200,
                'unit' => 'px',
                'default' => 100
            ],
            'play_icon_color' => [
                'label' => 'Play Icon Color',
                'type' => 'color',
                'default' => '#ffffff'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $showArrows = $attrs['show_arrows'] ?? true;
        $showThumbnails = $attrs['show_thumbnails'] ?? true;

        $innerHtml = '<div class="jtb-video-slider-container">';
        $innerHtml .= '<div class="jtb-video-slider-main">';
        $innerHtml .= '<div class="jtb-video-slider-track">';
        $innerHtml .= $content;
        $innerHtml .= '</div>';

        if ($showArrows) {
            $innerHtml .= '<button class="jtb-video-slider-arrow jtb-video-slider-prev">‹</button>';
            $innerHtml .= '<button class="jtb-video-slider-arrow jtb-video-slider-next">›</button>';
        }

        $innerHtml .= '</div>';

        if ($showThumbnails) {
            $innerHtml .= '<div class="jtb-video-slider-thumbnails"></div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Container
        $css .= $selector . ' .jtb-video-slider-main { position: relative; }' . "\n";
        $css .= $selector . ' .jtb-video-slider-track { overflow: hidden; }' . "\n";

        // Arrows
        $arrowColor = $attrs['arrow_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-video-slider-arrow { ';
        $css .= 'position: absolute; top: 50%; transform: translateY(-50%); ';
        $css .= 'background: rgba(0,0,0,0.5); color: ' . $arrowColor . '; ';
        $css .= 'border: none; width: 50px; height: 50px; font-size: 30px; cursor: pointer; ';
        $css .= 'z-index: 10; border-radius: 50%; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-video-slider-prev { left: 20px; }' . "\n";
        $css .= $selector . ' .jtb-video-slider-next { right: 20px; }' . "\n";

        // Thumbnails
        $thumbWidth = $attrs['thumbnail_width'] ?? 100;
        $css .= $selector . ' .jtb-video-slider-thumbnails { display: flex; gap: 10px; margin-top: 15px; justify-content: center; }' . "\n";
        $css .= $selector . ' .jtb-video-slider-thumb { width: ' . $thumbWidth . 'px; cursor: pointer; opacity: 0.6; transition: opacity 0.3s; }' . "\n";
        $css .= $selector . ' .jtb-video-slider-thumb.jtb-active { opacity: 1; }' . "\n";

        // Play icon
        $playColor = $attrs['play_icon_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-video-play-overlay { ';
        $css .= 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); ';
        $css .= 'color: ' . $playColor . '; font-size: 60px; ';
        $css .= '}' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('video_slider', JTB_Module_VideoSlider::class);
