<?php
declare(strict_types=1);

class LmsReview
{
    public static function getForCourse(int $courseId, string $status = 'approved'): array
    {
        $stmt = db()->prepare("SELECT * FROM lms_reviews WHERE course_id = ? AND status = ? ORDER BY created_at DESC");
        $stmt->execute([$courseId, $status]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getPending(): array
    {
        return db()->query("SELECT r.*, c.title AS course_title FROM lms_reviews r JOIN lms_courses c ON r.course_id = c.id WHERE r.status = 'pending' ORDER BY r.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create(int $courseId, string $email, string $name, int $rating, string $review): array
    {
        $pdo = db();
        $existing = $pdo->prepare("SELECT id FROM lms_reviews WHERE course_id = ? AND email = ?");
        $existing->execute([$courseId, $email]);
        if ($existing->fetch()) return ['ok' => false, 'error' => 'You already reviewed this course'];

        $enrolled = $pdo->prepare("SELECT id FROM lms_enrollments WHERE course_id = ? AND email = ?");
        $enrolled->execute([$courseId, $email]);
        if (!$enrolled->fetch()) return ['ok' => false, 'error' => 'You must be enrolled to review'];

        $stmt = $pdo->prepare("INSERT INTO lms_reviews (course_id, email, name, rating, review) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$courseId, $email, $name, min(5, max(1, $rating)), $review]);
        return ['ok' => true, 'id' => (int)$pdo->lastInsertId()];
    }

    public static function approve(int $id): void
    {
        $pdo = db();
        $pdo->prepare("UPDATE lms_reviews SET status = 'approved' WHERE id = ?")->execute([$id]);
        $review = $pdo->prepare("SELECT course_id FROM lms_reviews WHERE id = ?");
        $review->execute([$id]);
        $courseId = (int)$review->fetchColumn();
        if ($courseId) self::recalcRating($courseId);
    }

    public static function reject(int $id): void
    {
        db()->prepare("UPDATE lms_reviews SET status = 'rejected' WHERE id = ?")->execute([$id]);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $review = $pdo->prepare("SELECT course_id FROM lms_reviews WHERE id = ?");
        $review->execute([$id]);
        $courseId = (int)$review->fetchColumn();
        $pdo->prepare("DELETE FROM lms_reviews WHERE id = ?")->execute([$id]);
        if ($courseId) self::recalcRating($courseId);
    }

    public static function recalcRating(int $courseId): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM lms_reviews WHERE course_id = ? AND status = 'approved'");
        $stmt->execute([$courseId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $pdo->prepare("UPDATE lms_courses SET avg_rating = ?, review_count = ? WHERE id = ?")->execute([round((float)$row['avg_r'], 2), (int)$row['cnt'], $courseId]);
    }
}
