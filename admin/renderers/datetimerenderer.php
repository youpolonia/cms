<?php

class DateTimeRenderer implements FieldRendererInterface
{
    public function render(array $field, $value, array $context): string
    {
        $name = htmlspecialchars($field['name'] ?? '', ENT_QUOTES);
        $class = htmlspecialchars($field['class'] ?? '', ENT_QUOTES);
        $type = htmlspecialchars($field['type'] ?? 'datetime-local', ENT_QUOTES);
        
        // Format value for HTML input
        $formattedValue = '';
        if ($value instanceof DateTimeInterface) {
            $formattedValue = $value->format($type === 'date' ? 'Y-m-d' : 'Y-m-d\TH:i');
        } elseif (!empty($value)) {
            $formattedValue = htmlspecialchars((string)$value, ENT_QUOTES);
        }

        return sprintf(
            '<input type="%s" name="%s" value="%s" class="%s" />',
            $type,
            $name,
            $formattedValue,
            $class
        );
    }
}
