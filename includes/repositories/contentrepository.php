<?php
declare(strict_types=1);

namespace CMS\Repositories;

use CMS\Models\Content;
use PDO;
use PDOException;

class ContentRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(Content $content): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO content 
            (type, data, author_id, status, created_at, updated_at) 
            VALUES (:type, :data, :author_id, :status, :created_at, :updated_at)
        ");

        $stmt->execute([
            ':type' => $content->getType(),
            ':data' => json_encode($content->getData()),
            ':author_id' => $content->getAuthorId(),
            ':status' => $content->getStatus(),
            ':created_at' => $content->getCreatedAt(),
            ':updated_at' => $content->getUpdatedAt()
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(Content $content): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE content SET
            type = :type,
            data = :data,
            status = :status,
            updated_at = :updated_at
            WHERE id = :id AND deleted_at IS NULL
        ");

        return $stmt->execute([
            ':id' => $content->getId(),
            ':type' => $content->getType(),
            ':data' => json_encode($content->getData()),
            ':status' => $content->getStatus(),
            ':updated_at' => $content->getUpdatedAt()
        ]);
    }

    public function find(int $id): ?Content
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM content 
            WHERE id = :id AND deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE content SET
            deleted_at = :deleted_at,
            updated_at = :updated_at
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id' => $id,
            ':deleted_at' => time(),
            ':updated_at' => time()
        ]);
    }

    private function hydrate(array $row): Content
    {
        return new Content(
            $row['type'],
            json_decode($row['data'], true),
            (int)$row['author_id'],
            $row['status'],
            (int)$row['id'],
            (int)$row['created_at'],
            (int)$row['updated_at'],
            $row['deleted_at'] ? (int)$row['deleted_at'] : null
        );
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }
}
