<?php
/**
 * Contact Form Module
 * Customizable contact form
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_ContactForm extends JTB_Element
{
    public string $icon = 'mail';
    public string $category = 'forms';
    public string $child_slug = 'contact_form_field';

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
    protected string $module_prefix = 'contact_form';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'form_field_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-form-field input, .jtb-form-field textarea'
        ],
        'form_field_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-form-field input, .jtb-form-field textarea'
        ],
        'form_field_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-form-field input, .jtb-form-field textarea',
            'hover' => true
        ],
        'form_field_border_width' => [
            'property' => 'border-width',
            'selector' => '.jtb-form-field input, .jtb-form-field textarea',
            'unit' => 'px'
        ],
        'form_field_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-form-field input, .jtb-form-field textarea',
            'unit' => 'px'
        ],
        'button_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-contact-submit',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-contact-submit',
            'hover' => true
        ],
        'button_border_radius' => [
            'property' => 'border-radius',
            'selector' => '.jtb-contact-submit',
            'unit' => 'px'
        ]
    ];

    public function getSlug(): string
    {
        return 'contact_form';
    }

    public function getName(): string
    {
        return 'Contact Form';
    }

    public function getFields(): array
    {
        return [
            'email' => [
                'label' => 'Email Address',
                'type' => 'text',
                'description' => 'Where form submissions will be sent'
            ],
            'title' => [
                'label' => 'Form Title',
                'type' => 'text',
                'default' => 'Contact Us'
            ],
            'success_message' => [
                'label' => 'Success Message',
                'type' => 'text',
                'default' => 'Thanks for contacting us! We will get in touch with you shortly.'
            ],
            'submit_button_text' => [
                'label' => 'Submit Button Text',
                'type' => 'text',
                'default' => 'Submit'
            ],
            'use_redirect' => [
                'label' => 'Redirect After Submit',
                'type' => 'toggle',
                'default' => false
            ],
            'redirect_url' => [
                'label' => 'Redirect URL',
                'type' => 'text',
                'show_if' => ['use_redirect' => true]
            ],
            // Field visibility
            'use_name' => [
                'label' => 'Show Name Field',
                'type' => 'toggle',
                'default' => true
            ],
            'use_email' => [
                'label' => 'Show Email Field',
                'type' => 'toggle',
                'default' => true
            ],
            'use_message' => [
                'label' => 'Show Message Field',
                'type' => 'toggle',
                'default' => true
            ],
            'use_captcha' => [
                'label' => 'Enable Captcha',
                'type' => 'toggle',
                'default' => false
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
                'default' => '#bbb',
                'hover' => true
            ],
            'form_field_border_width' => [
                'label' => 'Field Border Width',
                'type' => 'range',
                'min' => 0,
                'max' => 5,
                'unit' => 'px',
                'default' => 1
            ],
            'form_field_border_radius' => [
                'label' => 'Field Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 30,
                'unit' => 'px',
                'default' => 0
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
            ],
            'button_border_radius' => [
                'label' => 'Button Border Radius',
                'type' => 'range',
                'min' => 0,
                'max' => 30,
                'unit' => 'px',
                'default' => 3
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $title = $this->esc($attrs['title'] ?? 'Contact Us');
        $submitText = $this->esc($attrs['submit_button_text'] ?? 'Submit');
        $successMessage = $this->esc($attrs['success_message'] ?? 'Thanks for contacting us!');
        $showName = $attrs['use_name'] ?? true;
        $showEmail = $attrs['use_email'] ?? true;
        $showMessage = $attrs['use_message'] ?? true;

        $formId = 'jtb-contact-form-' . $this->generateId();

        $innerHtml = '<div class="jtb-contact-form-container">';

        if (!empty($title)) {
            $innerHtml .= '<h3 class="jtb-contact-form-title">' . $title . '</h3>';
        }

        $innerHtml .= '<form class="jtb-contact-form" id="' . $formId . '" method="post" data-success-message="' . $successMessage . '">';

        if ($showName) {
            $innerHtml .= '<div class="jtb-form-field">';
            $innerHtml .= '<label for="' . $formId . '-name">Name</label>';
            $innerHtml .= '<input type="text" id="' . $formId . '-name" name="name" placeholder="Your Name" required>';
            $innerHtml .= '</div>';
        }

        if ($showEmail) {
            $innerHtml .= '<div class="jtb-form-field">';
            $innerHtml .= '<label for="' . $formId . '-email">Email</label>';
            $innerHtml .= '<input type="email" id="' . $formId . '-email" name="email" placeholder="Your Email" required>';
            $innerHtml .= '</div>';
        }

        if ($showMessage) {
            $innerHtml .= '<div class="jtb-form-field">';
            $innerHtml .= '<label for="' . $formId . '-message">Message</label>';
            $innerHtml .= '<textarea id="' . $formId . '-message" name="message" placeholder="Your Message" rows="5" required></textarea>';
            $innerHtml .= '</div>';
        }

        $innerHtml .= '<div class="jtb-form-field jtb-form-submit">';
        $innerHtml .= '<button type="submit" class="jtb-button jtb-contact-submit">' . $submitText . '</button>';
        $innerHtml .= '</div>';

        $innerHtml .= '</form>';

        $innerHtml .= '<div class="jtb-contact-form-success" style="display: none;">' . $successMessage . '</div>';

        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Contact Form module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Form fields base styles
        $css .= $selector . ' .jtb-form-field { margin-bottom: 20px; }' . "\n";
        $css .= $selector . ' .jtb-form-field label { display: block; margin-bottom: 5px; font-weight: 500; }' . "\n";

        $css .= $selector . ' .jtb-form-field input, ' . $selector . ' .jtb-form-field textarea { ';
        $css .= 'width: 100%; padding: 12px 15px; font-size: 14px; transition: border-color 0.3s ease; box-sizing: border-box; border-style: solid; ';
        $css .= '}' . "\n";

        // Focus state
        $css .= $selector . ' .jtb-form-field input:focus, ' . $selector . ' .jtb-form-field textarea:focus { outline: none; }' . "\n";

        // Button base styles
        $css .= $selector . ' .jtb-contact-submit { border: none; padding: 12px 30px; cursor: pointer; font-size: 14px; transition: all 0.3s ease; }' . "\n";

        // Success message
        $css .= $selector . ' .jtb-contact-form-success { padding: 20px; background: #d4edda; color: #155724; border-radius: 5px; text-align: center; }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('contact_form', JTB_Module_ContactForm::class);
