<?php
/**
 * Fullwidth Header Module
 * Full-width hero header with title, content, and buttons
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_FullwidthHeader extends JTB_Element
{
    public string $icon = 'header';
    public string $category = 'fullwidth';

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = false;
    public bool $use_box_shadow = false;
    public bool $use_animation = true;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = true;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'fullwidth_header';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'background_overlay_color' => [
            'property' => 'background',
            'selector' => '.jtb-header-overlay'
        ],
        'title_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-header-title'
        ],
        'content_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-header-description'
        ],
        'title_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-header-title',
            'unit' => 'px',
            'responsive' => true
        ],
        'subhead_font_size' => [
            'property' => 'font-size',
            'selector' => '.jtb-header-subhead',
            'unit' => 'px',
            'responsive' => true
        ],
        'header_height' => [
            'property' => 'min-height',
            'selector' => '.jtb-fullwidth-header-container',
            'unit' => 'px',
            'responsive' => true
        ],
        'button_one_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-header-button-one',
            'hover' => true
        ],
        'button_one_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-header-button-one',
            'hover' => true
        ],
        'button_two_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-header-button-two',
            'hover' => true
        ],
        'button_two_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-header-button-two',
            'hover' => true
        ],
        'button_two_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-header-button-two',
            'hover' => true
        ],
        'scroll_down_color' => [
            'property' => 'color',
            'selector' => '.jtb-scroll-icon'
        ]
    ];

    public function getSlug(): string
    {
        return 'fullwidth_header';
    }

    public function getName(): string
    {
        return 'Fullwidth Header';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Your Title Goes Here'
            ],
            'subhead' => [
                'label' => 'Subheading',
                'type' => 'text'
            ],
            'content' => [
                'label' => 'Content',
                'type' => 'richtext',
                'default' => '<p>Your content goes here.</p>'
            ],
            'button_one_text' => [
                'label' => 'Button One Text',
                'type' => 'text',
                'default' => 'Learn More'
            ],
            'button_one_url' => [
                'label' => 'Button One URL',
                'type' => 'text'
            ],
            'button_two_text' => [
                'label' => 'Button Two Text',
                'type' => 'text'
            ],
            'button_two_url' => [
                'label' => 'Button Two URL',
                'type' => 'text'
            ],
            'header_fullscreen' => [
                'label' => 'Make Fullscreen',
                'type' => 'toggle',
                'default' => false
            ],
            'header_height' => [
                'label' => 'Header Height',
                'type' => 'range',
                'min' => 200,
                'max' => 1000,
                'unit' => 'px',
                'default' => 500,
                'show_if' => ['header_fullscreen' => false],
                'responsive' => true
            ],
            'text_orientation' => [
                'label' => 'Text Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center',
                'responsive' => true
            ],
            'content_orientation' => [
                'label' => 'Content Position',
                'type' => 'select',
                'options' => [
                    'top' => 'Top',
                    'center' => 'Center',
                    'bottom' => 'Bottom'
                ],
                'default' => 'center'
            ],
            'header_level' => [
                'label' => 'Title Heading Level',
                'type' => 'select',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4'
                ],
                'default' => 'h1'
            ],
            'background_overlay_color' => [
                'label' => 'Overlay Color',
                'type' => 'color',
                'default' => 'rgba(0,0,0,0.3)'
            ],
            'title_text_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'content_text_color' => [
                'label' => 'Content Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'range',
                'min' => 20,
                'max' => 120,
                'unit' => 'px',
                'default' => 60,
                'responsive' => true
            ],
            'subhead_font_size' => [
                'label' => 'Subheading Font Size',
                'type' => 'range',
                'min' => 12,
                'max' => 60,
                'unit' => 'px',
                'default' => 24,
                'responsive' => true
            ],
            // Buttons
            'button_one_bg_color' => [
                'label' => 'Button 1 Background',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_one_text_color' => [
                'label' => 'Button 1 Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'button_two_bg_color' => [
                'label' => 'Button 2 Background',
                'type' => 'color',
                'default' => 'transparent',
                'hover' => true
            ],
            'button_two_text_color' => [
                'label' => 'Button 2 Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'button_two_border_color' => [
                'label' => 'Button 2 Border Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ],
            'scroll_down_icon' => [
                'label' => 'Show Scroll Down',
                'type' => 'toggle',
                'default' => false
            ],
            'scroll_down_color' => [
                'label' => 'Scroll Icon Color',
                'type' => 'color',
                'default' => '#ffffff',
                'show_if' => ['scroll_down_icon' => true]
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Your Title Goes Here');
        $subhead = $this->esc($attrs['subhead'] ?? '');
        $bodyContent = $attrs['content'] ?? '';
        $btn1Text = $this->esc($attrs['button_one_text'] ?? '');
        $btn1Url = $attrs['button_one_url'] ?? '';
        $btn2Text = $this->esc($attrs['button_two_text'] ?? '');
        $btn2Url = $attrs['button_two_url'] ?? '';
        $headerLevel = $attrs['header_level'] ?? 'h1';
        $fullscreen = !empty($attrs['header_fullscreen']);
        $showScroll = !empty($attrs['scroll_down_icon']);
        $contentPosition = $attrs['content_orientation'] ?? 'center';

        $containerClass = 'jtb-fullwidth-header-container';
        if ($fullscreen) {
            $containerClass .= ' jtb-header-fullscreen';
        }
        $containerClass .= ' jtb-content-position-' . $contentPosition;

        $innerHtml = '<div class="' . $containerClass . '">';
        $innerHtml .= '<div class="jtb-header-overlay"></div>';

        $innerHtml .= '<div class="jtb-header-content">';

        // Title
        if (!empty($title)) {
            $innerHtml .= '<' . $headerLevel . ' class="jtb-header-title">' . $title . '</' . $headerLevel . '>';
        }

        // Subheading
        if (!empty($subhead)) {
            $innerHtml .= '<div class="jtb-header-subhead">' . $subhead . '</div>';
        }

        // Content
        if (!empty($bodyContent)) {
            $innerHtml .= '<div class="jtb-header-description">' . $bodyContent . '</div>';
        }

        // Buttons
        if (!empty($btn1Text) || !empty($btn2Text)) {
            $innerHtml .= '<div class="jtb-header-buttons">';

            if (!empty($btn1Text) && !empty($btn1Url)) {
                $innerHtml .= '<a href="' . $this->esc($btn1Url) . '" class="jtb-button jtb-header-button-one">' . $btn1Text . '</a>';
            }

            if (!empty($btn2Text) && !empty($btn2Url)) {
                $innerHtml .= '<a href="' . $this->esc($btn2Url) . '" class="jtb-button jtb-header-button-two">' . $btn2Text . '</a>';
            }

            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';

        // Scroll down
        if ($showScroll) {
            $scrollIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';
            $innerHtml .= '<div class="jtb-header-scroll-down">';
            $innerHtml .= '<span class="jtb-scroll-icon">' . $scrollIcon . '</span>';
            $innerHtml .= '</div>';
        }

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $fullscreen = !empty($attrs['header_fullscreen']);
        $height = $attrs['header_height'] ?? 500;
        $overlayColor = $attrs['background_overlay_color'] ?? 'rgba(0,0,0,0.3)';
        $titleColor = $attrs['title_text_color'] ?? '#ffffff';
        $contentColor = $attrs['content_text_color'] ?? '#ffffff';
        $titleSize = $attrs['title_font_size'] ?? 60;
        $subheadSize = $attrs['subhead_font_size'] ?? 24;
        $textAlign = $attrs['text_orientation'] ?? 'center';
        $contentPosition = $attrs['content_orientation'] ?? 'center';

        // Container
        $css .= $selector . ' .jtb-fullwidth-header-container { ';
        $css .= 'position: relative; ';
        $css .= 'display: flex; ';
        $css .= 'flex-direction: column; ';
        if (!$fullscreen) {
            $css .= 'min-height: ' . $height . 'px; ';
        }
        $css .= '}' . "\n";

        if ($fullscreen) {
            $css .= $selector . ' .jtb-header-fullscreen { min-height: 100vh; }' . "\n";
        }

        // Content position
        if ($contentPosition === 'top') {
            $css .= $selector . ' .jtb-fullwidth-header-container { justify-content: flex-start; padding-top: 80px; }' . "\n";
        } elseif ($contentPosition === 'bottom') {
            $css .= $selector . ' .jtb-fullwidth-header-container { justify-content: flex-end; padding-bottom: 80px; }' . "\n";
        } else {
            $css .= $selector . ' .jtb-fullwidth-header-container { justify-content: center; }' . "\n";
        }

        // Overlay
        $css .= $selector . ' .jtb-header-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: ' . $overlayColor . '; }' . "\n";

        // Content
        $css .= $selector . ' .jtb-header-content { position: relative; z-index: 1; max-width: 1080px; margin: 0 auto; padding: 40px; text-align: ' . $textAlign . '; width: 100%; box-sizing: border-box; }' . "\n";

        // Title
        $css .= $selector . ' .jtb-header-title { color: ' . $titleColor . '; font-size: ' . $titleSize . 'px; margin: 0 0 20px; }' . "\n";

        // Subhead
        $css .= $selector . ' .jtb-header-subhead { color: ' . $titleColor . '; font-size: ' . $subheadSize . 'px; margin-bottom: 20px; opacity: 0.9; }' . "\n";

        // Description
        $css .= $selector . ' .jtb-header-description { color: ' . $contentColor . '; margin-bottom: 30px; font-size: 18px; max-width: 800px; margin-left: auto; margin-right: auto; }' . "\n";

        // Buttons
        $css .= $selector . ' .jtb-header-buttons { display: flex; gap: 15px; justify-content: ' . ($textAlign === 'left' ? 'flex-start' : ($textAlign === 'right' ? 'flex-end' : 'center')) . '; flex-wrap: wrap; }' . "\n";

        // Button One
        $btn1Bg = $attrs['button_one_bg_color'] ?? '#2ea3f2';
        $btn1Text = $attrs['button_one_text_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-header-button-one { background-color: ' . $btn1Bg . '; color: ' . $btn1Text . '; border: none; padding: 15px 30px; text-decoration: none; font-size: 16px; transition: all 0.3s ease; }' . "\n";

        if (!empty($attrs['button_one_bg_color__hover'])) {
            $css .= $selector . ' .jtb-header-button-one:hover { background-color: ' . $attrs['button_one_bg_color__hover'] . '; }' . "\n";
        }

        // Button Two
        $btn2Bg = $attrs['button_two_bg_color'] ?? 'transparent';
        $btn2Text = $attrs['button_two_text_color'] ?? '#ffffff';
        $btn2Border = $attrs['button_two_border_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-header-button-two { background-color: ' . $btn2Bg . '; color: ' . $btn2Text . '; border: 2px solid ' . $btn2Border . '; padding: 13px 28px; text-decoration: none; font-size: 16px; transition: all 0.3s ease; }' . "\n";

        if (!empty($attrs['button_two_bg_color__hover'])) {
            $css .= $selector . ' .jtb-header-button-two:hover { background-color: ' . $attrs['button_two_bg_color__hover'] . '; }' . "\n";
        }

        // Scroll down
        $scrollColor = $attrs['scroll_down_color'] ?? '#ffffff';
        $css .= $selector . ' .jtb-header-scroll-down { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 1; }' . "\n";
        $css .= $selector . ' .jtb-scroll-icon { display: inline-flex; align-items: center; justify-content: center; color: ' . $scrollColor . '; animation: jtb-bounce 2s infinite; cursor: pointer; }' . "\n";
        $css .= $selector . ' .jtb-scroll-icon svg { width: 30px; height: 30px; }' . "\n";
        $css .= '@keyframes jtb-bounce { 0%, 20%, 50%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-15px); } 60% { transform: translateY(-7px); } }' . "\n";

        // Responsive
        if (!empty($attrs['header_height__tablet']) && !$fullscreen) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-fullwidth-header-container { min-height: ' . $attrs['header_height__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['header_height__phone']) && !$fullscreen) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-fullwidth-header-container { min-height: ' . $attrs['header_height__phone'] . 'px; } }' . "\n";
        }

        if (!empty($attrs['title_font_size__tablet'])) {
            $css .= '@media (max-width: 980px) { ' . $selector . ' .jtb-header-title { font-size: ' . $attrs['title_font_size__tablet'] . 'px; } }' . "\n";
        }
        if (!empty($attrs['title_font_size__phone'])) {
            $css .= '@media (max-width: 767px) { ' . $selector . ' .jtb-header-title { font-size: ' . $attrs['title_font_size__phone'] . 'px; } }' . "\n";
        }

        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('fullwidth_header', JTB_Module_FullwidthHeader::class);
