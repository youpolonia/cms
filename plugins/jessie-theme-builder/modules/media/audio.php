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

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'audio';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'player_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-audio-container'
        ],
        'player_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-audio-container'
        ],
        'progress_color' => [
            'property' => '--audio-progress-color',
            'selector' => '.jtb-audio-container'
        ]
    ];

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
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

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

    /**
     * Generate CSS for Audio module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Container base styles
        $css .= $selector . ' .jtb-audio-container { padding: 20px; border-radius: 8px; }' . "\n";

        // Artwork
        $css .= $selector . ' .jtb-audio-artwork { margin-bottom: 15px; text-align: center; }' . "\n";
        $css .= $selector . ' .jtb-audio-artwork img { max-width: 200px; border-radius: 8px; }' . "\n";

        // Meta
        $css .= $selector . ' .jtb-audio-meta { margin-bottom: 15px; text-align: center; }' . "\n";
        $css .= $selector . ' .jtb-audio-title { font-size: 18px; font-weight: bold; }' . "\n";
        $css .= $selector . ' .jtb-audio-artist { font-size: 14px; opacity: 0.8; }' . "\n";
        $css .= $selector . ' .jtb-audio-album { font-size: 12px; opacity: 0.6; }' . "\n";

        // Audio player
        $css .= $selector . ' .jtb-audio-player { width: 100%; }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('audio', JTB_Module_Audio::class);
