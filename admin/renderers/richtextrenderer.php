<?php

class RichTextRenderer implements FieldRendererInterface
{
    public function render(array $field, $value, array $context): string
    {
        $name = htmlspecialchars($field['name'] ?? '', ENT_QUOTES);
        $class = htmlspecialchars($field['class'] ?? '', ENT_QUOTES);
        $id = htmlspecialchars($field['id'] ?? 'richtext-' . uniqid(), ENT_QUOTES);
        
        // Basic HTML sanitization
        $allowedTags = '<p><br><strong><em><ul><ol><li><a>';
        $value = strip_tags((string)$value, $allowedTags);
        
        return sprintf(
            '<textarea name="%s" id="%s" class="%s richtext-editor">%s</textarea>',
            $name,
            $id,
            $class,
            htmlspecialchars($value, ENT_QUOTES)
        );
    }
}
