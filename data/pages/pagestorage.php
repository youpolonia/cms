<?php

class PageStorage {
    private static string $storagePath = __DIR__ . '/';

    public static function savePageData(string $pageId, array $data): bool {
        self::ensureDirectoryExists();
        $filePath = self::$storagePath . $pageId . '.json';
        
        try {
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            if ($jsonData === false) {
                throw new \RuntimeException('JSON encoding failed');
            }
            
            $result = file_put_contents($filePath, $jsonData);
            return $result !== false;
        } catch (\Exception $e) {
            error_log("Failed to save page data: " . $e->getMessage());
            return false;
        }
    }

    public static function loadPageData(string $pageId): ?array {
        $filePath = self::$storagePath . $pageId . '.json';
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                return null;
            }
            
            $data = json_decode($content, true);
            return is_array($data) ? $data : null;
        } catch (\Exception $e) {
            error_log("Failed to load page data: " . $e->getMessage());
            return null;
        }
    }

    public static function listPages(): array {
        self::ensureDirectoryExists();
        $files = glob(self::$storagePath . '*.json');
        $pages = [];
        
        foreach ($files as $file) {
            $pageId = basename($file, '.json');
            $pages[] = $pageId;
        }
        
        return $pages;
    }

    private static function ensureDirectoryExists(): void {
        if (!is_dir(self::$storagePath)) {
            mkdir(self::$storagePath, 0755, true);
        }
    }
}
