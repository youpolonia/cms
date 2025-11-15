<?php
class BlockManager {
    public static function registerBlockType(string $type, array $config): void {
        // Dispatch plugin hook before registration
        PluginManager::dispatch('onRegisterBlock', [
            'type' => $type,
            'config' => $config
        ]);

        // Original registration logic
        self::$blockTypes[$type] = $config;
    }

    public static function getBlockConfig(string $type): ?array {
        $config = self::$blockTypes[$type] ?? null;
        
        // Allow plugins to modify config
        return PluginManager::dispatchFilter('onGetBlockConfig', $config, [
            'type' => $type
        ]);
    }
}
