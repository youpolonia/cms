<?php
/**
 * Contact Form Field Module (Child)
 * Single form field in contact form
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Module_ContactFormField extends JTB_Element
{
    public string $icon = 'edit-3';
    public string $category = 'forms';
    public bool $is_child = true;

    public bool $use_typography = true;
    public bool $use_background = true;
    public bool $use_spacing = true;
    public bool $use_border = true;
    public bool $use_box_shadow = false;
    public bool $use_animation = false;
    public bool $use_transform = false;
    public bool $use_position = false;
    public bool $use_filters = false;

    // === UNIFIED THEME SYSTEM ===
    protected string $module_prefix = 'contact_form_field';

    /**
     * Declarative style configuration
     */
    protected array $style_config = [
        'label_color' => [
            'property' => 'color',
            'selector' => '.jtb-field-label'
        ],
        'input_background' => [
            'property' => 'background',
            'selector' => '.jtb-field-input'
        ],
        'input_text_color' => [
            'property' => 'color',
            'selector' => '.jtb-field-input'
        ],
        'input_border_color' => [
            'property' => 'border-color',
            'selector' => '.jtb-field-input'
        ],
        'input_focus_border' => [
            'property' => 'border-color',
            'selector' => '.jtb-field-input:focus'
        ]
    ];

    public function getSlug(): string
    {
        return 'contact_form_field';
    }

    public function getName(): string
    {
        return 'Form Field';
    }

    public function getFields(): array
    {
        return [
            'field_id' => [
                'label' => 'Field ID',
                'type' => 'text',
                'default' => 'field_1'
            ],
            'field_title' => [
                'label' => 'Field Label',
                'type' => 'text',
                'default' => 'Name'
            ],
            'field_type' => [
                'label' => 'Field Type',
                'type' => 'select',
                'options' => [
                    'input' => 'Text Input',
                    'email' => 'Email',
                    'textarea' => 'Text Area',
                    'select' => 'Dropdown Select',
                    'radio' => 'Radio Buttons',
                    'checkbox' => 'Checkboxes',
                    'number' => 'Number',
                    'phone' => 'Phone',
                    'url' => 'URL',
                    'date' => 'Date',
                    'time' => 'Time',
                    'file' => 'File Upload'
                ],
                'default' => 'input'
            ],
            'required_mark' => [
                'label' => 'Required Field',
                'type' => 'toggle',
                'default' => true
            ],
            'field_placeholder' => [
                'label' => 'Placeholder Text',
                'type' => 'text',
                'default' => ''
            ],
            'select_options' => [
                'label' => 'Options (one per line)',
                'type' => 'textarea',
                'default' => "Option 1\nOption 2\nOption 3",
                'show_if' => ['field_type' => ['select', 'radio', 'checkbox']]
            ],
            'min_length' => [
                'label' => 'Min Length',
                'type' => 'number',
                'default' => 0,
                'show_if' => ['field_type' => ['input', 'textarea']]
            ],
            'max_length' => [
                'label' => 'Max Length',
                'type' => 'number',
                'default' => 0,
                'show_if' => ['field_type' => ['input', 'textarea']]
            ],
            'allowed_extensions' => [
                'label' => 'Allowed File Types',
                'type' => 'text',
                'default' => 'jpg,png,pdf',
                'show_if' => ['field_type' => 'file']
            ],
            'max_file_size' => [
                'label' => 'Max File Size (MB)',
                'type' => 'number',
                'default' => 5,
                'show_if' => ['field_type' => 'file']
            ],
            'fullwidth' => [
                'label' => 'Full Width',
                'type' => 'toggle',
                'default' => true
            ],
            'conditional_logic' => [
                'label' => 'Enable Conditional Logic',
                'type' => 'toggle',
                'default' => false
            ],
            'conditional_field' => [
                'label' => 'Show if Field',
                'type' => 'text',
                'default' => '',
                'show_if' => ['conditional_logic' => true]
            ],
            'conditional_value' => [
                'label' => 'Equals Value',
                'type' => 'text',
                'default' => '',
                'show_if' => ['conditional_logic' => true]
            ],
            // Styling
            'label_color' => [
                'label' => 'Label Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'input_background' => [
                'label' => 'Input Background',
                'type' => 'color',
                'default' => '#ffffff'
            ],
            'input_text_color' => [
                'label' => 'Input Text Color',
                'type' => 'color',
                'default' => '#333333'
            ],
            'input_border_color' => [
                'label' => 'Input Border Color',
                'type' => 'color',
                'default' => '#dddddd'
            ],
            'input_focus_border' => [
                'label' => 'Focus Border Color',
                'type' => 'color',
                'default' => '#7c3aed'
            ]
        ];
    }

    public function render(array $attrs, string $content = ''): string
    {
        // Apply default styles from design system
        $attrs = JTB_Default_Styles::mergeWithDefaults($this->getSlug(), $attrs);

        $fieldId = $this->esc($attrs['field_id'] ?? 'field_' . uniqid());
        $fieldTitle = $this->esc($attrs['field_title'] ?? 'Field');
        $fieldType = $attrs['field_type'] ?? 'input';
        $required = !empty($attrs['required_mark']);
        $placeholder = $this->esc($attrs['field_placeholder'] ?? '');
        $fullwidth = !empty($attrs['fullwidth']);

        $fieldClass = 'jtb-form-field';
        if ($fullwidth) {
            $fieldClass .= ' jtb-field-fullwidth';
        }

        $html = '<div class="' . $fieldClass . '" data-field-id="' . $fieldId . '">';

        // Label
        $html .= '<label class="jtb-field-label" for="' . $fieldId . '">';
        $html .= $fieldTitle;
        if ($required) {
            $html .= '<span class="jtb-required">*</span>';
        }
        $html .= '</label>';

        // Input based on type
        $requiredAttr = $required ? ' required' : '';

        switch ($fieldType) {
            case 'textarea':
                $html .= '<textarea id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input jtb-field-textarea" placeholder="' . $placeholder . '"' . $requiredAttr . ' rows="5"></textarea>';
                break;

            case 'select':
                $options = array_filter(array_map('trim', explode("\n", $attrs['select_options'] ?? '')));
                $html .= '<select id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input jtb-field-select"' . $requiredAttr . '>';
                $html .= '<option value="">' . ($placeholder ?: 'Select...') . '</option>';
                foreach ($options as $opt) {
                    $html .= '<option value="' . $this->esc($opt) . '">' . $this->esc($opt) . '</option>';
                }
                $html .= '</select>';
                break;

            case 'radio':
            case 'checkbox':
                $options = array_filter(array_map('trim', explode("\n", $attrs['select_options'] ?? '')));
                $inputType = $fieldType === 'checkbox' ? 'checkbox' : 'radio';
                $html .= '<div class="jtb-field-options">';
                foreach ($options as $i => $opt) {
                    $optId = $fieldId . '_' . $i;
                    $html .= '<label class="jtb-option-label">';
                    $html .= '<input type="' . $inputType . '" id="' . $optId . '" name="' . $fieldId . ($inputType === 'checkbox' ? '[]' : '') . '" value="' . $this->esc($opt) . '">';
                    $html .= '<span>' . $this->esc($opt) . '</span>';
                    $html .= '</label>';
                }
                $html .= '</div>';
                break;

            case 'file':
                $extensions = $attrs['allowed_extensions'] ?? 'jpg,png,pdf';
                $accept = '.' . implode(',.', array_map('trim', explode(',', $extensions)));
                $html .= '<input type="file" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input jtb-field-file" accept="' . $accept . '"' . $requiredAttr . '>';
                break;

            case 'email':
                $html .= '<input type="email" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input" placeholder="' . $placeholder . '"' . $requiredAttr . '>';
                break;

            case 'number':
                $html .= '<input type="number" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input" placeholder="' . $placeholder . '"' . $requiredAttr . '>';
                break;

            case 'phone':
                $html .= '<input type="tel" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input" placeholder="' . $placeholder . '"' . $requiredAttr . '>';
                break;

            case 'url':
                $html .= '<input type="url" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input" placeholder="' . $placeholder . '"' . $requiredAttr . '>';
                break;

            case 'date':
                $html .= '<input type="date" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input"' . $requiredAttr . '>';
                break;

            case 'time':
                $html .= '<input type="time" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input"' . $requiredAttr . '>';
                break;

            default: // text input
                $html .= '<input type="text" id="' . $fieldId . '" name="' . $fieldId . '" class="jtb-field-input" placeholder="' . $placeholder . '"' . $requiredAttr . '>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate CSS for Contact Form Field module
     * Base styles are in jtb-base-modules.css
     */
    public function generateCss(array $attrs, string $selector): string
    {
        $css = '';

        // Use declarative style_config system
        $css .= $this->generateStyleConfigCss($attrs, $selector);

        // Field container
        $css .= $selector . ' { margin-bottom: 20px; }' . "\n";
        $css .= $selector . '.jtb-field-fullwidth { width: 100%; }' . "\n";

        // Label
        $css .= $selector . ' .jtb-field-label { display: block; margin-bottom: 6px; font-weight: 500; }' . "\n";
        $css .= $selector . ' .jtb-required { color: #ef4444; margin-left: 2px; }' . "\n";

        // Input base styles
        $css .= $selector . ' .jtb-field-input { ';
        $css .= 'width: 100%; padding: 12px 16px; border: 1px solid; border-radius: 6px; font-size: 15px; ';
        $css .= 'transition: border-color 0.2s ease, box-shadow 0.2s ease; ';
        $css .= '}' . "\n";

        // Focus state
        $focusBorder = $attrs['input_focus_border'] ?? '#7c3aed';
        $css .= $selector . ' .jtb-field-input:focus { outline: none; box-shadow: 0 0 0 3px ' . $this->hexToRgba($focusBorder, 0.15) . '; }' . "\n";

        // Textarea
        $css .= $selector . ' .jtb-field-textarea { resize: vertical; min-height: 120px; }' . "\n";

        // Select
        $css .= $selector . ' .jtb-field-select { ';
        $css .= 'appearance: none; ';
        $css .= 'background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23666\' stroke-width=\'2\'%3E%3Cpolyline points=\'6 9 12 15 18 9\'/%3E%3C/svg%3E"); ';
        $css .= 'background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px; ';
        $css .= '}' . "\n";

        // Radio/Checkbox options
        $css .= $selector . ' .jtb-field-options { display: flex; flex-wrap: wrap; gap: 12px; }' . "\n";
        $css .= $selector . ' .jtb-option-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }' . "\n";

        // Parent class handles common styles
        $css .= parent::generateCss($attrs, $selector);

        return $css;
    }

    private function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }
}

JTB_Registry::register('contact_form_field', JTB_Module_ContactFormField::class);
