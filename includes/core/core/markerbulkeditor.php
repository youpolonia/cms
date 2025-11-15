<?php
declare(strict_types=1);

/**
 * Marker Bulk Editor - Handles bulk operations on marker templates
 */
class MarkerBulkEditor
{
    /**
     * Apply bulk updates to multiple templates
     */
    public static function updateTemplates(
        array $templateNames,
        array $updates,
        bool $validate = true
    ): array {
        $results = [];
        
        foreach ($templateNames as $name) {
            $template = MarkerTemplateLibrary::get($name);
            if (!$template) {
                $results[$name] = 'Template not found';
                continue;
            }

            $updated = self::applyUpdates($template, $updates);
            
            if ($validate) {
                $errors = MarkerValidationEngine::validateTemplate($updated);
                if (!empty($errors)) {
                    $results[$name] = $errors;
                    continue;
                }
            }

            $success = MarkerTemplateLibrary::save($name, $updated);
            $results[$name] = $success ? 'Updated' : 'Save failed';
        }

        return $results;
    }

    private static function applyUpdates(array $template, array $updates): array
    {
        foreach ($updates as $path => $value) {
            $parts = explode('.', $path);
            $current = &$template;
            
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
            
            $current = $value;
        }

        return $template;
    }

    /**
     * Bulk delete templates
     */
    public static function deleteTemplates(array $templateNames): array
    {
        $results = [];
        
        foreach ($templateNames as $name) {
            $results[$name] = MarkerTemplateLibrary::delete($name) 
                ? 'Deleted' 
                : 'Delete failed';
        }

        return $results;
    }
}
