<?php

namespace Admin\Renderers;

class ContentRenderer
{
    /**
     * Render content by coordinating field renderers
     *
     * @param array $contentData Array of field data to render
     * @param array $context Additional rendering context
     * @return string Combined rendered output
     */
    public function render(array $contentData, array $context = []): string
    {
        $output = '';
        
        foreach ($contentData as $field) {
            if (!isset($field['type'])) {
                continue; // Skip invalid fields
            }

            try {
                $renderer = FieldRendererFactory::getRenderer($field['type']);
                $output .= $renderer->render(array_merge($field, $context));
            } catch (\InvalidArgumentException $e) {
                // Log error but continue with other fields
                error_log("Field render error: " . $e->getMessage());
                continue;
            }
        }

        return $output;
    }
}
