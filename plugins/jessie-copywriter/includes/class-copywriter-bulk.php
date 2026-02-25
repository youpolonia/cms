<?php
namespace Plugins\JessieCopywriter;

/**
 * Bulk copy generation — batch processing for multiple products
 */
class CopywriterBulk {
    private \PDO $pdo;

    public function __construct() {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
    }

    /**
     * Create a batch from array of products
     */
    public function createBatch(int $userId, array $products, string $platform = 'general', string $tone = 'professional', ?int $brandId = null): array {
        if (empty($products)) {
            return ['success' => false, 'error' => 'No products provided'];
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO copywriter_batches (user_id, brand_id, platform, tone, total_items, status) VALUES (?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([$userId, $brandId, $platform, $tone, count($products)]);
        $batchId = (int)$this->pdo->lastInsertId();

        // Create content records in pending state
        $insert = $this->pdo->prepare(
            "INSERT INTO copywriter_content (user_id, batch_id, brand_id, product_name, product_features, product_category, platform, tone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
        );

        foreach ($products as $p) {
            $insert->execute([
                $userId, $batchId, $brandId,
                trim($p['name'] ?? ''),
                trim($p['features'] ?? ''),
                trim($p['category'] ?? 'other'),
                $platform, $tone
            ]);
        }

        return ['success' => true, 'batch_id' => $batchId, 'total' => count($products)];
    }

    /**
     * Process all pending items in a batch
     */
    public function processBatch(int $batchId, int $userId): array {
        $batch = $this->getBatch($batchId, $userId);
        if (!$batch) return ['success' => false, 'error' => 'Batch not found'];
        if ($batch['status'] === 'completed') return ['success' => false, 'error' => 'Batch already completed'];

        $this->pdo->prepare("UPDATE copywriter_batches SET status = 'processing' WHERE id = ?")->execute([$batchId]);

        $items = $this->pdo->prepare("SELECT * FROM copywriter_content WHERE batch_id = ? AND user_id = ? AND status = 'pending' ORDER BY id");
        $items->execute([$batchId, $userId]);
        $pending = $items->fetchAll(\PDO::FETCH_ASSOC);

        $core = new CopywriterCore();
        $completed = 0;
        $failed = 0;

        foreach ($pending as $item) {
            $result = $core->generate($userId, [
                'name' => $item['product_name'],
                'features' => $item['product_features'],
                'category' => $item['product_category'],
                'platform' => $item['platform'],
                'tone' => $item['tone'],
                'brand_id' => $item['brand_id'] ?? 0,
            ]);

            if ($result['success']) {
                $completed++;
            } else {
                $failed++;
            }
        }

        $finalStatus = $failed === count($pending) ? 'failed' : 'completed';
        $this->pdo->prepare("UPDATE copywriter_batches SET status = ?, completed_items = ?, failed_items = ? WHERE id = ?")
            ->execute([$finalStatus, $completed, $failed, $batchId]);

        return ['success' => true, 'completed' => $completed, 'failed' => $failed, 'status' => $finalStatus];
    }

    /**
     * Get batch details
     */
    public function getBatch(int $batchId, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM copywriter_batches WHERE id = ? AND user_id = ?");
        $stmt->execute([$batchId, $userId]);
        $batch = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$batch) return null;

        $items = $this->pdo->prepare("SELECT id, product_name, platform, tone, title, status, created_at FROM copywriter_content WHERE batch_id = ? AND user_id = ? ORDER BY id");
        $items->execute([$batchId, $userId]);
        $batch['items'] = $items->fetchAll(\PDO::FETCH_ASSOC);
        return $batch;
    }

    /**
     * List user's batches
     */
    public function getBatches(int $userId, int $limit = 20): array {
        $stmt = $this->pdo->prepare("SELECT * FROM copywriter_batches WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Export batch as CSV
     */
    public function exportBatchCsv(int $batchId, int $userId): ?string {
        $batch = $this->getBatch($batchId, $userId);
        if (!$batch) return null;

        $items = $this->pdo->prepare("SELECT product_name, platform, tone, title, description, bullet_points, meta_title, meta_description, tags, status FROM copywriter_content WHERE batch_id = ? AND user_id = ?");
        $items->execute([$batchId, $userId]);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Product', 'Platform', 'Tone', 'Title', 'Description', 'Bullet Points', 'Meta Title', 'Meta Description', 'Tags', 'Status']);
        while ($row = $items->fetch(\PDO::FETCH_ASSOC)) {
            fputcsv($output, array_values($row));
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
