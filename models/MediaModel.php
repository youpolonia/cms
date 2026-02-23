<?php
/**
 * MediaModel - Media model for Jessie AI-CMS
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

require_once __DIR__ . '/BaseModel.php';

class MediaModel extends BaseModel
{
    protected static string $table = 'media';

    /**
     * Find media by filename
     */
    public static function findByFilename(string $filename): ?array
    {
        return static::findBy('filename', $filename);
    }

    /**
     * Find media by original name
     */
    public static function findByOriginalName(string $originalName): ?array
    {
        return static::findBy('original_name', $originalName);
    }

    /**
     * Get media by mime type
     */
    public static function byMimeType(string $mimeType, string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        return static::all(['mime_type' => $mimeType], $orderBy, $limit);
    }

    /**
     * Get images only
     */
    public static function images(string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        $sql = "SELECT * FROM `media` 
                WHERE mime_type LIKE 'image/%' 
                ORDER BY $orderBy 
                LIMIT ?";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get media by size range
     */
    public static function bySizeRange(int $minSize, int $maxSize, string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        $sql = "SELECT * FROM `media` 
                WHERE file_size >= ? AND file_size <= ? 
                ORDER BY $orderBy 
                LIMIT ?";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$minSize, $maxSize, $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get total storage used
     */
    public static function totalSize(): int
    {
        $sql = "SELECT SUM(file_size) as total FROM `media`";
        $stmt = static::db()->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get media by folder
     */
    public static function byFolder(string $folder, string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        return static::all(['folder' => $folder], $orderBy, $limit);
    }

    /**
     * Get media with thumbnails
     */
    public static function withThumbnails(string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        return static::all(['has_thumbnails' => 1], $orderBy, $limit);
    }

    /**
     * Get media with WebP versions
     */
    public static function withWebP(string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        return static::all(['has_webp' => 1], $orderBy, $limit);
    }

    /**
     * Get media by dimensions range
     */
    public static function byDimensionsRange(int $minWidth, int $maxWidth, int $minHeight, int $maxHeight, string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        $sql = "SELECT * FROM `media` 
                WHERE width >= ? AND width <= ? 
                AND height >= ? AND height <= ? 
                ORDER BY $orderBy 
                LIMIT ?";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$minWidth, $maxWidth, $minHeight, $maxHeight, $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}