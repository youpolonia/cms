<?php
declare(strict_types=1);

namespace CMS\Models;

/**
 * Content model with soft delete support
 */
class Content extends BaseModel
{
    protected static $table = 'contents';
    protected static $columnWhitelist = [
        'id', 'type', 'data', 'author_id', 'status',
        'created_at', 'updated_at', 'deleted_at'
    ];

    private ?int $id;
    private string $type;
    private array $data;
    private int $authorId;
    private string $status;
    private int $createdAt;
    private int $updatedAt;
    private ?int $deletedAt;

    public function __construct(
        string $type,
        array $data,
        int $authorId,
        string $status = 'draft',
        ?int $id = null,
        ?int $createdAt = null,
        ?int $updatedAt = null,
        ?int $deletedAt = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->data = $data;
        $this->authorId = $authorId;
        $this->status = $status;
        $this->createdAt = $createdAt ?? time();
        $this->updatedAt = $updatedAt ?? time();
        $this->deletedAt = $deletedAt;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getData(): array { return $this->data; }
    public function getAuthorId(): int { return $this->authorId; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): int { return $this->createdAt; }
    public function getUpdatedAt(): int { return $this->updatedAt; }
    public function getDeletedAt(): ?int { return $this->deletedAt; }

    // Setters
    public function setData(array $data): void {
        $this->data = $data;
        $this->updatedAt = time();
    }

    public function setStatus(string $status): void {
        $this->status = $status;
        $this->updatedAt = time();
    }

    public function markDeleted(): void {
        $this->deletedAt = time();
        $this->updatedAt = time();
    }

    public function isDeleted(): bool {
        return $this->deletedAt !== null;
    }

    /**
     * Saves the content to database
     */
    public function save(): bool
    {
        $data = [
            'type' => $this->type,
            'data' => json_encode($this->data),
            'author_id' => $this->authorId,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt
        ];

        if ($this->id) {
            return (bool)static::query()
                ->where('id', $this->id)
                ->update($data);
        } else {
            $id = static::query()->insert($data);
            if ($id) {
                $this->id = $id;
                return true;
            }
            return false;
        }
    }

    /**
     * Loads content by ID
     */
    public static function findById(int $id): ?self
    {
        $row = static::query()
            ->where('id', $id)
            ->first();

        if (!$row) {
            return null;
        }

        return new self(
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
}
