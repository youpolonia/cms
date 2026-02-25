<?php
namespace Plugins\JessieSeoWriter;

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 3)); }
require_once CMS_ROOT . '/db.php';

/**
 * SEO Writer Core — orchestrator for keyword, editor, generator, audit modules
 */
class SeoWriterCore {
    private \PDO $pdo;
    private int $userId;

    public function __construct(int $userId) {
        $this->pdo = \core\Database::connection();
        $this->userId = $userId;
    }

    public function getPdo(): \PDO { return $this->pdo; }
    public function getUserId(): int { return $this->userId; }

    // ── Projects ──

    public function getProjects(int $limit = 50, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, 
                    (SELECT COUNT(*) FROM seowriter_content c WHERE c.project_id = p.id) as content_count,
                    (SELECT COUNT(*) FROM seowriter_audits a WHERE a.project_id = p.id) as audit_count
             FROM seowriter_projects p 
             WHERE p.user_id = ? AND p.status = 'active'
             ORDER BY p.updated_at DESC 
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$this->userId, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getProject(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM seowriter_projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function createProject(string $name, string $description = '', string $keyword = '', string $language = 'en'): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO seowriter_projects (user_id, name, description, target_keyword, language) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$this->userId, $name, $description, $keyword, $language]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateProject(int $id, array $data): bool {
        $allowed = ['name', 'description', 'target_keyword', 'language', 'status'];
        $sets = [];
        $params = [];
        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $sets[] = "`$f` = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $params[] = $this->userId;
        return $this->pdo->prepare("UPDATE seowriter_projects SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ?")->execute($params);
    }

    // ── Content ──

    public function getContent(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM seowriter_content WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function getContentList(int $projectId = 0, int $limit = 50): array {
        if ($projectId > 0) {
            $stmt = $this->pdo->prepare("SELECT id, title, target_keyword, seo_score, word_count, status, created_at, updated_at FROM seowriter_content WHERE user_id = ? AND project_id = ? ORDER BY updated_at DESC LIMIT ?");
            $stmt->execute([$this->userId, $projectId, $limit]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id, title, target_keyword, seo_score, word_count, status, created_at, updated_at FROM seowriter_content WHERE user_id = ? ORDER BY updated_at DESC LIMIT ?");
            $stmt->execute([$this->userId, $limit]);
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function saveContent(array $data): int {
        if (!empty($data['id'])) {
            // Update existing
            $stmt = $this->pdo->prepare(
                "UPDATE seowriter_content SET title = ?, meta_description = ?, target_keyword = ?, body = ?, outline_json = ?, seo_score = ?, word_count = ?, status = ?, project_id = ?
                 WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([
                $data['title'] ?? '', $data['meta_description'] ?? '', $data['target_keyword'] ?? '',
                $data['body'] ?? '', $data['outline_json'] ?? null, (int)($data['seo_score'] ?? 0),
                (int)($data['word_count'] ?? 0), $data['status'] ?? 'draft', $data['project_id'] ?? null,
                (int)$data['id'], $this->userId
            ]);
            return (int)$data['id'];
        }
        // Insert new
        $stmt = $this->pdo->prepare(
            "INSERT INTO seowriter_content (user_id, project_id, title, meta_description, target_keyword, body, outline_json, seo_score, word_count, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $this->userId, $data['project_id'] ?? null, $data['title'] ?? '', $data['meta_description'] ?? '',
            $data['target_keyword'] ?? '', $data['body'] ?? '', $data['outline_json'] ?? null,
            (int)($data['seo_score'] ?? 0), (int)($data['word_count'] ?? 0), $data['status'] ?? 'draft'
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    // ── Audits ──

    public function saveAudit(string $url, int $score, array $issues, array $meta = [], ?int $projectId = null): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO seowriter_audits (user_id, project_id, url, score, issues_json, meta_json) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $this->userId, $projectId, $url, $score,
            json_encode($issues, JSON_UNESCAPED_UNICODE),
            json_encode($meta, JSON_UNESCAPED_UNICODE)
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getAudits(int $limit = 20): array {
        $stmt = $this->pdo->prepare("SELECT * FROM seowriter_audits WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$this->userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ── Dashboard stats ──

    public function getDashboardStats(): array {
        $uid = $this->userId;
        $projects = $this->pdo->prepare("SELECT COUNT(*) FROM seowriter_projects WHERE user_id = ? AND status = 'active'");
        $projects->execute([$uid]);

        $articles = $this->pdo->prepare("SELECT COUNT(*) FROM seowriter_content WHERE user_id = ?");
        $articles->execute([$uid]);

        $audits = $this->pdo->prepare("SELECT COUNT(*) FROM seowriter_audits WHERE user_id = ?");
        $audits->execute([$uid]);

        $avgScore = $this->pdo->prepare("SELECT AVG(seo_score) FROM seowriter_content WHERE user_id = ? AND seo_score > 0");
        $avgScore->execute([$uid]);

        return [
            'projects' => (int)$projects->fetchColumn(),
            'articles' => (int)$articles->fetchColumn(),
            'audits'   => (int)$audits->fetchColumn(),
            'avg_score' => round((float)$avgScore->fetchColumn()),
        ];
    }
}
