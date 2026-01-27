<?php
require_once __DIR__ . '/../../../core/database.php';

class ContentService {
    private static function getDbConnection(): PDO {
        return \core\Database::connection();
    }

    public static function getContentBySlug(string $slug, bool $includeDrafts = false): ?array {
        if (!self::validateSlug($slug)) {
            throw new InvalidArgumentException('Invalid slug format');
        }

        try {
            $db = self::getDbConnection();
            $sql = 'SELECT * FROM content WHERE slug = :slug AND type = "page"';
            if (!$includeDrafts) {
                $sql .= ' AND status = "published"';
            }
            $stmt = $db->prepare($sql);
            $stmt->execute([':slug' => $slug]);
            $result = $stmt->fetch();
            
            if (!$result) {
                throw new ContentNotFoundException('Content not found');
            }
            return $result;
        } catch (PDOException $e) {
            error_log('Content fetch error: ' . $e->getMessage());
            throw new ContentRetrievalException('Failed to retrieve content', 0, $e);
        }
    }

    public static function getBlogPostBySlug(string $slug): ?array {
        if (!self::validateSlug($slug)) {
            return null;
        }

        try {
            $db = self::getDbConnection();
            $stmt = $db->prepare('SELECT * FROM content WHERE slug = :slug AND type = "blog"');
            $stmt->execute([':slug' => $slug]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('Blog post fetch error: ' . $e->getMessage());
            return null;
        }
    }

    public static function listContent(
        int $page = 1,
        int $perPage = 10,
        string $status = 'published',
        string $orderBy = 'created_at',
        string $orderDirection = 'DESC'
    ): array {
        try {
            $db = self::getDbConnection();
            
            // Validate inputs
            if ($page < 1 || $perPage < 1) {
                throw new InvalidArgumentException('Page and perPage must be positive integers');
            }
            
            $offset = ($page - 1) * $perPage;
            $sql = 'SELECT * FROM content WHERE type = "page" AND status = :status';
            $sql .= " ORDER BY $orderBy $orderDirection LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Content listing error: ' . $e->getMessage());
            throw new ContentRetrievalException('Failed to list content', 0, $e);
        }
    }
    
    public static function countContent(string $status = 'published'): int {
        try {
            $db = self::getDbConnection();
            $stmt = $db->prepare('SELECT COUNT(*) FROM content WHERE type = "page" AND status = :status');
            $stmt->execute([':status' => $status]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Content count error: ' . $e->getMessage());
            throw new ContentRetrievalException('Failed to count content', 0, $e);
        }
    }

    public static function listAllBlogPosts(): array {
        try {
            $db = self::getDbConnection();
            $stmt = $db->query('SELECT * FROM content WHERE type = "blog" ORDER BY date DESC');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Blog posts listing error: ' . $e->getMessage());
            return [];
        }
    }

    private static function validateSlug(string $slug): bool {
        return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
    }
}
