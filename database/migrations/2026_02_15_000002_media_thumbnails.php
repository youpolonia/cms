<?php
/**
 * Migration: Add media thumbnails columns
 * 
 * Adds columns to track generated thumbnails and WebP variants.
 */

return new class {
    public string $name = '2026_02_15_000002_media_thumbnails';

    public function up(\PDO $pdo): void
    {
        // Check if columns exist first
        $cols = $pdo->query("DESCRIBE media")->fetchAll(\PDO::FETCH_COLUMN);

        if (!in_array('has_thumbnails', $cols)) {
            $pdo->exec("ALTER TABLE media ADD COLUMN has_thumbnails TINYINT(1) NOT NULL DEFAULT 0 AFTER mime_type");
        }
        if (!in_array('has_webp', $cols)) {
            $pdo->exec("ALTER TABLE media ADD COLUMN has_webp TINYINT(1) NOT NULL DEFAULT 0 AFTER has_thumbnails");
        }
        if (!in_array('width', $cols)) {
            $pdo->exec("ALTER TABLE media ADD COLUMN width INT UNSIGNED NULL AFTER has_webp");
        }
        if (!in_array('height', $cols)) {
            $pdo->exec("ALTER TABLE media ADD COLUMN height INT UNSIGNED NULL AFTER width");
        }
        if (!in_array('file_size', $cols)) {
            $pdo->exec("ALTER TABLE media ADD COLUMN file_size BIGINT UNSIGNED NULL AFTER height");
        }
    }

    public function down(\PDO $pdo): void
    {
        $cols = $pdo->query("DESCRIBE media")->fetchAll(\PDO::FETCH_COLUMN);
        foreach (['has_thumbnails', 'has_webp', 'width', 'height', 'file_size'] as $col) {
            if (in_array($col, $cols)) {
                $pdo->exec("ALTER TABLE media DROP COLUMN $col");
            }
        }
    }
};
