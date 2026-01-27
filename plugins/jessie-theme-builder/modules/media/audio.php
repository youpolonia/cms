<?php
/**
 * Audio Module
 * HTML5 audio player
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Audio extends JTB_Element
{
    public string $icon = 'audio';
    public string $category = 'media';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = true;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    public function getSlug(): string
    {
        return 'audio';
    }

    public function getName(): string
    {
        return 'Audio';
    }

    public function getFields(): array
    {
        return [
            'audio' => [
                'label' => 'Audio File',
                'type' => 'upload',
                'accept' => 'audio/*'
            ],
            'title' => [
                'label' => 'Title',
                'type' => 'text'
            ],
            'artist' => [
                'label' => 'Artist',
                'type' => 'text'
            ],
            'album' => [
                'label' => 'Album',
                'type' => 'text'
            ],
            'image_url' => [
                'label' => 'Album Art',
                'type' => 'upload'
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
            'player_bg_color' => [
                'label' => 'Player Background',
                'type' => 'color',
                'default' => '#222222'
            ],
            'player_text_color' => [
                'label' => 'Player Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'progress_color' => [
                'label' => 'Progress Bar Color',
                'type' => 'color',
                'default' => '#2ea3f2'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        $audioUrl = $attrs['audio'] ?? '';
        $title = $this->esc($attrs['title'] ?? '');
        $artist = $this->esc($attrs['artist'] ?? '');
        $album = $this->esc($attrs['album'] ?? '');
        $imageUrl = $attrs['image_url'] ?? '';
        $autoplay = !empty($attrs['autoplay']) ? ' autoplay' : '';
        $loop = !empty($attrs['loop']) ? ' loop' : '';

        $innerHtml = '<div class="jtb-audio-container">';

        // Album art
        if (!empty($imageUrl)) {
            $innerHtml .= '<div class="jtb-audio-artwork">';
            $innerHtml .= '<img src="' . $this->esc($imageUrl) . '" alt="' . ($title ?: 'Album Art') . '" />';
            $innerHtml .= '</div>';
        }

        // Meta info
        if (!empty($title) || !empty($artist) || !empty($album)) {
            $innerHtml .= '<div class="jtb-audio-meta">';
            if (!empty($title)) {
                $innerHtml .= '<div class="jtb-audio-title">' . $title . '</div>';
            }
            if (!empty($artist)) {
                $innerHtml .= '<div class="jtb-audio-artist">' . $artist . '</div>';
            }
            if (!empty($album)) {
                $innerHtml .= '<div class="jtb-audio-album">' . $album . '</div>';
            }
            $innerHtml .= '</div>';
        }

        // Audio element
        if (!empty($audioUrl)) {
            $innerHtml .= '<audio class="jtb-audio-player" controls' . $autoplay . $loop . '>';
            $innerHtml .= '<source src="' . $this->esc($audioUrl) . '" type="audio/mpeg">';
            $innerHtml .= 'Your browser does not support the audio element.';
            $innerHtml .= '</audio>';
        } else {
            $innerHtml .= '<p class="jtb-audio-placeholder">No audio file selected</p>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        $bgColor = $attrs['player_bg_color'] ?? '#222222';
        $textColor = $attrs['player_text_color'] ?? '#ffffff';
        $progressColor = $attrs['progress_color'] ?? '#2ea3f2';

        $css .= $selector . ' .jtb-audio-container { ';
        $css .= 'background-color: ' . $bgColor . '; ';
        $css .= 'color: ' . $textColor . '; ';
        $css .= 'padding: 20px; ';
        $css .= 'border-radius: 8px; ';
        $css .= '}' . "\n";

        // Artwork
        $css .= $selector . ' .jtb-audio-artwork { ';
        $css .= 'margin-bottom: 15px; ';
        $css .= 'text-align: center; ';
        $css .= '}' . "\n";

        $css .= $selector . ' .jtb-audio-artwork img { ';
        $css .= 'max-width: 200px; ';
        $css .= 'border-radius: 8px; ';
        $css .= '}' . "\n";

        // Meta
        $css .= $selector . ' .jtb-audio-meta { margin-bottom: 15px; text-align: center; }' . "\n";
        $css .= $selector . ' .jtb-audio-title { font-size: 18px; font-weight: bold; }' . "\n";
        $css .= $selector . ' .jtb-audio-artist { font-size: 14px; opacity: 0.8; }' . "\n";
        $css .= $selector . ' .jtb-audio-album { font-size: 12px; opacity: 0.6; }' . "\n";

        // Audio player
        $css .= $selector . ' .jtb-audio-player { width: 100%; }' . "\n";

        // Custom audio styling
        $css .= $selector . ' .jtb-audio-player::-webkit-media-controls-panel { background-color: ' . $bgColor . '; }' . "\n";

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('audio', JTB_Module_Audio::class);
