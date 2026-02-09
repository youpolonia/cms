<?php
/**
 * Email Optin/Signup Module
 * Newsletter signup form
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Signup extends JTB_Element
{
    public string $icon = 'mail-list';
    public string $category = 'forms';

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
    protected string $module_prefix = 'signup';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'form_field_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-form-field input'
        ],
        'form_field_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-form-field input'
        ],
        'form_field_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-form-field input'
        ],
        'button_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-signup-submit',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-signup-submit',
            'hover' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'signup';
    }

    public function getName(): string
    {
        return 'Email Optin';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Subscribe to Our Newsletter'
            ],
            'content' => [
                'label' => 'Description',
                'type' => 'richtext',
                'default' => '<p>Get the latest news and updates delivered to your inbox.</p>'
            ],
            'email_provider' => [
                'label' => 'Email Service',
                'type' => 'select',
                'options' => [
                    'none' => 'None (Store Locally)',
                    'mailchimp' => 'Mailchimp',
                    'convertkit' => 'ConvertKit',
                    'custom' => 'Custom API'
                ],
                'default' => 'none'
            ],
            'api_key' => [
                'label' => 'API Key',
                'type' => 'text',
                'show_if_not' => ['email_provider' => 'none']
            ],
            'list_id' => [
                'label' => 'List ID',
                'type' => 'text',
                'show_if_not' => ['email_provider' => 'none']
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Subscribe'
            ],
            'success_message' => [
                'label' => 'Success Message',
                'type' => 'text',
                'default' => 'Thanks for subscribing!'
            ],
            'layout' => [
                'label' => 'Layout',
                'type' => 'select',
                'options' => [
                    'stacked' => 'Stacked',
                    'inline' => 'Inline'
                ],
                'default' => 'stacked'
            ],
            'use_name' => [
                'label' => 'Show Name Field',
                'type' => 'toggle',
                'default' => false
            ],
            'use_first_last' => [
                'label' => 'Separate First/Last Name',
                'type' => 'toggle',
                'default' => false,
                'show_if' => ['use_name' => true]
            ],
            // Styling
            'form_field_bg_color' => [
                'label' => 'Field Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'form_field_text_color' => [
                'label' => 'Field Text Color',
                'type' => 'color',
                'default' => '#666666'
            ],
            'form_field_border_color' => [
                'label' => 'Field Border Color',
                'type' => 'color',
                'default' => '#bbb'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2ea3f2',
                'hover' => true
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff',
                'hover' => true
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Subscribe to Our Newsletter');
        $description = $attrs['content'] ?? '';
        $buttonText = $this->esc($attrs['button_text'] ?? 'Subscribe');
        $successMessage = $this->esc($attrs['success_message'] ?? 'Thanks for subscribing!');
        $layout = $attrs['layout'] ?? 'stacked';
        $useName = !empty($attrs['use_name']);
        $useFirstLast = !empty($attrs['use_first_last']);

        $formId = 'jtb-signup-form-' . $this->generateId();
        $layoutClass = 'jtb-signup-' . $layout;

        $innerHtml = '<div class="jtb-signup-container ' . $layoutClass . '">';

        // Header
        if (!empty($title) || !empty($description)) {
            $innerHtml .= '<div class="jtb-signup-header">';
            if (!empty($title)) {
                $innerHtml .= '<h3 class="jtb-signup-title">' . $title . '</h3>';
            }
            if (!empty($description)) {
                $innerHtml .= '<div class="jtb-signup-description">' . $description . '</div>';
            }
            $innerHtml .= '</div>';
        }

        // Form
        $innerHtml .= '<form class="jtb-signup-form" id="' . $formId . '" data-success-message="' . $successMessage . '">';
        $innerHtml .= '<div class="jtb-signup-fields">';

        // Name fields
        if ($useName) {
            if ($useFirstLast) {
                $innerHtml .= '<div class="jtb-form-field jtb-field-first-name">';
                $innerHtml .= '<input type="text" name="first_name" placeholder="First Name" required>';
                $innerHtml .= '</div>';
                $innerHtml .= '<div class="jtb-form-field jtb-field-last-name">';
                $innerHtml .= '<input type="text" name="last_name" placeholder="Last Name">';
                $innerHtml .= '</div>';
            } else {
                $innerHtml .= '<div class="jtb-form-field jtb-field-name">';
                $innerHtml .= '<input type="text" name="name" placeholder="Your Name" required>';
                $innerHtml .= '</div>';
            }
        }

        // Email field
        $innerHtml .= '<div class="jtb-form-field jtb-field-email">';
        $innerHtml .= '<input type="email" name="email" placeholder="Your Email" required>';
        $innerHtml .= '</div>';

        // Submit
        $innerHtml .= '<div class="jtb-form-field jtb-form-submit">';
        $innerHtml .= '<button type="submit" class="jtb-button jtb-signup-submit">' . $buttonText . '</button>';
        $innerHtml .= '</div>';

        $innerHtml .= '</div>';
        $innerHtml .= '</form>';

        // Success
        $innerHtml .= '<div class="jtb-signup-success" style="display: none;">' . $successMessage . '</div>';

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Signup module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        $layout = $attrs['layout'] ?? 'stacked';

        // Layout
        if ($layout === 'inline') {
            $css .= $selector . ' .jtb-signup-fields { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-start; }' . "\n";
            $css .= $selector . ' .jtb-field-email { flex: 1; min-width: 200px; }' . "\n";
            $css .= $selector . ' .jtb-form-submit { flex-shrink: 0; }' . "\n";
        }

        // Form fields base styles
        $css .= $selector . ' .jtb-form-field { margin-bottom: 15px; }' . "\n";

        $css .= $selector . ' .jtb-form-field input { ';
        $css .= 'width: 100%; padding: 12px 15px; box-sizing: border-box; font-size: 14px; border: 1px solid; ';
        $css .= '}' . "\n";

        // Button base styles
        $css .= $selector . ' .jtb-signup-submit { ';
        if ($layout === 'stacked') {
            $css .= 'width: 100%; ';
        }
        $css .= 'border: none; padding: 12px 30px; cursor: pointer; font-size: 14px; transition: all 0.3s ease; white-space: nowrap; ';
        $css .= '}' . "\n";

        // Success
        $css .= $selector . ' .jtb-signup-success { padding: 20px; background: #d4edda; color: #155724; border-radius: 5px; text-align: center; }' . "\n";

        // Header
        $css .= $selector . ' .jtb-signup-header { margin-bottom: 20px; text-align: center; }' . "\n";
        $css .= $selector . ' .jtb-signup-title { margin-bottom: 10px; }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('signup', JTB_Module_Signup::class);
