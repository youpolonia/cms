<?php
class FormGenerator {
    private $fields = [];
    private $errors = [];
    private $values = [];
    private $form_id;

    public function __construct($form_id) {
        $this->form_id = $form_id;
    }

    public function addField($name, $type, $label, $options = []) {
        $this->fields[$name] = [
            'type' => $type,
            'label' => $label,
            'options' => $options,
            'required' => $options['required'] ?? false
        ];
        return $this;
    }

    public function setValues($values) {
        $this->values = $values;
        return $this;
    }

    public function setErrors($errors) {
        $this->errors = $errors;
        return $this;
    }

    public function render() {
        $output = '
<form id="' . htmlspecialchars(
$this->form_id) . '" method="post">';
        
        foreach ($this->fields as $name => $field) {
            $output .= $this->renderField($name, $field);
        }

        $output .= '
</form>';
        return $output;
    }

    private function renderField($name, $field) {
        $value = htmlspecialchars($this->values[$name] ?? '');
        $error = $this->errors[$name] ?? null;
        $required = $field['required'] ? ' required' : '';

        $field_html = '
<div class="form-group">';
        $field_html .= '<label for="' . $name . '">' . htmlspecialchars($field['label']) . '</label>';

        switch ($field['type']) {
            case 'text':
            case 'email':
            case 'password':
                $field_html .= '
<input type="' .
 $field['type'] . '" id="' . $name . '" name="' . $name . '" value="' . $value . '"' . $required . '>';
                break;
            case 'textarea':
                $field_html .= '<textarea id="' . $name . '" name="' . $name . '"' . $required . '>' . $value . '</textarea>';
                break;
            case 'select':
                $field_html .= '<select id="' . $name . '" name="' . $name . '"' . $required . '>';
                foreach ($field['options']['choices'] as $key => $label) {
                    $selected = ($value == $key) ? ' selected' : '';
                    $field_html .= '<option value="' . htmlspecialchars($key) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
                }
                $field_html .= '</select>';
                break;
            case 'checkbox':
                $checked = $value ? ' checked' : '';
                $field_html .= '
<input type="checkbox" id="' .
 $name . '" name="' . $name . '" value="1"' . $checked . $required . '>';
                break;
        }

        if ($error) {
            $field_html .= '
<span class="error">' . htmlspecialchars(
$error) . '</span>';
        }

        $field_html .= '</div>';
        return $field_html;
    }

    public function validate($data) {
        $errors = [];
        foreach ($this->fields as $name => $field) {
            if ($field['required'] && empty($data[$name])) {
                $errors[$name] = 'This field is required';
            }
        }
        $this->errors = $errors;
        return empty($errors);
    }
}
