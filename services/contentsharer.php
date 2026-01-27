<?php
class ContentSharer {
    public static function share($tenantId, $content) {
        $sharedId = self::generateSharedId($tenantId, $content['id']);
        self::storeSharedContent($sharedId, $content);
        return $sharedId;
    }

    private static function generateSharedId($tenantId, $contentId) {
        return $tenantId . '_' . $contentId . '_' . time();
    }

    private static function storeSharedContent($sharedId, $content) {
        $storagePath = __DIR__ . '/../../storage/shared_content/';
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        file_put_contents(
            $storagePath . $sharedId . '.json',
            json_encode($content)
        );
    }
}
