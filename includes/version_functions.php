<?php
declare(strict_types=1);

class VersionFunctions {
    public static function getAllVersions(): array {
        $db = \core\Database::connection();

        $query = "SELECT v.id, v.version_number, v.created_at,
                 u.username AS author, v.comment
                 FROM versions v
                 JOIN users u ON v.user_id = u.id
                 ORDER BY v.created_at DESC";
        $result = $db->query($query);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createVersion(): int {
        $db = \core\Database::connection();

        $userId = $_SESSION['user_id'];
        $comment = $_POST['comment'] ?? 'Automatic version';

        $stmt = $db->prepare("INSERT INTO versions
                   (user_id, version_number, comment)
                   VALUES (?, UNIX_TIMESTAMP(), ?)");
        $stmt->execute([$userId, $comment]);

        return $db->lastInsertId();
    }

    public static function restoreVersion(int $versionId): void {
        $db = \core\Database::connection();

        // Get version content
        $stmt = $db->prepare("SELECT content FROM versions WHERE id = ?");
        $stmt->execute([$versionId]);
        $version = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$version) {
            throw new RuntimeException("Version not found");
        }

        // Restore to current content
        $stmt = $db->prepare("UPDATE current_content SET content = ?");
        $stmt->execute([$version['content']]);
    }

    public static function getVersionDiff(int $version1, int $version2): array {
        $db = \core\Database::connection();

        $stmt1 = $db->prepare("SELECT content FROM versions WHERE id = ?");
        $stmt1->execute([$version1]);
        $v1 = $stmt1->fetch(PDO::FETCH_ASSOC);

        $stmt2 = $db->prepare("SELECT content FROM versions WHERE id = ?");
        $stmt2->execute([$version2]);
        $v2 = $stmt2->fetch(PDO::FETCH_ASSOC);

        if (!$v1 || !$v2) {
            throw new RuntimeException("One or both versions not found");
        }

        return [
            'left' => htmlspecialchars($v1['content']),
            'right' => htmlspecialchars($v2['content'])
        ];
    }
}
