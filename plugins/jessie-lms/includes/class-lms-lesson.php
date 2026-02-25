<?php
declare(strict_types=1);

class LmsLesson
{
    public static function getByCourse(int $courseId): array
    {
        $stmt = db()->prepare("SELECT * FROM lms_lessons WHERE course_id = ? ORDER BY section, sort_order");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT l.*, c.title AS course_title, c.slug AS course_slug FROM lms_lessons l JOIN lms_courses c ON l.course_id = c.id WHERE l.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $maxOrder = (int)$pdo->prepare("SELECT MAX(sort_order) FROM lms_lessons WHERE course_id = ?")->execute([(int)$data['course_id']]) ? $pdo->query("SELECT MAX(sort_order) FROM lms_lessons WHERE course_id = " . (int)$data['course_id'])->fetchColumn() : 0;
        $stmt = $pdo->prepare("INSERT INTO lms_lessons (course_id, title, slug, content_type, content_html, video_url, duration_minutes, sort_order, section, is_preview, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($data['title'] ?? 'lesson')));
        $stmt->execute([
            (int)$data['course_id'], $data['title'] ?? '', $slug,
            $data['content_type'] ?? 'text', $data['content_html'] ?? '', $data['video_url'] ?? null,
            (int)($data['duration_minutes'] ?? 0), ($maxOrder ?? 0) + 1,
            $data['section'] ?? '', (int)($data['is_preview'] ?? 0), $data['status'] ?? 'published',
        ]);
        $id = (int)$pdo->lastInsertId();
        \LmsCourse::recalcDuration((int)$data['course_id']);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['title','content_type','content_html','video_url','duration_minutes','sort_order','section','is_preview','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) { if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; } }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE lms_lessons SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
        $lesson = self::get($id);
        if ($lesson) \LmsCourse::recalcDuration((int)$lesson['course_id']);
    }

    public static function delete(int $id): void
    {
        $lesson = self::get($id);
        db()->prepare("DELETE FROM lms_lessons WHERE id = ?")->execute([$id]);
        if ($lesson) \LmsCourse::recalcDuration((int)$lesson['course_id']);
    }

    public static function reorder(int $courseId, array $lessonIds): void
    {
        $pdo = db();
        foreach ($lessonIds as $i => $lid) {
            $pdo->prepare("UPDATE lms_lessons SET sort_order = ? WHERE id = ? AND course_id = ?")->execute([$i + 1, (int)$lid, $courseId]);
        }
    }

    /**
     * Get grouped by section.
     */
    public static function getGrouped(int $courseId): array
    {
        $lessons = self::getByCourse($courseId);
        $sections = [];
        foreach ($lessons as $l) {
            $sec = $l['section'] ?: 'General';
            $sections[$sec][] = $l;
        }
        return $sections;
    }
}
