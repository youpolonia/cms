<?php
declare(strict_types=1);

/**
 * ImageStudioCore — wraps ShopAIImages for SaaS use
 * Adds user_id tracking, saves to uploads/imagestudio/{user_id}/
 */
class ImageStudioCore {
    private \PDO $pdo;
    private int $userId;
    private string $uploadBase;
    private string $urlBase;

    public function __construct(int $userId) {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
        require_once CMS_ROOT . '/db.php';
        require_once CMS_ROOT . '/core/shop-ai-images.php';
        $this->pdo = \core\Database::connection();
        $this->userId = $userId;
        $this->uploadBase = CMS_ROOT . '/uploads/imagestudio/' . $userId;
        $this->urlBase = '/uploads/imagestudio/' . $userId;
        if (!is_dir($this->uploadBase)) {
            @mkdir($this->uploadBase, 0755, true);
        }
    }

    // ── Upload handling ──

    /**
     * Handle multipart file upload and save to user dir
     * @param array $file $_FILES['image'] element
     * @return array ['ok'=>bool, 'image'=>array|null, 'error'=>string|null]
     */
    public function uploadImage(array $file): array {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errMap = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit',
                UPLOAD_ERR_PARTIAL    => 'File upload incomplete',
                UPLOAD_ERR_NO_FILE    => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Server temp dir missing',
                UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            ];
            return ['ok' => false, 'error' => $errMap[$file['error'] ?? 0] ?? 'Upload failed'];
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed, true)) {
            return ['ok' => false, 'error' => 'Unsupported format. Use JPG, PNG, WebP or GIF.'];
        }

        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };

        $origName = basename($file['name']);
        $filename = 'upload_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $this->uploadBase . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['ok' => false, 'error' => 'Failed to save uploaded file'];
        }

        $info = @getimagesize($destPath);
        $width = $info[0] ?? 0;
        $height = $info[1] ?? 0;

        $record = $this->saveRecord($filename, $origName, $destPath, $this->urlBase . '/' . $filename, filesize($destPath), $mime, $width, $height, 'upload');
        return ['ok' => true, 'image' => $record];
    }

    // ── Remove Background ──

    public function removeBackground(string $imagePath): array {
        $result = \ShopAIImages::removeBackground($imagePath);
        if (!$result['ok']) return $result;

        // Move output to user dir
        $moved = $this->moveToUserDir($result['absolute_path'], 'nobg_');
        if (!$moved['ok']) return $moved;

        $info = @getimagesize($moved['path']);
        $record = $this->saveRecord(
            $moved['filename'], basename($imagePath), $moved['path'], $moved['url'],
            filesize($moved['path']), $info['mime'] ?? 'image/png',
            $info[0] ?? 0, $info[1] ?? 0, 'remove_bg', null, null, null, 1
        );
        return ['ok' => true, 'image' => $record];
    }

    // ── Alt Text ──

    public function generateAltText(string $imagePath, string $productName = ''): array {
        $result = \ShopAIImages::generateAltText($imagePath, $productName);
        if (!$result['ok']) return $result;

        // Save as alt_text type record (no new image file)
        $stmt = $this->pdo->prepare(
            "INSERT INTO imagestudio_images (user_id, filename, file_path, file_url, type, alt_text, status, credits_used)
             VALUES (?, ?, ?, ?, 'alt_text', ?, 'completed', 1)"
        );
        $stmt->execute([$this->userId, basename($imagePath), $imagePath, '', $result['alt']]);
        $id = (int)$this->pdo->lastInsertId();

        return [
            'ok'  => true,
            'id'  => $id,
            'alt' => $result['alt'],
            'raw_caption' => $result['raw_caption'] ?? '',
        ];
    }

    // ── Enhance ──

    public function enhanceImage(string $imagePath, string $prompt = ''): array {
        $result = \ShopAIImages::enhanceImage($imagePath, $prompt);
        if (!$result['ok']) return $result;

        $moved = $this->moveToUserDir($result['absolute_path'], 'enhanced_');
        if (!$moved['ok']) return $moved;

        $info = @getimagesize($moved['path']);
        $record = $this->saveRecord(
            $moved['filename'], basename($imagePath), $moved['path'], $moved['url'],
            filesize($moved['path']), $info['mime'] ?? 'image/png',
            $info[0] ?? 0, $info[1] ?? 0, 'enhanced', null, $prompt, null, 2
        );
        return ['ok' => true, 'image' => $record];
    }

    // ── Generate ──

    public function generateImage(string $prompt, string $style = 'photo'): array {
        $stylePrompts = [
            'photo'        => 'photorealistic, high resolution',
            'illustration' => 'digital illustration, vector art style',
            '3d'           => '3D render, octane render, cinema 4D',
            'flat'         => 'flat design, minimal, clean vector',
        ];
        $fullPrompt = $prompt . ', ' . ($stylePrompts[$style] ?? $stylePrompts['photo']);

        $result = \ShopAIImages::generateProductImage($fullPrompt);
        if (!$result['ok']) return $result;

        $moved = $this->moveToUserDir($result['absolute_path'], 'gen_');
        if (!$moved['ok']) return $moved;

        $info = @getimagesize($moved['path']);
        $record = $this->saveRecord(
            $moved['filename'], '', $moved['path'], $moved['url'],
            filesize($moved['path']), $info['mime'] ?? 'image/png',
            $info[0] ?? 0, $info[1] ?? 0, 'generated', null, $prompt,
            json_encode(['style' => $style]), 3
        );
        return ['ok' => true, 'image' => $record];
    }

    // ── Get user images ──

    public function getImages(int $limit = 50, int $offset = 0, ?string $type = null): array {
        $sql = "SELECT * FROM imagestudio_images WHERE user_id = ?";
        $params = [$this->userId];
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getImage(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM imagestudio_images WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function deleteImage(int $id): bool {
        $img = $this->getImage($id);
        if (!$img) return false;
        // Remove file
        if (!empty($img['file_path']) && file_exists($img['file_path'])) {
            @unlink($img['file_path']);
        }
        $stmt = $this->pdo->prepare("DELETE FROM imagestudio_images WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->rowCount() > 0;
    }

    public function getStats(): array {
        $stmt = $this->pdo->prepare(
            "SELECT type, COUNT(*) as cnt FROM imagestudio_images WHERE user_id = ? GROUP BY type"
        );
        $stmt->execute([$this->userId]);
        $byType = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $byType[$r['type']] = (int)$r['cnt'];
        }
        $stmt2 = $this->pdo->prepare("SELECT COUNT(*) FROM imagestudio_images WHERE user_id = ?");
        $stmt2->execute([$this->userId]);
        return ['total' => (int)$stmt2->fetchColumn(), 'by_type' => $byType];
    }

    // ── Helpers ──

    private function moveToUserDir(string $sourcePath, string $prefix): array {
        if (!file_exists($sourcePath)) {
            return ['ok' => false, 'error' => 'Source file not found'];
        }
        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'png';
        $filename = $prefix . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $this->uploadBase . '/' . $filename;

        if (!rename($sourcePath, $dest) && !copy($sourcePath, $dest)) {
            return ['ok' => false, 'error' => 'Failed to move file to user directory'];
        }
        if (file_exists($sourcePath) && $sourcePath !== $dest) {
            @unlink($sourcePath);
        }
        return ['ok' => true, 'path' => $dest, 'url' => $this->urlBase . '/' . $filename, 'filename' => $filename];
    }

    private function saveRecord(
        string $filename, string $origName, string $filePath, string $fileUrl,
        int $fileSize, string $mime, int $width, int $height, string $type,
        ?int $sourceId = null, ?string $prompt = null, ?string $meta = null, int $credits = 0
    ): array {
        $stmt = $this->pdo->prepare(
            "INSERT INTO imagestudio_images
             (user_id, filename, original_filename, file_path, file_url, file_size, mime_type, width, height, type, source_image_id, prompt, metadata_json, status, credits_used)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)"
        );
        $stmt->execute([
            $this->userId, $filename, $origName, $filePath, $fileUrl,
            $fileSize, $mime, $width, $height, $type, $sourceId, $prompt, $meta, $credits
        ]);
        $id = (int)$this->pdo->lastInsertId();

        return [
            'id' => $id, 'filename' => $filename, 'original_filename' => $origName,
            'file_url' => $fileUrl, 'file_size' => $fileSize, 'mime_type' => $mime,
            'width' => $width, 'height' => $height, 'type' => $type,
            'prompt' => $prompt, 'credits_used' => $credits,
        ];
    }
}
