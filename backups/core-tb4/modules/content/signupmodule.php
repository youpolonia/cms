<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;

class SignupModule extends Module
{
    protected array $content_fields = [];
    protected array $design_fields = [];

    public function __construct()
    {
        $this->name = 'Email Signup';
        $this->slug = 'signup';
        $this->icon = 'mail';
        $this->category = 'content';

        $this->content_fields = [
            'title' => ['type' => 'text', 'label' => 'Title', 'default' => 'Subscribe to Our Newsletter'],
            'description' => ['type' => 'textarea', 'label' => 'Description', 'default' => 'Get the latest updates, tips, and exclusive content delivered straight to your inbox.'],
            'layout' => ['type' => 'select', 'label' => 'Layout', 'options' => ['inline' => 'Inline (Side by Side)', 'stacked' => 'Stacked (Vertical)', 'minimal' => 'Minimal (Email Only)', 'card' => 'Card Style', 'split' => 'Split (Image + Form)'], 'default' => 'stacked'],
            'show_name_field' => ['type' => 'select', 'label' => 'Show Name Field', 'options' => ['no' => 'No', 'yes' => 'Yes', 'first_last' => 'First & Last Name'], 'default' => 'no'],
            'email_placeholder' => ['type' => 'text', 'label' => 'Email Placeholder', 'default' => 'Enter your email address'],
            'name_placeholder' => ['type' => 'text', 'label' => 'Name Placeholder', 'default' => 'Your name'],
            'first_name_placeholder' => ['type' => 'text', 'label' => 'First Name Placeholder', 'default' => 'First name'],
            'last_name_placeholder' => ['type' => 'text', 'label' => 'Last Name Placeholder', 'default' => 'Last name'],
            'button_text' => ['type' => 'text', 'label' => 'Button Text', 'default' => 'Subscribe'],
            'success_message' => ['type' => 'text', 'label' => 'Success Message', 'default' => 'Thank you for subscribing!'],
            'error_message' => ['type' => 'text', 'label' => 'Error Message', 'default' => 'Something went wrong. Please try again.'],
            'show_privacy_notice' => ['type' => 'select', 'label' => 'Show Privacy Notice', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'yes'],
            'privacy_text' => ['type' => 'text', 'label' => 'Privacy Text', 'default' => 'We respect your privacy. Unsubscribe at any time.'],
            'show_checkbox' => ['type' => 'select', 'label' => 'Show Consent Checkbox', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'no'],
            'checkbox_text' => ['type' => 'text', 'label' => 'Checkbox Text', 'default' => 'I agree to receive marketing emails'],
            'provider' => ['type' => 'select', 'label' => 'Email Provider', 'options' => ['none' => 'None (Custom)', 'mailchimp' => 'Mailchimp', 'convertkit' => 'ConvertKit', 'mailerlite' => 'MailerLite', 'sendinblue' => 'Brevo (Sendinblue)', 'custom_api' => 'Custom API'], 'default' => 'none'],
            'form_action' => ['type' => 'text', 'label' => 'Form Action URL', 'default' => ''],
            'image_url' => ['type' => 'text', 'label' => 'Image URL (for Split layout)', 'default' => ''],
            'image_position' => ['type' => 'select', 'label' => 'Image Position', 'options' => ['left' => 'Left', 'right' => 'Right'], 'default' => 'left'],
            'show_subscriber_count' => ['type' => 'select', 'label' => 'Show Subscriber Count', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'no'],
            'subscriber_count' => ['type' => 'text', 'label' => 'Subscriber Count Text', 'default' => 'Join 10,000+ subscribers']
        ];

        $this->design_fields = [
            'background_type' => ['type' => 'select', 'label' => 'Background Type', 'options' => ['color' => 'Solid Color', 'gradient' => 'Gradient', 'image' => 'Image'], 'default' => 'color'],
            'background_color' => ['type' => 'color', 'label' => 'Background Color', 'default' => '#f8fafc'],
            'gradient_start' => ['type' => 'color', 'label' => 'Gradient Start', 'default' => '#667eea'],
            'gradient_end' => ['type' => 'color', 'label' => 'Gradient End', 'default' => '#764ba2'],
            'gradient_direction' => ['type' => 'select', 'label' => 'Gradient Direction', 'options' => ['to right' => 'Horizontal', 'to bottom' => 'Vertical', '135deg' => 'Diagonal'], 'default' => '135deg'],
            'background_image' => ['type' => 'text', 'label' => 'Background Image URL', 'default' => ''],
            'overlay_color' => ['type' => 'color', 'label' => 'Overlay Color', 'default' => 'rgba(0,0,0,0.5)'],
            'padding' => ['type' => 'text', 'label' => 'Section Padding', 'default' => '60px 24px'],
            'border_radius' => ['type' => 'text', 'label' => 'Container Radius', 'default' => '16px'],
            'box_shadow' => ['type' => 'select', 'label' => 'Box Shadow', 'options' => ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'], 'default' => 'none'],
            'max_width' => ['type' => 'select', 'label' => 'Max Width', 'options' => ['sm' => 'Small (480px)', 'md' => 'Medium (600px)', 'lg' => 'Large (800px)', 'xl' => 'Extra Large (1000px)', 'full' => 'Full Width'], 'default' => 'md'],
            'text_align' => ['type' => 'select', 'label' => 'Text Alignment', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'], 'default' => 'center'],
            'title_color' => ['type' => 'color', 'label' => 'Title Color', 'default' => '#111827'],
            'title_font_size' => ['type' => 'text', 'label' => 'Title Font Size', 'default' => '32px'],
            'title_font_weight' => ['type' => 'select', 'label' => 'Title Font Weight', 'options' => ['400' => 'Normal', '500' => 'Medium', '600' => 'Semi Bold', '700' => 'Bold', '800' => 'Extra Bold'], 'default' => '700'],
            'title_margin_bottom' => ['type' => 'text', 'label' => 'Title Margin Bottom', 'default' => '16px'],
            'description_color' => ['type' => 'color', 'label' => 'Description Color', 'default' => '#6b7280'],
            'description_font_size' => ['type' => 'text', 'label' => 'Description Font Size', 'default' => '16px'],
            'description_margin_bottom' => ['type' => 'text', 'label' => 'Description Margin Bottom', 'default' => '32px'],
            'input_bg_color' => ['type' => 'color', 'label' => 'Input Background', 'default' => '#ffffff'],
            'input_text_color' => ['type' => 'color', 'label' => 'Input Text Color', 'default' => '#111827'],
            'input_border_color' => ['type' => 'color', 'label' => 'Input Border Color', 'default' => '#d1d5db'],
            'input_border_width' => ['type' => 'text', 'label' => 'Input Border Width', 'default' => '1px'],
            'input_border_radius' => ['type' => 'text', 'label' => 'Input Border Radius', 'default' => '8px'],
            'input_padding' => ['type' => 'text', 'label' => 'Input Padding', 'default' => '14px 18px'],
            'input_focus_border' => ['type' => 'color', 'label' => 'Input Focus Border', 'default' => '#2563eb'],
            'button_bg_color' => ['type' => 'color', 'label' => 'Button Background', 'default' => '#2563eb'],
            'button_text_color' => ['type' => 'color', 'label' => 'Button Text Color', 'default' => '#ffffff'],
            'button_hover_bg' => ['type' => 'color', 'label' => 'Button Hover Background', 'default' => '#1d4ed8'],
            'button_border_radius' => ['type' => 'text', 'label' => 'Button Border Radius', 'default' => '8px'],
            'button_padding' => ['type' => 'text', 'label' => 'Button Padding', 'default' => '14px 28px'],
            'button_font_size' => ['type' => 'text', 'label' => 'Button Font Size', 'default' => '16px'],
            'button_font_weight' => ['type' => 'select', 'label' => 'Button Font Weight', 'options' => ['400' => 'Normal', '500' => 'Medium', '600' => 'Semi Bold', '700' => 'Bold'], 'default' => '600'],
            'button_full_width' => ['type' => 'select', 'label' => 'Full Width Button (Stacked)', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'no'],
            'field_gap' => ['type' => 'text', 'label' => 'Field Gap', 'default' => '12px'],
            'privacy_color' => ['type' => 'color', 'label' => 'Privacy Text Color', 'default' => '#9ca3af'],
            'privacy_font_size' => ['type' => 'text', 'label' => 'Privacy Font Size', 'default' => '13px'],
            'subscriber_count_color' => ['type' => 'color', 'label' => 'Subscriber Count Color', 'default' => '#6b7280'],
            'success_color' => ['type' => 'color', 'label' => 'Success Message Color', 'default' => '#10b981'],
            'error_color' => ['type' => 'color', 'label' => 'Error Message Color', 'default' => '#ef4444'],
            'icon_color' => ['type' => 'color', 'label' => 'Icon Color', 'default' => '#2563eb'],
            'show_email_icon' => ['type' => 'select', 'label' => 'Show Email Icon in Input', 'options' => ['no' => 'No', 'yes' => 'Yes'], 'default' => 'yes']
        ];

        $this->advanced_fields = [
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'default' => ''],
            'css_class' => ['type' => 'text', 'label' => 'CSS Class', 'default' => ''],
            'custom_css' => ['type' => 'textarea', 'label' => 'Custom CSS', 'default' => '']
        ];
    }

    /**
     * Get content tab fields (required by parent abstract class)
     */
    public function get_content_fields(): array
    {
        return $this->content_fields;
    }

    /**
     * Get design tab fields
     */
    public function get_design_fields(): array
    {
        return $this->design_fields;
    }

    /**
     * Get defaults for a specific section
     */
    protected function getDefaults(string $section): array
    {
        $defaults = [];
        $fields = match($section) {
            'content' => $this->content_fields,
            'design' => $this->design_fields,
            'advanced' => $this->advanced_fields,
            default => []
        };

        foreach ($fields as $key => $field) {
            $defaults[$key] = $field['default'] ?? '';
        }

        return $defaults;
    }

    public function render(array $attrs): string
    {
        $content = array_merge($this->getDefaults('content'), $attrs['content'] ?? []);
        $design = array_merge($this->getDefaults('design'), $attrs['design'] ?? []);
        $advanced = array_merge($this->getDefaults('advanced'), $attrs['advanced'] ?? []);

        $id = $advanced['css_id'] ? ' id="' . htmlspecialchars($advanced['css_id']) . '"' : '';
        $class = 'tb4-signup';
        if ($advanced['css_class']) {
            $class .= ' ' . htmlspecialchars($advanced['css_class']);
        }

        // Max width mapping
        $maxWidthMap = [
            'sm' => '480px',
            'md' => '600px',
            'lg' => '800px',
            'xl' => '1000px',
            'full' => '100%'
        ];
        $maxWidth = $maxWidthMap[$design['max_width']] ?? '600px';

        // Background style
        $bgStyle = '';
        if ($design['background_type'] === 'color') {
            $bgStyle = 'background:' . htmlspecialchars($design['background_color']) . ';';
        } elseif ($design['background_type'] === 'gradient') {
            $bgStyle = 'background:linear-gradient(' . htmlspecialchars($design['gradient_direction']) . ',' . htmlspecialchars($design['gradient_start']) . ',' . htmlspecialchars($design['gradient_end']) . ');';
        } elseif ($design['background_type'] === 'image' && $design['background_image']) {
            $bgStyle = 'background:url(' . htmlspecialchars($design['background_image']) . ') center/cover no-repeat;';
        }

        // Box shadow
        $shadowMap = [
            'none' => '',
            'sm' => 'box-shadow:0 1px 3px rgba(0,0,0,0.1);',
            'md' => 'box-shadow:0 4px 6px rgba(0,0,0,0.1);',
            'lg' => 'box-shadow:0 10px 25px rgba(0,0,0,0.15);'
        ];
        $shadowStyle = $shadowMap[$design['box_shadow']] ?? '';

        $layout = $content['layout'];

        $html = '<div' . $id . ' class="' . $class . '">';

        // Split layout
        if ($layout === 'split') {
            $imageUrl = $content['image_url'] ?: '';
            $imagePos = $content['image_position'];
            $splitGradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';

            $html .= '<div class="tb4-signup-split" style="display:grid;grid-template-columns:1fr 1fr;overflow:hidden;border-radius:' . htmlspecialchars($design['border_radius']) . ';' . $shadowStyle . '">';

            if ($imagePos === 'left') {
                $html .= '<div class="tb4-signup-split-image" style="background:' . ($imageUrl ? 'url(' . htmlspecialchars($imageUrl) . ')' : $splitGradient) . ';background-size:cover;background-position:center;min-height:400px;"></div>';
            }

            $html .= '<div class="tb4-signup-split-content" style="padding:60px 48px;background:' . htmlspecialchars($design['background_color']) . ';display:flex;flex-direction:column;justify-content:center;text-align:' . htmlspecialchars($design['text_align']) . ';">';
        } elseif ($layout === 'card') {
            $html .= '<div class="tb4-signup-wrapper" style="' . $bgStyle . 'padding:' . htmlspecialchars($design['padding']) . ';">';
            $html .= '<div class="tb4-signup-card" style="max-width:' . $maxWidth . ';margin:0 auto;background:#ffffff;border-radius:' . htmlspecialchars($design['border_radius']) . ';padding:48px;box-shadow:0 10px 40px rgba(0,0,0,0.1);text-align:' . htmlspecialchars($design['text_align']) . ';">';
        } else {
            $html .= '<div class="tb4-signup-wrapper" style="' . $bgStyle . 'padding:' . htmlspecialchars($design['padding']) . ';border-radius:' . htmlspecialchars($design['border_radius']) . ';' . $shadowStyle . '">';
            $html .= '<div class="tb4-signup-container" style="max-width:' . $maxWidth . ';margin:0 auto;text-align:' . htmlspecialchars($design['text_align']) . ';">';
        }

        // Subscriber count
        if ($content['show_subscriber_count'] === 'yes') {
            $justifyContent = $design['text_align'] === 'center' ? 'justify-content:center;' : '';
            $html .= '<div class="tb4-signup-subscriber-count" style="display:flex;align-items:center;' . $justifyContent . 'gap:8px;font-size:14px;color:' . htmlspecialchars($design['subscriber_count_color']) . ';margin-bottom:24px;">';
            $html .= '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
            $html .= htmlspecialchars($content['subscriber_count']) . '</div>';
        }

        // Title
        if ($content['title']) {
            $html .= '<h2 class="tb4-signup-title" style="font-size:' . htmlspecialchars($design['title_font_size']) . ';font-weight:' . htmlspecialchars($design['title_font_weight']) . ';color:' . htmlspecialchars($design['title_color']) . ';margin:0 0 ' . htmlspecialchars($design['title_margin_bottom']) . ' 0;line-height:1.2;">' . htmlspecialchars($content['title']) . '</h2>';
        }

        // Description
        if ($content['description'] && $layout !== 'minimal') {
            $html .= '<p class="tb4-signup-description" style="font-size:' . htmlspecialchars($design['description_font_size']) . ';color:' . htmlspecialchars($design['description_color']) . ';margin:0 0 ' . htmlspecialchars($design['description_margin_bottom']) . ' 0;line-height:1.6;">' . htmlspecialchars($content['description']) . '</p>';
        }

        // Form
        $formStyle = 'display:flex;gap:' . htmlspecialchars($design['field_gap']) . ';';
        if ($layout === 'inline') {
            $formStyle .= 'flex-direction:row;flex-wrap:wrap;';
        } else {
            $formStyle .= 'flex-direction:column;';
        }

        $formAction = $content['form_action'] ? ' action="' . htmlspecialchars($content['form_action']) . '"' : '';
        $html .= '<form class="tb4-signup-form" style="' . $formStyle . '"' . $formAction . ' method="post">';

        $inputStyle = 'width:100%;padding:' . htmlspecialchars($design['input_padding']) . ';background:' . htmlspecialchars($design['input_bg_color']) . ';border:' . htmlspecialchars($design['input_border_width']) . ' solid ' . htmlspecialchars($design['input_border_color']) . ';border-radius:' . htmlspecialchars($design['input_border_radius']) . ';font-size:15px;color:' . htmlspecialchars($design['input_text_color']) . ';outline:none;box-sizing:border-box;';

        // Name fields
        if ($content['show_name_field'] === 'yes') {
            $html .= '<input type="text" name="name" placeholder="' . htmlspecialchars($content['name_placeholder']) . '" style="' . $inputStyle . '" required>';
        } elseif ($content['show_name_field'] === 'first_last') {
            if ($layout === 'inline') {
                $html .= '<input type="text" name="first_name" placeholder="' . htmlspecialchars($content['first_name_placeholder']) . '" style="' . $inputStyle . 'flex:1;min-width:120px;" required>';
                $html .= '<input type="text" name="last_name" placeholder="' . htmlspecialchars($content['last_name_placeholder']) . '" style="' . $inputStyle . 'flex:1;min-width:120px;" required>';
            } else {
                $html .= '<div style="display:flex;gap:' . htmlspecialchars($design['field_gap']) . ';">';
                $html .= '<input type="text" name="first_name" placeholder="' . htmlspecialchars($content['first_name_placeholder']) . '" style="' . $inputStyle . 'flex:1;" required>';
                $html .= '<input type="text" name="last_name" placeholder="' . htmlspecialchars($content['last_name_placeholder']) . '" style="' . $inputStyle . 'flex:1;" required>';
                $html .= '</div>';
            }
        }

        // Email field with icon
        $inputWrapperStyle = 'position:relative;display:flex;align-items:center;' . ($layout === 'inline' ? 'flex:1;min-width:200px;' : 'width:100%;');
        $html .= '<div class="tb4-signup-input-wrapper" style="' . $inputWrapperStyle . '">';

        if ($design['show_email_icon'] === 'yes') {
            $html .= '<span class="tb4-signup-input-icon" style="position:absolute;left:14px;color:' . htmlspecialchars($design['icon_color']) . ';pointer-events:none;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg></span>';
            $emailInputStyle = $inputStyle . 'padding-left:44px;';
        } else {
            $emailInputStyle = $inputStyle;
        }

        $html .= '<input type="email" name="email" placeholder="' . htmlspecialchars($content['email_placeholder']) . '" style="' . $emailInputStyle . '" required>';
        $html .= '</div>';

        // Button
        $btnWidthStyle = $design['button_full_width'] === 'yes' && $layout !== 'inline' ? 'width:100%;' : '';
        $html .= '<button type="submit" class="tb4-signup-btn" style="padding:' . htmlspecialchars($design['button_padding']) . ';background:' . htmlspecialchars($design['button_bg_color']) . ';color:' . htmlspecialchars($design['button_text_color']) . ';border:none;border-radius:' . htmlspecialchars($design['button_border_radius']) . ';font-size:' . htmlspecialchars($design['button_font_size']) . ';font-weight:' . htmlspecialchars($design['button_font_weight']) . ';cursor:pointer;transition:all 0.2s;' . $btnWidthStyle . '">' . htmlspecialchars($content['button_text']) . '</button>';

        $html .= '</form>';

        // Checkbox
        if ($content['show_checkbox'] === 'yes') {
            $justifyContent = $design['text_align'] === 'center' ? 'justify-content:center;' : '';
            $html .= '<label class="tb4-signup-checkbox" style="display:flex;align-items:flex-start;gap:10px;text-align:left;font-size:14px;color:' . htmlspecialchars($design['description_color']) . ';margin-top:16px;' . $justifyContent . '">';
            $html .= '<input type="checkbox" name="consent" style="margin-top:3px;" required>';
            $html .= '<span>' . htmlspecialchars($content['checkbox_text']) . '</span>';
            $html .= '</label>';
        }

        // Privacy notice
        if ($content['show_privacy_notice'] === 'yes') {
            $html .= '<p class="tb4-signup-privacy" style="font-size:' . htmlspecialchars($design['privacy_font_size']) . ';color:' . htmlspecialchars($design['privacy_color']) . ';margin:20px 0 0 0;">';
            $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
            $html .= htmlspecialchars($content['privacy_text']) . '</p>';
        }

        // Close containers
        if ($layout === 'split') {
            $html .= '</div>';
            if ($content['image_position'] === 'right') {
                $imageUrl = $content['image_url'] ?: '';
                $splitGradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                $html .= '<div class="tb4-signup-split-image" style="background:' . ($imageUrl ? 'url(' . htmlspecialchars($imageUrl) . ')' : $splitGradient) . ';background-size:cover;background-position:center;min-height:400px;"></div>';
            }
            $html .= '</div>';
        } else {
            $html .= '</div></div>';
        }

        $html .= '</div>';

        // Custom CSS
        if ($advanced['custom_css']) {
            $html .= '<style>' . $advanced['custom_css'] . '</style>';
        }

        return $html;
    }
}
