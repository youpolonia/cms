<?php

class ImageRenderer implements FieldRendererInterface
{
    public function render(array $field, $value, array $context): string
    {
        $name = htmlspecialchars($field['name'] ?? '', ENT_QUOTES);
        $class = htmlspecialchars($field['class'] ?? '', ENT_QUOTES);
        $accept = htmlspecialchars($field['accept'] ?? 'image/*', ENT_QUOTES);
        
        $html = sprintf(
            '<input type="file" name="%s" accept="%s" class="%s" />',
            $name,
            $accept,
            $class
        );

        if ($value) {
            $html .= sprintf(
                '<div class="image-preview"><img src="%s" alt="Preview" style="max-width: 200px;"/></div>',
                htmlspecialchars((string)$value, ENT_QUOTES)
            );
        }

        return $html;
    }
}
