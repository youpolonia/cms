<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

/**
 * TB 4.0 Contact Form Module
 * Customizable contact form with configurable fields
 */
class ContactModule extends Module
{
    public function __construct()
    {
        $this->name = 'Contact Form';
        $this->slug = 'contact';
        $this->icon = 'mail';
        $this->category = 'interactive';

        $this->elements = [
            'main' => '.tb4-contact-form',
            'field' => '.tb4-contact-field',
            'input' => '.tb4-contact-input',
            'button' => '.tb4-contact-button'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'form_title' => [
                'label' => 'Form Title',
                'type' => 'text',
                'default' => 'Contact Us'
            ],
            'form_description' => [
                'label' => 'Form Description',
                'type' => 'textarea',
                'default' => 'Fill out the form below and we will get back to you shortly.'
            ],
            'show_name' => [
                'label' => 'Show Name Field',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'name_label' => [
                'label' => 'Name Label',
                'type' => 'text',
                'default' => 'Your Name'
            ],
            'name_placeholder' => [
                'label' => 'Name Placeholder',
                'type' => 'text',
                'default' => 'John Doe'
            ],
            'name_required' => [
                'label' => 'Name Required',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_email' => [
                'label' => 'Show Email Field',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'email_label' => [
                'label' => 'Email Label',
                'type' => 'text',
                'default' => 'Email Address'
            ],
            'email_placeholder' => [
                'label' => 'Email Placeholder',
                'type' => 'text',
                'default' => 'email@example.com'
            ],
            'email_required' => [
                'label' => 'Email Required',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'show_phone' => [
                'label' => 'Show Phone Field',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ],
            'phone_label' => [
                'label' => 'Phone Label',
                'type' => 'text',
                'default' => 'Phone Number'
            ],
            'phone_placeholder' => [
                'label' => 'Phone Placeholder',
                'type' => 'text',
                'default' => '+1 (555) 000-0000'
            ],
            'show_subject' => [
                'label' => 'Show Subject Field',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'yes'
            ],
            'subject_label' => [
                'label' => 'Subject Label',
                'type' => 'text',
                'default' => 'Subject'
            ],
            'subject_placeholder' => [
                'label' => 'Subject Placeholder',
                'type' => 'text',
                'default' => 'How can we help?'
            ],
            'message_label' => [
                'label' => 'Message Label',
                'type' => 'text',
                'default' => 'Message'
            ],
            'message_placeholder' => [
                'label' => 'Message Placeholder',
                'type' => 'text',
                'default' => 'Write your message here...'
            ],
            'message_rows' => [
                'label' => 'Message Rows',
                'type' => 'select',
                'options' => [
                    '3' => '3 rows',
                    '5' => '5 rows',
                    '7' => '7 rows',
                    '10' => '10 rows'
                ],
                'default' => '5'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Send Message'
            ],
            'success_message' => [
                'label' => 'Success Message',
                'type' => 'text',
                'default' => 'Thank you! Your message has been sent.'
            ],
            'recipient_email' => [
                'label' => 'Recipient Email',
                'type' => 'text',
                'default' => ''
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'form_layout' => [
                'label' => 'Form Layout',
                'type' => 'select',
                'options' => [
                    'stacked' => 'Stacked',
                    'inline' => 'Inline Labels',
                    'floating' => 'Floating Labels'
                ],
                'default' => 'stacked'
            ],
            'background_color' => [
                'label' => 'Form Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'form_padding' => [
                'label' => 'Form Padding',
                'type' => 'text',
                'default' => '32px'
            ],
            'form_border_radius' => [
                'label' => 'Form Border Radius',
                'type' => 'text',
                'default' => '12px'
            ],
            'form_shadow' => [
                'label' => 'Form Shadow',
                'type' => 'select',
                'options' => [
                    'none' => 'None',
                    'sm' => 'Small',
                    'md' => 'Medium',
                    'lg' => 'Large'
                ],
                'default' => 'md'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '24px'
            ],
            'description_color' => [
                'label' => 'Description Color',
                'type' => 'color',
                'default' => '#6b7280'
            ],
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#374151'
            ],
            'label_font_size' => [
                'label' => 'Label Font Size',
                'type' => 'text',
                'default' => '14px'
            ],
            'input_bg_color' => [
                'label' => 'Input Background',
                'type' => 'color',
                'default' => '#f9fafb'
            ],
            'input_border_color' => [
                'label' => 'Input Border',
                'type' => 'color',
                'default' => '#d1d5db'
            ],
            'input_focus_border' => [
                'label' => 'Input Focus Border',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'input_text_color' => [
                'label' => 'Input Text Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'input_border_radius' => [
                'label' => 'Input Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'input_padding' => [
                'label' => 'Input Padding',
                'type' => 'text',
                'default' => '12px 16px'
            ],
            'field_gap' => [
                'label' => 'Field Gap',
                'type' => 'text',
                'default' => '20px'
            ],
            'button_bg_color' => [
                'label' => 'Button Background',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'button_text_color' => [
                'label' => 'Button Text Color',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'button_border_radius' => [
                'label' => 'Button Border Radius',
                'type' => 'text',
                'default' => '8px'
            ],
            'button_padding' => [
                'label' => 'Button Padding',
                'type' => 'text',
                'default' => '14px 32px'
            ],
            'button_full_width' => [
                'label' => 'Full Width Button',
                'type' => 'select',
                'options' => [
                    'yes' => 'Yes',
                    'no' => 'No'
                ],
                'default' => 'no'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    public function render(array $attrs): string
    {
        // Content settings
        $formTitle = $attrs['form_title'] ?? 'Contact Us';
        $formDescription = $attrs['form_description'] ?? 'Fill out the form below and we will get back to you shortly.';
        $showName = ($attrs['show_name'] ?? 'yes') !== 'no';
        $nameLabel = $attrs['name_label'] ?? 'Your Name';
        $namePlaceholder = $attrs['name_placeholder'] ?? 'John Doe';
        $nameRequired = ($attrs['name_required'] ?? 'yes') !== 'no';
        $showEmail = ($attrs['show_email'] ?? 'yes') !== 'no';
        $emailLabel = $attrs['email_label'] ?? 'Email Address';
        $emailPlaceholder = $attrs['email_placeholder'] ?? 'email@example.com';
        $emailRequired = ($attrs['email_required'] ?? 'yes') !== 'no';
        $showPhone = ($attrs['show_phone'] ?? 'no') === 'yes';
        $phoneLabel = $attrs['phone_label'] ?? 'Phone Number';
        $phonePlaceholder = $attrs['phone_placeholder'] ?? '+1 (555) 000-0000';
        $showSubject = ($attrs['show_subject'] ?? 'yes') !== 'no';
        $subjectLabel = $attrs['subject_label'] ?? 'Subject';
        $subjectPlaceholder = $attrs['subject_placeholder'] ?? 'How can we help?';
        $messageLabel = $attrs['message_label'] ?? 'Message';
        $messagePlaceholder = $attrs['message_placeholder'] ?? 'Write your message here...';
        $messageRows = $attrs['message_rows'] ?? '5';
        $buttonText = $attrs['button_text'] ?? 'Send Message';
        $successMessage = $attrs['success_message'] ?? 'Thank you! Your message has been sent.';
        $recipientEmail = $attrs['recipient_email'] ?? '';

        // Design settings
        $formLayout = $attrs['form_layout'] ?? 'stacked';
        $bgColor = $attrs['background_color'] ?? '#ffffff';
        $formPadding = $attrs['form_padding'] ?? '32px';
        $formBorderRadius = $attrs['form_border_radius'] ?? '12px';
        $formShadow = $attrs['form_shadow'] ?? 'md';
        $titleColor = $attrs['title_color'] ?? '#111827';
        $titleFontSize = $attrs['title_font_size'] ?? '24px';
        $descriptionColor = $attrs['description_color'] ?? '#6b7280';
        $labelColor = $attrs['label_color'] ?? '#374151';
        $labelFontSize = $attrs['label_font_size'] ?? '14px';
        $inputBgColor = $attrs['input_bg_color'] ?? '#f9fafb';
        $inputBorderColor = $attrs['input_border_color'] ?? '#d1d5db';
        $inputFocusBorder = $attrs['input_focus_border'] ?? '#2563eb';
        $inputTextColor = $attrs['input_text_color'] ?? '#111827';
        $inputBorderRadius = $attrs['input_border_radius'] ?? '8px';
        $inputPadding = $attrs['input_padding'] ?? '12px 16px';
        $fieldGap = $attrs['field_gap'] ?? '20px';
        $buttonBgColor = $attrs['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $attrs['button_text_color'] ?? '#ffffff';
        $buttonBorderRadius = $attrs['button_border_radius'] ?? '8px';
        $buttonPadding = $attrs['button_padding'] ?? '14px 32px';
        $buttonFullWidth = ($attrs['button_full_width'] ?? 'no') === 'yes';

        // Shadow map
        $shadowMap = [
            'none' => 'none',
            'sm' => '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            'md' => '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
            'lg' => '0 10px 15px -3px rgba(0, 0, 0, 0.1)'
        ];
        $shadow = $shadowMap[$formShadow] ?? $shadowMap['md'];

        // Build HTML
        $html = '<div class="tb4-contact-wrapper" style="background:' . esc_attr($bgColor) . ';padding:' . esc_attr($formPadding) . ';border-radius:' . esc_attr($formBorderRadius) . ';box-shadow:' . $shadow . ';">';

        // Title
        if (!empty($formTitle)) {
            $html .= '<h3 class="tb4-contact-title" style="font-size:' . esc_attr($titleFontSize) . ';font-weight:700;color:' . esc_attr($titleColor) . ';margin:0 0 8px 0;">' . esc_html($formTitle) . '</h3>';
        }

        // Description
        if (!empty($formDescription)) {
            $html .= '<p class="tb4-contact-description" style="font-size:14px;color:' . esc_attr($descriptionColor) . ';margin:0 0 24px 0;">' . esc_html($formDescription) . '</p>';
        }

        // Form
        $html .= '<form class="tb4-contact-form" method="post" action="" data-success-message="' . esc_attr($successMessage) . '" data-recipient="' . esc_attr($recipientEmail) . '" style="display:flex;flex-direction:column;gap:' . esc_attr($fieldGap) . ';">';

        // Name field
        if ($showName) {
            $html .= $this->renderField(
                'name',
                'text',
                $nameLabel,
                $namePlaceholder,
                $nameRequired,
                $labelColor,
                $labelFontSize,
                $inputBgColor,
                $inputBorderColor,
                $inputBorderRadius,
                $inputPadding,
                $inputTextColor
            );
        }

        // Email field
        if ($showEmail) {
            $html .= $this->renderField(
                'email',
                'email',
                $emailLabel,
                $emailPlaceholder,
                $emailRequired,
                $labelColor,
                $labelFontSize,
                $inputBgColor,
                $inputBorderColor,
                $inputBorderRadius,
                $inputPadding,
                $inputTextColor
            );
        }

        // Phone field
        if ($showPhone) {
            $html .= $this->renderField(
                'phone',
                'tel',
                $phoneLabel,
                $phonePlaceholder,
                false,
                $labelColor,
                $labelFontSize,
                $inputBgColor,
                $inputBorderColor,
                $inputBorderRadius,
                $inputPadding,
                $inputTextColor
            );
        }

        // Subject field
        if ($showSubject) {
            $html .= $this->renderField(
                'subject',
                'text',
                $subjectLabel,
                $subjectPlaceholder,
                false,
                $labelColor,
                $labelFontSize,
                $inputBgColor,
                $inputBorderColor,
                $inputBorderRadius,
                $inputPadding,
                $inputTextColor
            );
        }

        // Message field (textarea)
        $html .= '<div class="tb4-contact-field" style="display:flex;flex-direction:column;gap:6px;">';
        $html .= '<label class="tb4-contact-label" style="font-size:' . esc_attr($labelFontSize) . ';font-weight:500;color:' . esc_attr($labelColor) . ';">' . esc_html($messageLabel) . '<span style="color:#ef4444;margin-left:2px;">*</span></label>';
        $html .= '<textarea name="message" class="tb4-contact-textarea" rows="' . esc_attr($messageRows) . '" placeholder="' . esc_attr($messagePlaceholder) . '" required style="padding:' . esc_attr($inputPadding) . ';background:' . esc_attr($inputBgColor) . ';border:1px solid ' . esc_attr($inputBorderColor) . ';border-radius:' . esc_attr($inputBorderRadius) . ';font-size:14px;color:' . esc_attr($inputTextColor) . ';resize:vertical;font-family:inherit;"></textarea>';
        $html .= '</div>';

        // Submit button
        $buttonWidthStyle = $buttonFullWidth ? 'width:100%;' : '';
        $html .= '<button type="submit" class="tb4-contact-button" style="padding:' . esc_attr($buttonPadding) . ';background:' . esc_attr($buttonBgColor) . ';color:' . esc_attr($buttonTextColor) . ';border:none;border-radius:' . esc_attr($buttonBorderRadius) . ';font-size:16px;font-weight:600;cursor:pointer;transition:background 0.2s;' . $buttonWidthStyle . '">' . esc_html($buttonText) . '</button>';

        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a form field
     */
    private function renderField(
        string $name,
        string $type,
        string $label,
        string $placeholder,
        bool $required,
        string $labelColor,
        string $labelFontSize,
        string $inputBgColor,
        string $inputBorderColor,
        string $inputBorderRadius,
        string $inputPadding,
        string $inputTextColor
    ): string {
        $html = '<div class="tb4-contact-field" style="display:flex;flex-direction:column;gap:6px;">';
        $html .= '<label class="tb4-contact-label" style="font-size:' . esc_attr($labelFontSize) . ';font-weight:500;color:' . esc_attr($labelColor) . ';">';
        $html .= esc_html($label);
        if ($required) {
            $html .= '<span style="color:#ef4444;margin-left:2px;">*</span>';
        }
        $html .= '</label>';
        $html .= '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" class="tb4-contact-input" placeholder="' . esc_attr($placeholder) . '"' . ($required ? ' required' : '') . ' style="padding:' . esc_attr($inputPadding) . ';background:' . esc_attr($inputBgColor) . ';border:1px solid ' . esc_attr($inputBorderColor) . ';border-radius:' . esc_attr($inputBorderRadius) . ';font-size:14px;color:' . esc_attr($inputTextColor) . ';font-family:inherit;">';
        $html .= '</div>';

        return $html;
    }
}
