<?php

namespace Admin\Renderers;

class FieldRendererFactory
{
    /**
     * Get the appropriate renderer for a field type
     *
     * @param string $fieldType
     * @return FieldRendererInterface
     * @throws \InvalidArgumentException When field type is not supported
     */
    public static function getRenderer(string $fieldType): FieldRendererInterface
    {
        return match ($fieldType) {
            'text' => new TextRenderer(),
            'rich_text' => new RichTextRenderer(),
            'image' => new ImageRenderer(),
            'datetime' => new DateTimeRenderer(),
            default => throw new \InvalidArgumentException("No renderer available for field type: $fieldType")
        };
    }
}

interface FieldRendererInterface
{
    public function render(array $fieldData): string;
}
