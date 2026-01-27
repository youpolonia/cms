<?php
namespace Core\TB4\Modules\Interactive;

require_once __DIR__ . '/../childmodule.php';

use Core\TB4\Modules\ChildModule;

class ContactItemModule extends ChildModule
{
    protected string $name = 'Contact Form Field';
    protected string $slug = 'contact_item';
    protected string $icon = 'text-cursor-input';
    protected string $category = 'interactive';
    protected string $type = 'child';
    protected ?string $parent_slug = 'contact';

    public function get_content_fields(): array
    {
        return [
            'field_type' => [
                'label' => 'Field Type',
                'type' => 'select',
                'options' => [
                    'text' => 'Text',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'textarea' => 'Textarea',
                    'select' => 'Dropdown',
                    'checkbox' => 'Checkbox',
                    'radio' => 'Radio'
                ],
                'default' => 'text'
            ],
            'field_label' => [
                'label' => 'Field Label',
                'type' => 'text',
                'default' => 'Field Label'
            ],
            'placeholder' => [
                'label' => 'Placeholder',
                'type' => 'text',
                'default' => ''
            ],
            'required' => [
                'label' => 'Required',
                'type' => 'toggle',
                'default' => 'no'
            ],
            'field_options' => [
                'label' => 'Options (one per line)',
                'type' => 'textarea',
                'default' => '',
                'description' => 'For select, checkbox, radio fields'
            ],
            'min_length' => [
                'label' => 'Min Length',
                'type' => 'number',
                'default' => ''
            ],
            'max_length' => [
                'label' => 'Max Length',
                'type' => 'number',
                'default' => ''
            ]
        ];
    }


    public function render(array $data = []): string
    {
        $type = $data['content']['field_type'] ?? 'text';
        $label = $data['content']['field_label'] ?? 'Field';
        $placeholder = $data['content']['placeholder'] ?? '';
        $required = ($data['content']['required'] ?? 'no') === 'yes';
        $reqAttr = $required ? ' required' : '';
        $reqMark = $required ? '<span style="color:#ef4444;">*</span>' : '';

        $html = '<div class="tb4-contact-field">';
        $html .= '<label style="display:block;margin-bottom:6px;font-weight:500;">' . htmlspecialchars($label) . $reqMark . '</label>';

        if ($type === 'textarea') {
            $html .= '<textarea placeholder="' . htmlspecialchars($placeholder) . '"' . $reqAttr . ' style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;min-height:100px;"></textarea>';
        } elseif ($type === 'select') {
            $html .= '<select' . $reqAttr . ' style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;"><option>' . htmlspecialchars($placeholder ?: 'Select...') . '</option></select>';
        } elseif ($type === 'checkbox' || $type === 'radio') {
            $html .= '<label style="display:flex;align-items:center;gap:8px;"><input type="' . $type . '"' . $reqAttr . '/> ' . htmlspecialchars($placeholder ?: 'Option') . '</label>';
        } else {
            $html .= '<input type="' . htmlspecialchars($type) . '" placeholder="' . htmlspecialchars($placeholder) . '"' . $reqAttr . ' style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;"/>';
        }

        $html .= '</div>';
        return $html;
    }
}
