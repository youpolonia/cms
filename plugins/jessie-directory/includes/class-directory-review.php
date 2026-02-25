<?php
declare(strict_types=1);

class DirectoryReview
{
    public static function getForListing(int $listingId, string $status = 'approved'): array
    {
        $stmt = db()->prepare("SELECT * FROM directory_reviews WHERE listing_id = ? AND status = ? ORDER BY created_at DESC");
        $stmt->execute([$listingId, $status]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getPending(): array
    {
        return db()->query("SELECT r.*, l.title AS listing_title FROM directory_reviews r JOIN directory_listings l ON r.listing_id = l.id WHERE r.status = 'pending' ORDER BY r.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO directory_reviews (listing_id, reviewer_name, reviewer_email, rating, title, content, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            (int)$data['listing_id'], $data['reviewer_name'] ?? '', $data['reviewer_email'] ?? '',
            max(1, min(5, (int)($data['rating'] ?? 5))),
            $data['title'] ?? '', $data['content'] ?? '',
            $data['auto_approve'] ?? false ? 'approved' : 'pending',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (($data['auto_approve'] ?? false)) \DirectoryListing::recalcRating((int)$data['listing_id']);
        return $id;
    }

    public static function approve(int $id): void
    {
        $pdo = db();
        $pdo->prepare("UPDATE directory_reviews SET status = 'approved' WHERE id = ?")->execute([$id]);
        $listingId = (int)$pdo->prepare("SELECT listing_id FROM directory_reviews WHERE id = ?")->execute([$id]) ? $pdo->query("SELECT listing_id FROM directory_reviews WHERE id = {$id}")->fetchColumn() : 0;
        $stmt = $pdo->prepare("SELECT listing_id FROM directory_reviews WHERE id = ?"); $stmt->execute([$id]); $listingId = (int)$stmt->fetchColumn();
        if ($listingId) \DirectoryListing::recalcRating($listingId);
    }

    public static function reject(int $id): void
    {
        db()->prepare("UPDATE directory_reviews SET status = 'rejected' WHERE id = ?")->execute([$id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare("SELECT listing_id FROM directory_reviews WHERE id = ?"); $stmt->execute([$id]); $lid = (int)$stmt->fetchColumn();
        db()->prepare("DELETE FROM directory_reviews WHERE id = ?")->execute([$id]);
        if ($lid) \DirectoryListing::recalcRating($lid);
    }
}
