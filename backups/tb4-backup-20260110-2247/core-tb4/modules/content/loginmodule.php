<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../module.php';

use Core\TB4\Modules\Module;
use function Core\TB4\Modules\esc_attr;
use function Core\TB4\Modules\esc_html;

/**
 * TB 4.0 Login Module
 * Displays a login form component with customizable styling
 */
class LoginModule extends Module
{
    public function __construct()
    {
        $this->name = 'Login';
        $this->slug = 'login';
        $this->icon = 'log-in';
        $this->category = 'content';

        $this->elements = [
            'main' => '.tb4-login',
            'form' => '.tb4-login__form',
            'title' => '.tb4-login__title',
            'field' => '.tb4-login__field',
            'label' => '.tb4-login__label',
            'input' => '.tb4-login__input',
            'button' => '.tb4-login__button',
            'options' => '.tb4-login__options',
            'remember' => '.tb4-login__remember',
            'forgot' => '.tb4-login__forgot'
        ];
    }

    public function get_content_fields(): array
    {
        return [
            'title' => [
                'label' => 'Form Title',
                'type' => 'text',
                'default' => 'Login',
                'description' => 'Title displayed above the form'
            ],
            'show_title' => [
                'label' => 'Show Title',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Display the form title'
            ],
            'username_label' => [
                'label' => 'Username Label',
                'type' => 'text',
                'default' => 'Username',
                'description' => 'Label for the username field'
            ],
            'username_placeholder' => [
                'label' => 'Username Placeholder',
                'type' => 'text',
                'default' => 'Enter username',
                'description' => 'Placeholder text for username input'
            ],
            'password_label' => [
                'label' => 'Password Label',
                'type' => 'text',
                'default' => 'Password',
                'description' => 'Label for the password field'
            ],
            'password_placeholder' => [
                'label' => 'Password Placeholder',
                'type' => 'text',
                'default' => 'Enter password',
                'description' => 'Placeholder text for password input'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Log In',
                'description' => 'Text for the submit button'
            ],
            'show_remember_me' => [
                'label' => 'Show Remember Me',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Display remember me checkbox'
            ],
            'remember_me_text' => [
                'label' => 'Remember Me Text',
                'type' => 'text',
                'default' => 'Remember me',
                'description' => 'Text for remember me checkbox'
            ],
            'show_forgot_password' => [
                'label' => 'Show Forgot Password',
                'type' => 'toggle',
                'default' => true,
                'description' => 'Display forgot password link'
            ],
            'forgot_password_text' => [
                'label' => 'Forgot Password Text',
                'type' => 'text',
                'default' => 'Forgot password?',
                'description' => 'Text for forgot password link'
            ],
            'forgot_password_url' => [
                'label' => 'Forgot Password URL',
                'type' => 'text',
                'default' => '/forgot-password',
                'description' => 'URL for forgot password link'
            ],
            'action_url' => [
                'label' => 'Form Action URL',
                'type' => 'text',
                'default' => '/login',
                'description' => 'URL where form submits to'
            ],
            'redirect_url' => [
                'label' => 'Redirect After Login',
                'type' => 'text',
                'default' => '',
                'description' => 'URL to redirect to after successful login'
            ]
        ];
    }

    public function get_design_fields(): array
    {
        return [
            'form_bg_color' => [
                'label' => 'Form Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'form_padding' => [
                'label' => 'Form Padding',
                'type' => 'text',
                'default' => '24px'
            ],
            'form_border_radius' => [
                'label' => 'Form Border Radius',
                'type' => 'text',
                'default' => '8px'
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
            'form_max_width' => [
                'label' => 'Form Max Width',
                'type' => 'text',
                'default' => '360px'
            ],
            'title_color' => [
                'label' => 'Title Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'title_font_size' => [
                'label' => 'Title Font Size',
                'type' => 'text',
                'default' => '20px'
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
            'input_text_color' => [
                'label' => 'Input Text Color',
                'type' => 'color',
                'default' => '#111827'
            ],
            'input_border_color' => [
                'label' => 'Input Border Color',
                'type' => 'color',
                'default' => '#d1d5db'
            ],
            'input_focus_border_color' => [
                'label' => 'Input Focus Border',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'input_border_radius' => [
                'label' => 'Input Border Radius',
                'type' => 'text',
                'default' => '4px'
            ],
            'input_padding' => [
                'label' => 'Input Padding',
                'type' => 'text',
                'default' => '10px 12px'
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
            'button_hover_bg_color' => [
                'label' => 'Button Hover Background',
                'type' => 'color',
                'default' => '#1d4ed8'
            ],
            'button_border_radius' => [
                'label' => 'Button Border Radius',
                'type' => 'text',
                'default' => '4px'
            ],
            'button_padding' => [
                'label' => 'Button Padding',
                'type' => 'text',
                'default' => '12px 16px'
            ],
            'link_color' => [
                'label' => 'Link Color',
                'type' => 'color',
                'default' => '#2563eb'
            ],
            'link_hover_color' => [
                'label' => 'Link Hover Color',
                'type' => 'color',
                'default' => '#1d4ed8'
            ],
            'field_spacing' => [
                'label' => 'Field Spacing',
                'type' => 'text',
                'default' => '16px'
            ],
            'alignment' => [
                'label' => 'Form Alignment',
                'type' => 'select',
                'options' => [
                    'left' => 'Left',
                    'center' => 'Center',
                    'right' => 'Right'
                ],
                'default' => 'center'
            ]
        ];
    }

    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;
    }

    /**
     * Get shadow CSS value based on preset
     */
    private function get_shadow_value(string $preset): string
    {
        return match ($preset) {
            'sm' => '0 1px 2px rgba(0,0,0,0.05)',
            'md' => '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1)',
            'lg' => '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1)',
            default => 'none'
        };
    }

    public function render(array $settings): string
    {
        // Content settings
        $title = $settings['title'] ?? 'Login';
        $showTitle = $settings['show_title'] ?? true;
        $usernameLabel = $settings['username_label'] ?? 'Username';
        $usernamePlaceholder = $settings['username_placeholder'] ?? 'Enter username';
        $passwordLabel = $settings['password_label'] ?? 'Password';
        $passwordPlaceholder = $settings['password_placeholder'] ?? 'Enter password';
        $buttonText = $settings['button_text'] ?? 'Log In';
        $showRememberMe = $settings['show_remember_me'] ?? true;
        $rememberMeText = $settings['remember_me_text'] ?? 'Remember me';
        $showForgotPassword = $settings['show_forgot_password'] ?? true;
        $forgotPasswordText = $settings['forgot_password_text'] ?? 'Forgot password?';
        $forgotPasswordUrl = $settings['forgot_password_url'] ?? '/forgot-password';
        $actionUrl = $settings['action_url'] ?? '/login';
        $redirectUrl = $settings['redirect_url'] ?? '';

        // Design settings
        $formBgColor = $settings['form_bg_color'] ?? '#ffffff';
        $formPadding = $settings['form_padding'] ?? '24px';
        $formBorderRadius = $settings['form_border_radius'] ?? '8px';
        $formShadow = $settings['form_shadow'] ?? 'md';
        $formMaxWidth = $settings['form_max_width'] ?? '360px';
        $titleColor = $settings['title_color'] ?? '#111827';
        $titleFontSize = $settings['title_font_size'] ?? '20px';
        $labelColor = $settings['label_color'] ?? '#374151';
        $labelFontSize = $settings['label_font_size'] ?? '14px';
        $inputBgColor = $settings['input_bg_color'] ?? '#f9fafb';
        $inputTextColor = $settings['input_text_color'] ?? '#111827';
        $inputBorderColor = $settings['input_border_color'] ?? '#d1d5db';
        $inputFocusBorderColor = $settings['input_focus_border_color'] ?? '#2563eb';
        $inputBorderRadius = $settings['input_border_radius'] ?? '4px';
        $inputPadding = $settings['input_padding'] ?? '10px 12px';
        $buttonBgColor = $settings['button_bg_color'] ?? '#2563eb';
        $buttonTextColor = $settings['button_text_color'] ?? '#ffffff';
        $buttonHoverBgColor = $settings['button_hover_bg_color'] ?? '#1d4ed8';
        $buttonBorderRadius = $settings['button_border_radius'] ?? '4px';
        $buttonPadding = $settings['button_padding'] ?? '12px 16px';
        $linkColor = $settings['link_color'] ?? '#2563eb';
        $linkHoverColor = $settings['link_hover_color'] ?? '#1d4ed8';
        $fieldSpacing = $settings['field_spacing'] ?? '16px';
        $alignment = $settings['alignment'] ?? 'center';

        // Generate unique ID
        $uniqueId = 'tb4-login-' . uniqid();

        // Shadow value
        $shadowValue = $this->get_shadow_value($formShadow);

        // Alignment styles
        $alignStyles = match ($alignment) {
            'center' => 'margin-left:auto;margin-right:auto;',
            'right' => 'margin-left:auto;',
            default => ''
        };

        // Build form styles
        $formStyles = implode(';', [
            'background-color:' . $formBgColor,
            'padding:' . $formPadding,
            'border-radius:' . $formBorderRadius,
            'box-shadow:' . $shadowValue,
            'max-width:' . $formMaxWidth,
            $alignStyles
        ]);

        // Build input styles
        $inputStyles = implode(';', [
            'width:100%',
            'box-sizing:border-box',
            'padding:' . $inputPadding,
            'background-color:' . $inputBgColor,
            'color:' . $inputTextColor,
            'border:1px solid ' . $inputBorderColor,
            'border-radius:' . $inputBorderRadius,
            'font-size:14px',
            'outline:none',
            'transition:border-color 0.2s, box-shadow 0.2s'
        ]);

        // Build button styles
        $buttonStyles = implode(';', [
            'width:100%',
            'padding:' . $buttonPadding,
            'background-color:' . $buttonBgColor,
            'color:' . $buttonTextColor,
            'border:none',
            'border-radius:' . $buttonBorderRadius,
            'font-size:14px',
            'font-weight:500',
            'cursor:pointer',
            'transition:background-color 0.2s, transform 0.1s'
        ]);

        // Build HTML
        $html = '<div id="' . esc_attr($uniqueId) . '" class="tb4-login">';
        $html .= '<form class="tb4-login__form" action="' . esc_attr($actionUrl) . '" method="post" style="' . $formStyles . '">';

        // Hidden redirect field
        if (!empty($redirectUrl)) {
            $html .= '<input type="hidden" name="redirect_url" value="' . esc_attr($redirectUrl) . '">';
        }

        // Title
        if ($showTitle && !empty($title)) {
            $html .= '<h3 class="tb4-login__title" style="margin:0 0 ' . $fieldSpacing . ' 0;color:' . esc_attr($titleColor) . ';font-size:' . esc_attr($titleFontSize) . ';font-weight:600;text-align:center;">' . esc_html($title) . '</h3>';
        }

        // Username field
        $html .= '<div class="tb4-login__field" style="margin-bottom:' . $fieldSpacing . ';">';
        $html .= '<label class="tb4-login__label" style="display:block;margin-bottom:6px;color:' . esc_attr($labelColor) . ';font-size:' . esc_attr($labelFontSize) . ';font-weight:500;">' . esc_html($usernameLabel) . '</label>';
        $html .= '<input type="text" name="username" class="tb4-login__input" placeholder="' . esc_attr($usernamePlaceholder) . '" style="' . $inputStyles . '" required>';
        $html .= '</div>';

        // Password field
        $html .= '<div class="tb4-login__field" style="margin-bottom:' . $fieldSpacing . ';">';
        $html .= '<label class="tb4-login__label" style="display:block;margin-bottom:6px;color:' . esc_attr($labelColor) . ';font-size:' . esc_attr($labelFontSize) . ';font-weight:500;">' . esc_html($passwordLabel) . '</label>';
        $html .= '<input type="password" name="password" class="tb4-login__input" placeholder="' . esc_attr($passwordPlaceholder) . '" style="' . $inputStyles . '" required>';
        $html .= '</div>';

        // Options row (remember me + forgot password)
        if ($showRememberMe || $showForgotPassword) {
            $html .= '<div class="tb4-login__options" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:' . $fieldSpacing . ';font-size:13px;">';

            if ($showRememberMe) {
                $html .= '<label class="tb4-login__remember" style="display:flex;align-items:center;gap:6px;color:#6b7280;cursor:pointer;">';
                $html .= '<input type="checkbox" name="remember_me" style="cursor:pointer;">';
                $html .= '<span>' . esc_html($rememberMeText) . '</span>';
                $html .= '</label>';
            } else {
                $html .= '<span></span>';
            }

            if ($showForgotPassword) {
                $html .= '<a class="tb4-login__forgot" href="' . esc_attr($forgotPasswordUrl) . '" style="color:' . esc_attr($linkColor) . ';text-decoration:none;transition:color 0.2s;">' . esc_html($forgotPasswordText) . '</a>';
            }

            $html .= '</div>';
        }

        // Submit button
        $html .= '<button type="submit" class="tb4-login__button" style="' . $buttonStyles . '">' . esc_html($buttonText) . '</button>';

        $html .= '</form>';
        $html .= '</div>';

        // Add scoped CSS for hover/focus states
        $html .= $this->generate_scoped_css($uniqueId, $settings);

        return $html;
    }

    /**
     * Generate scoped CSS for interactive states
     */
    private function generate_scoped_css(string $uniqueId, array $settings): string
    {
        $selector = '#' . $uniqueId;

        $inputFocusBorderColor = $settings['input_focus_border_color'] ?? '#2563eb';
        $buttonBgColor = $settings['button_bg_color'] ?? '#2563eb';
        $buttonHoverBgColor = $settings['button_hover_bg_color'] ?? '#1d4ed8';
        $linkColor = $settings['link_color'] ?? '#2563eb';
        $linkHoverColor = $settings['link_hover_color'] ?? '#1d4ed8';

        $css = [];

        // Input focus state
        $css[] = $selector . ' .tb4-login__input:focus {';
        $css[] = '  border-color: ' . esc_attr($inputFocusBorderColor) . ';';
        $css[] = '  box-shadow: 0 0 0 3px ' . esc_attr($inputFocusBorderColor) . '1a;';
        $css[] = '}';

        // Button hover state
        $css[] = $selector . ' .tb4-login__button:hover {';
        $css[] = '  background-color: ' . esc_attr($buttonHoverBgColor) . ';';
        $css[] = '}';

        // Button active state
        $css[] = $selector . ' .tb4-login__button:active {';
        $css[] = '  transform: scale(0.98);';
        $css[] = '}';

        // Button focus visible
        $css[] = $selector . ' .tb4-login__button:focus-visible {';
        $css[] = '  outline: 2px solid ' . esc_attr($buttonBgColor) . ';';
        $css[] = '  outline-offset: 2px;';
        $css[] = '}';

        // Link hover state
        $css[] = $selector . ' .tb4-login__forgot:hover {';
        $css[] = '  color: ' . esc_attr($linkHoverColor) . ';';
        $css[] = '  text-decoration: underline;';
        $css[] = '}';

        return '<style>' . implode("\n", $css) . '</style>';
    }
}
