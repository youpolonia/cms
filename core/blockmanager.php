<?php
class BlockManager {
    const BLOCKS_DIR = __DIR__ . '/../../data/custom-blocks/blocks';
    const METADATA_DIR = __DIR__ . '/../../data/custom-blocks/metadata';

    public static function saveBlock(string $name, string $content, array $metadata = []): bool {
        $blockFile = self::BLOCKS_DIR . '/' . $name . '.html';
        $metadataFile = self::METADATA_DIR . '/' . $name . '.json';

        if (!file_put_contents($blockFile, $content)) {
            return false;
        }

        $metadata = array_merge([
            'name' => $name,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s')
        ], $metadata);

        return file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT)) !== false;
    }

    public static function getBlock(string $name): ?array {
        $blockFile = self::BLOCKS_DIR . '/' . $name . '.html';
        $metadataFile = self::METADATA_DIR . '/' . $name . '.json';

        if (!file_exists($blockFile) || !file_exists($metadataFile)) {
            return null;
        }

        return [
            'content' => file_get_contents($blockFile),
            'metadata' => json_decode(file_get_contents($metadataFile), true)
        ];
    }

    public static function listBlocks(): array {
        $blocks = [];
        $files = glob(self::BLOCKS_DIR . '/*.html');

        foreach ($files as $file) {
            $name = basename($file, '.html');
            $metadataFile = self::METADATA_DIR . '/' . $name . '.json';

            if (file_exists($metadataFile)) {
                $blocks[$name] = json_decode(file_get_contents($metadataFile), true);
            }
        }

        return $blocks;
    }
}
