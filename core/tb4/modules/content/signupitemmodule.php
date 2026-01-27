<?php
namespace Core\TB4\Modules\Content;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

class SignupItemModule extends ChildModule
{
    protected string $name = 'Signup Field';
    protected string $slug = 'signup_item';
    protected string $icon = 'user-plus';
    protected string $category = 'content';
    protected string $type = 'child';
    protected ?string $parent_slug = 'signup';

    public function get_content_fields(): array
    {
        return [
            'field_type' => [
                'label' => 'Field Type',
                'type' => 'select',
                'options' => [
                    'email' => 'Email',
                    'text' => 'Text',
                    'name' => 'Name',
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'phone' => 'Phone',
                    'checkbox' => 'Checkbox (GDPR/Terms)'
                ],
                'default' => 'email'
            ],
            'field_label' => [
                'label' => 'Field Label',
                'type' => 'text',
                'default' => 'Email Address'
            ],
            'placeholder' => [
                'label' => 'Placeholder',
                'type' => 'text',
                'default' => 'Enter your email'
            ],
            'required' => [
                'label' => 'Required',
                'type' => 'toggle',
                'default' => 'yes'
            ],
            'checkbox_text' => [
                'label' => 'Checkbox Text',
                'type' => 'textarea',
                'default' => 'I agree to receive marketing emails',
                'description' => 'Only for checkbox field type'
            ],
            'show_label' => [
                'label' => 'Show Label',
                'type' => 'toggle',
                'default' => 'yes'
            ]
        ];
    }

    public function render(array $data = []): string
    {
        $type = $data['content']['field_type'] ?? 'email';
        $label = $data['content']['field_label'] ?? 'Email';
        $placeholder = $data['content']['placeholder'] ?? '';
        $required = ($data['content']['required'] ?? 'yes') === 'yes';
        $showLabel = ($data['content']['show_label'] ?? 'yes') === 'yes';
        $checkboxText = $data['content']['checkbox_text'] ?? '';

        $reqAttr = $required ? ' required' : '';
        $reqMark = $required ? '<span style="color:#ef4444;margin-left:2px;">*</span>' : '';

        $html = '<div class="tb4-signup-field" style="margin-bottom:12px;">';

        if ($type === 'checkbox') {
            $html .= '<label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;">';
            $html .= '<input type="checkbox"' . $reqAttr . ' style="margin-top:3px;"/>';
            $html .= '<span style="font-size:14px;color:#374151;">' . htmlspecialchars($checkboxText) . $reqMark . '</span>';
            $html .= '</label>';
        } else {
            if ($showLabel) {
                $html .= '<label style="display:block;margin-bottom:6px;font-weight:500;font-size:14px;color:#374151;">' . htmlspecialchars($label) . $reqMark . '</label>';
            }
            $inputType = ($type === 'email') ? 'email' : (($type === 'phone') ? 'tel' : 'text');
            $html .= '<input type="' . $inputType . '" placeholder="' . htmlspecialchars($placeholder) . '"' . $reqAttr . ' style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;"/>';
        }

        $html .= '</div>';
        return $html;
    }
}
