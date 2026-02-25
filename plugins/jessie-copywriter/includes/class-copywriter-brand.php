<?php
namespace Plugins\JessieCopywriter;

/**
 * Brand Voice CRUD — tone, vocabulary, guidelines stored as JSON
 */
class CopywriterBrand {
    private \PDO $pdo;

    public function __construct() {
        if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
        require_once CMS_ROOT . '/db.php';
        $this->pdo = \core\Database::connection();
    }

    /** List all brands for a user */
    public function list(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM copywriter_brands WHERE user_id = ? AND status = 'active' ORDER BY name"
        );
        $stmt->execute([$userId]);
        $brands = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($brands as &$b) {
            $b['vocabulary'] = json_decode($b['vocabulary_json'] ?? '{}', true) ?: [];
            $b['guidelines'] = json_decode($b['guidelines_json'] ?? '{}', true) ?: [];
        }
        return $brands;
    }

    /** Get single brand */
    public function get(int $id, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM copywriter_brands WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $b = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$b) return null;
        $b['vocabulary'] = json_decode($b['vocabulary_json'] ?? '{}', true) ?: [];
        $b['guidelines'] = json_decode($b['guidelines_json'] ?? '{}', true) ?: [];
        return $b;
    }

    /** Create a brand voice */
    public function create(int $userId, array $data): array {
        $name = trim($data['name'] ?? '');
        if ($name === '') {
            return ['success' => false, 'error' => 'Brand name is required'];
        }

        $tone = trim($data['tone'] ?? 'professional');
        $vocabulary = $data['vocabulary'] ?? [];
        $guidelines = $data['guidelines'] ?? [];
        $examples = trim($data['examples'] ?? '');

        if (is_string($vocabulary)) $vocabulary = array_filter(array_map('trim', explode(',', $vocabulary)));
        if (is_string($guidelines)) $guidelines = array_filter(array_map('trim', explode("\n", $guidelines)));

        $stmt = $this->pdo->prepare(
            "INSERT INTO copywriter_brands (user_id, name, tone, vocabulary_json, guidelines_json, examples)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $userId, $name, $tone,
            json_encode($vocabulary, JSON_UNESCAPED_UNICODE),
            json_encode($guidelines, JSON_UNESCAPED_UNICODE),
            $examples
        ]);

        return ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }

    /** Update a brand voice */
    public function update(int $id, int $userId, array $data): array {
        $existing = $this->get($id, $userId);
        if (!$existing) return ['success' => false, 'error' => 'Brand not found'];

        $name = trim($data['name'] ?? $existing['name']);
        $tone = trim($data['tone'] ?? $existing['tone']);
        $examples = trim($data['examples'] ?? $existing['examples'] ?? '');

        $vocabulary = $data['vocabulary'] ?? $existing['vocabulary'];
        $guidelines = $data['guidelines'] ?? $existing['guidelines'];
        if (is_string($vocabulary)) $vocabulary = array_filter(array_map('trim', explode(',', $vocabulary)));
        if (is_string($guidelines)) $guidelines = array_filter(array_map('trim', explode("\n", $guidelines)));

        $stmt = $this->pdo->prepare(
            "UPDATE copywriter_brands SET name=?, tone=?, vocabulary_json=?, guidelines_json=?, examples=? WHERE id=? AND user_id=?"
        );
        $stmt->execute([
            $name, $tone,
            json_encode($vocabulary, JSON_UNESCAPED_UNICODE),
            json_encode($guidelines, JSON_UNESCAPED_UNICODE),
            $examples, $id, $userId
        ]);

        return ['success' => true];
    }

    /** Archive a brand */
    public function delete(int $id, int $userId): bool {
        $stmt = $this->pdo->prepare("UPDATE copywriter_brands SET status='archived' WHERE id=? AND user_id=?");
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Build brand voice prompt instructions
     */
    public function buildPromptInstructions(int $brandId, int $userId): string {
        $brand = $this->get($brandId, $userId);
        if (!$brand) return '';

        $instructions = "BRAND VOICE: {$brand['name']}\n";
        $instructions .= "TONE: {$brand['tone']}\n";

        if (!empty($brand['vocabulary'])) {
            $instructions .= "PREFERRED VOCABULARY: " . implode(', ', $brand['vocabulary']) . "\n";
        }
        if (!empty($brand['guidelines'])) {
            $instructions .= "GUIDELINES:\n";
            foreach ($brand['guidelines'] as $g) {
                $instructions .= "- $g\n";
            }
        }
        if (!empty($brand['examples'])) {
            $instructions .= "EXAMPLE COPY STYLE:\n{$brand['examples']}\n";
        }

        return $instructions;
    }
}
