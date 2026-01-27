<?php
/**
 * Builder v2.2 Block Manager
 * Manages block lifecycle and rendering
 */
class BlockManager {
    private static $blocks = [];
    private static $registeredTypes = ['text', 'image'];

    /**
     * Register a new block type
     * @param string $type Block type identifier
     * @param array $config Configuration array
     */
    public static function registerType(string $type, array $config): void {
        if (!in_array($type, self::$registeredTypes)) {
            self::$registeredTypes[] = $type;
        }
        self::$blocks[$type] = $config;
    }

    /**
     * Get block configuration
     * @param string $type Block type
     * @return array|null Block config or null if not found
     */
    public static function getConfig(string $type): ?array {
        return self::$blocks[$type] ?? null;
    }

    /**
     * Render block HTML
     * @param string $type Block type
     * @param array $data Block data
     * @return string Rendered HTML
     */
    public static function render(string $type, array $data): string {
        $config = self::getConfig($type);
        if (!$config) {
            return '<!-- Block type not registered -->';
        }

        $html = $config['template'] ?? '';
        foreach ($data as $key => $value) {
            $html = str_replace("{{{$key}}}", htmlspecialchars($value), $html);
        }
        return $html;
    }

    /**
     * Get all registered block types
     * @return array List of registered types
     */
    public static function getRegisteredTypes(): array {
        return self::$registeredTypes;
    }
}

// Initialize with default blocks
BlockManager::registerType('text', [
    'template' => '
<div class="builder-text">{{{content}}}</div>',
    'properties' => ['content' => 'string']
]);

BlockManager::registerType('image', [
    'template' => '<img src="{{{src}}}" alt="{{{alt}}}" class="builder-image">',
    'properties' => ['src' => 'string', 'alt' => 'string']
]);
