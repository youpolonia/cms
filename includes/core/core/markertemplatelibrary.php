<?php
declare(strict_types=1);

/**
 * Marker Template Library - Handles storage and retrieval of marker templates
 */
class MarkerTemplateLibrary
{
    private static array $templates = [];
    private static string $storagePath = __DIR__ . '/../../storage/marker_templates/';

    /**
     * Load all templates from storage
     */
    public static function loadAll(): void
    {
        if (!is_dir(self::$storagePath)) {
            mkdir(self::$storagePath, 0755, true);
        }

        $files = glob(self::$storagePath . '*.json');
        foreach ($files as $file) {
            $templateName = basename($file, '.json');
            self::$templates[$templateName] = json_decode(
                file_get_contents($file),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
    }

    /**
     * Get template by name
     */
    public static function get(string $name): ?array
    {
        return self::$templates[$name] ?? null;
    }

    /**
     * Save template to storage
     */
    public static function save(string $name, array $template): bool
    {
        $filePath = self::$storagePath . $name . '.json';
        $result = file_put_contents(
            $filePath,
            json_encode($template, JSON_PRETTY_PRINT)
        );
        
        if ($result !== false) {
            self::$templates[$name] = $template;
            return true;
        }
        
        return false;
    }

    /**
     * List all available templates
     */
    public static function list(): array
    {
        return array_keys(self::$templates);
    }

    /**
     * Delete a template
     */
    public static function delete(string $name): bool
    {
        $filePath = self::$storagePath . $name . '.json';
        if (file_exists($filePath)) {
            unlink($filePath);
            unset(self::$templates[$name]);
            return true;
        }
        return false;
    }
}
