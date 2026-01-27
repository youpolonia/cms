<?php

class TextRenderer implements FieldRendererInterface
{
    public function render(array $field, $value, array $context): string
    {
        $name = htmlspecialchars($field['name'] ?? '', ENT_QUOTES);
        $value = htmlspecialchars((string)$value, ENT_QUOTES);
        $class = htmlspecialchars($field['class'] ?? '', ENT_QUOTES);
        
        return sprintf(
            '<input type="text" name="%s" value="%s" class="%s" />',
            $name,
            $value,
            $class
        );
    }
}
