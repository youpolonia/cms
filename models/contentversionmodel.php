<?php
class ContentVersionModel {
    protected $db;

    public function __construct() {
        require_once __DIR__ . '/../core/database.php';
        $this->db = \core\Database::connection();
    }

    public function createInitialVersion($contentId, array $data) {
        $query = "INSERT INTO content_versions 
                  (content_id, version_number, title, content, is_autosave, created_by)
                  VALUES (:content_id, 1, :title, :content, 0, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':content_id' => $contentId,
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':user_id' => $_SESSION['user_id'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function createNewVersion($contentId, array $data) {
        $latest = $this->getLatestVersion($contentId);
        $newVersion = $latest ? $latest['version_number'] + 1 : 1;

        $query = "INSERT INTO content_versions 
                  (content_id, version_number, title, content, is_autosave, created_by)
                  VALUES (:content_id, :version, :title, :content, 0, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':content_id' => $contentId,
            ':version' => $newVersion,
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':user_id' => $_SESSION['user_id'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function createAutosaveVersion($contentId, array $data) {
        // Delete any existing autosave for this content
        $this->deleteAutosave($contentId);

        $query = "INSERT INTO content_versions 
                  (content_id, version_number, title, content, is_autosave, created_by)
                  VALUES (:content_id, 0, :title, :content, 1, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':content_id' => $contentId,
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':user_id' => $_SESSION['user_id'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function getLatestVersion($contentId) {
        $query = "SELECT * FROM content_versions 
                 WHERE content_id = :content_id AND is_autosave = 0
                 ORDER BY version_number DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':content_id' => $contentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAutosave($contentId) {
        $query = "SELECT * FROM content_versions 
                 WHERE content_id = :content_id AND is_autosave = 1
                 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':content_id' => $contentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getByPageId($contentId) {
        $query = "SELECT * FROM content_versions 
                 WHERE content_id = :content_id AND is_autosave = 0
                 ORDER BY version_number DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':content_id' => $contentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function deleteAutosave($contentId) {
        $query = "DELETE FROM content_versions 
                 WHERE content_id = :content_id AND is_autosave = 1";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':content_id' => $contentId]);
    }

    public function deleteByPageId($contentId) {
        $query = "DELETE FROM content_versions WHERE content_id = :content_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':content_id' => $contentId]);
    }

    public function getVersionById($versionId) {
        $query = "SELECT * FROM content_versions WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $versionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getVersionsForComparison($contentId, $versionAId, $versionBId) {
        $query = "SELECT * FROM content_versions
                 WHERE id IN (:id1, :id2) AND content_id = :content_id
                 ORDER BY version_number DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':id1' => $versionAId,
            ':id2' => $versionBId,
            ':content_id' => $contentId
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function restoreVersion($contentId, $versionId) {
        $version = $this->getVersionById($versionId);
        if (!$version) {
            return false;
        }

        return $this->createNewVersion($contentId, [
            'title' => $version['title'],
            'content' => $version['content']
        ]);
    }
}
