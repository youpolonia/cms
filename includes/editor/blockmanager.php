<?php
class BlockManager {
    private static $registeredBlocks = [];

    public static function registerBlockType(string $id, string $label, string $handlerClass): void {
        self::$registeredBlocks[$id] = [
            'label' => $label,
            'handler' => $handlerClass
        ];
    }

    public static function getBlockTypes(): array {
        return self::$registeredBlocks;
    }

    public static function renderBlock(array $blockData): string {
        if (!isset(self::$registeredBlocks[$blockData['type']])) {
            return '';
        }

        $handlerClass = self::$registeredBlocks[$blockData['type']]['handler'];
        if (!class_exists($handlerClass)) {
            return '';
        }

        return call_user_func([$handlerClass, 'render'], $blockData);
    }

    public static function saveBlock(array $blockData): bool {
        if (!isset(self::$registeredBlocks[$blockData['type']])) {
            return false;
        }

        $handlerClass = self::$registeredBlocks[$blockData['type']]['handler'];
        if (!class_exists($handlerClass)) {
            return false;
        }

        return call_user_func([$handlerClass, 'save'], $blockData);
    }
}

// Register core block types
BlockManager::registerBlockType('text', 'Text Block', 'TextBlockHandler');
BlockManager::registerBlockType('image', 'Image Block', 'ImageBlockHandler');
BlockManager::registerBlockType('video', 'Video Block', 'VideoBlockHandler');
BlockManager::registerBlockType('ai', 'AI Layout Generator', 'AIBlockHandler');
