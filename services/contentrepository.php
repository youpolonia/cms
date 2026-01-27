<?php
declare(strict_types=1);

class ContentRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findAll(int $limit = 20, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, 
                    (SELECT name FROM content_lifecycle_states WHERE id = c.lifecycle_state_id) as state_name
             FROM contents c
             ORDER BY c.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(string $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, 
                    (SELECT name FROM content_lifecycle_states WHERE id = c.lifecycle_state_id) as state_name
             FROM contents c
             WHERE c.id = :id"
        );
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data): string {
        $stmt = $this->pdo->prepare(
            "INSERT INTO contents (id, title, body, lifecycle_state_id, created_by, created_at)
             VALUES (UUID(), :title, :body, 
                    (SELECT id FROM content_lifecycle_states WHERE name = 'draft'), 
                    :created_by, NOW())"
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':body' => $data['body'],
            ':created_by' => $data['user_id']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update(string $id, array $data): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE contents 
             SET title = :title, 
                 body = :body,
                 updated_at = NOW()
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':title' => $data['title'],
            ':body' => $data['body']
        ]);
    }

    public function delete(string $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM contents WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
