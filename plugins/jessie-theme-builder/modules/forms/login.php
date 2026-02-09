<?php
/**
 * Login Module
 * User login form
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_Login extends JTB_Element
{
    public string $icon = 'lock';
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
    protected string $module_prefix = 'login';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'form_field_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-form-field input[type="text"], .jtb-form-field input[type="password"]'
        ],
        'form_field_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-form-field input[type="text"], .jtb-form-field input[type="password"]'
        ],
        'form_field_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-form-field input[type="text"], .jtb-form-field input[type="password"]',
            'hover' => true
        ],
        'button_bg_color' => [
            'property' => 'background-color',
            'selector' => '.jtb-login-submit',
            'hover' => true
        ],
        'button_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-login-submit',
            'hover' => true
        ]
    ];

    public function getSlug(): string
    {
        return 'login';
    }

    public function getName(): string
    {
        return 'Login';
    }

    public function getFields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Login'
            ],
            'current_page_redirect' => [
                'label' => 'Redirect to Current Page',
                'type' => 'toggle',
                'default' => true
            ],
            'redirect_url' => [
                'label' => 'Custom Redirect URL',
                'type' => 'text',
                'show_if' => ['current_page_redirect' => false]
            ],
            // Labels
            'username_label' => [
                'label' => 'Username Label',
                'type' => 'text',
                'default' => 'Username'
            ],
            'password_label' => [
                'label' => 'Password Label',
                'type' => 'text',
                'default' => 'Password'
            ],
            'button_text' => [
                'label' => 'Button Text',
                'type' => 'text',
                'default' => 'Login'
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

        $title = $this->esc($attrs['title'] ?? 'Login');
        $usernameLabel = $this->esc($attrs['username_label'] ?? 'Username');
        $passwordLabel = $this->esc($attrs['password_label'] ?? 'Password');
        $buttonText = $this->esc($attrs['button_text'] ?? 'Login');

        $formId = 'jtb-login-form-' . $this->generateId();

        $innerHtml = '<div class="jtb-login-container">';

        if (!empty($title)) {
            $innerHtml .= '<h3 class="jtb-login-title">' . $title . '</h3>';
        }

        $innerHtml .= '<form class="jtb-login-form" id="' . $formId . '" method="post">';

        // Username
        $innerHtml .= '<div class="jtb-form-field">';
        $innerHtml .= '<label for="' . $formId . '-username">' . $usernameLabel . '</label>';
        $innerHtml .= '<input type="text" id="' . $formId . '-username" name="username" placeholder="' . $usernameLabel . '" required>';
        $innerHtml .= '</div>';

        // Password
        $innerHtml .= '<div class="jtb-form-field">';
        $innerHtml .= '<label for="' . $formId . '-password">' . $passwordLabel . '</label>';
        $innerHtml .= '<input type="password" id="' . $formId . '-password" name="password" placeholder="' . $passwordLabel . '" required>';
        $innerHtml .= '</div>';

        // Remember me
        $innerHtml .= '<div class="jtb-form-field jtb-form-checkbox">';
        $innerHtml .= '<label><input type="checkbox" name="remember"> Remember Me</label>';
        $innerHtml .= '</div>';

        // Submit
        $innerHtml .= '<div class="jtb-form-field jtb-form-submit">';
        $innerHtml .= '<button type="submit" class="jtb-button jtb-login-submit">' . $buttonText . '</button>';
        $innerHtml .= '</div>';

        // Links
        $innerHtml .= '<div class="jtb-login-links">';
        $innerHtml .= '<a href="#" class="jtb-forgot-password">Forgot Password?</a>';
        $innerHtml .= '</div>';

        $innerHtml .= '</form>';
        $innerHtml .= '</div>';

        return $this->renderWrapper($innerHtml, $attrs);
    }

    /**
     * Generate CSS for Login module
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

        $css .= $selector . ' .jtb-form-field input[type="text"], ' . $selector . ' .jtb-form-field input[type="password"] { ';
        $css .= 'width: 100%; padding: 12px 15px; box-sizing: border-box; font-size: 14px; border: 1px solid; ';
        $css .= '}' . "\n";

        // Checkbox
        $css .= $selector . ' .jtb-form-checkbox label { display: flex; align-items: center; gap: 8px; font-weight: normal; }' . "\n";

        // Button base styles
        $css .= $selector . ' .jtb-login-submit { width: 100%; border: none; padding: 12px 30px; cursor: pointer; font-size: 14px; transition: all 0.3s ease; }' . "\n";

        // Links
        $css .= $selector . ' .jtb-login-links { text-align: center; margin-top: 15px; }' . "\n";
        $css .= $selector . ' .jtb-login-links a { font-size: 13px; color: #666; }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }
}

JTB_Registry::register('login', JTB_Module_Login::class);
