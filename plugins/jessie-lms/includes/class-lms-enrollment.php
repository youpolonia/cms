<?php
declare(strict_types=1);

class LmsEnrollment
{
    public static function enroll(int $courseId, string $email, string $name = '', ?int $userId = null): array
    {
        $pdo = db();
        $existing = $pdo->prepare("SELECT id, status FROM lms_enrollments WHERE course_id = ? AND email = ?");
        $existing->execute([$courseId, strtolower(trim($email))]);
        $row = $existing->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            if ($row['status'] === 'dropped') {
                $pdo->prepare("UPDATE lms_enrollments SET status = 'active', started_at = NOW() WHERE id = ?")->execute([$row['id']]);
                return ['ok' => true, 'id' => (int)$row['id'], 'reactivated' => true];
            }
            return ['ok' => true, 'id' => (int)$row['id'], 'already_enrolled' => true];
        }

        $stmt = $pdo->prepare("INSERT INTO lms_enrollments (course_id, user_id, email, name, status, completed_lessons) VALUES (?, ?, ?, ?, 'active', '[]')");
        $stmt->execute([$courseId, $userId, strtolower(trim($email)), $name]);
        $id = (int)$pdo->lastInsertId();

        $pdo->prepare("UPDATE lms_courses SET enrollment_count = enrollment_count + 1 WHERE id = ?")->execute([$courseId]);

        if (function_exists('cms_event')) {
            cms_event('lms.enrolled', ['enrollment_id' => $id, 'course_id' => $courseId, 'email' => $email]);
        }
        return ['ok' => true, 'id' => $id];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT e.*, c.title AS course_title FROM lms_enrollments e JOIN lms_courses c ON e.course_id = c.id WHERE e.id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) $r['completed_lessons'] = json_decode($r['completed_lessons'] ?: '[]', true);
        return $r ?: null;
    }

    public static function getByCourse(int $courseId, int $page = 1, int $perPage = 25): array
    {
        $pdo = db();
        $total = (int)$pdo->prepare("SELECT COUNT(*) FROM lms_enrollments WHERE course_id = ?")->execute([$courseId]) ?
            $pdo->query("SELECT COUNT(*) FROM lms_enrollments WHERE course_id = {$courseId}")->fetchColumn() : 0;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM lms_enrollments WHERE course_id = ?");
        $stmt->execute([$courseId]); $total = (int)$stmt->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM lms_enrollments WHERE course_id = ? ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute([$courseId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) $r['completed_lessons'] = json_decode($r['completed_lessons'] ?: '[]', true);
        return ['enrollments' => $rows, 'total' => $total];
    }

    public static function completeLesson(int $enrollmentId, int $lessonId): array
    {
        $pdo = db();
        $enrollment = self::get($enrollmentId);
        if (!$enrollment) return ['ok' => false, 'error' => 'Enrollment not found'];

        $completed = $enrollment['completed_lessons'];
        if (!in_array($lessonId, $completed)) {
            $completed[] = $lessonId;
            $totalLessons = (int)$pdo->prepare("SELECT COUNT(*) FROM lms_lessons WHERE course_id = ? AND status = 'published'")->execute([$enrollment['course_id']]) ?
                $pdo->query("SELECT COUNT(*) FROM lms_lessons WHERE course_id = {$enrollment['course_id']} AND status = 'published'")->fetchColumn() : 1;
            $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM lms_lessons WHERE course_id = ? AND status = 'published'");
            $stmt2->execute([$enrollment['course_id']]); $totalLessons = max(1, (int)$stmt2->fetchColumn());
            $progress = round(count($completed) / $totalLessons * 100, 2);
            $isComplete = $progress >= 100;

            $pdo->prepare("UPDATE lms_enrollments SET completed_lessons = ?, progress_pct = ?, last_activity = NOW()" .
                ($isComplete ? ", status = 'completed', completed_at = NOW(), certificate_id = ?" : "") .
                " WHERE id = ?")
                ->execute(array_merge(
                    [json_encode($completed), $progress],
                    $isComplete ? ['CERT-' . strtoupper(substr(md5($enrollmentId . time()), 0, 8))] : [],
                    [$enrollmentId]
                ));

            if ($isComplete) {
                $pdo->prepare("UPDATE lms_courses SET completion_count = completion_count + 1 WHERE id = ?")->execute([$enrollment['course_id']]);
                if (function_exists('cms_event')) {
                    cms_event('lms.completed', ['enrollment_id' => $enrollmentId, 'course_id' => $enrollment['course_id']]);
                }
            }
        }

        return ['ok' => true, 'progress' => $progress ?? $enrollment['progress_pct'], 'completed' => $isComplete ?? false];
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS total, SUM(status='active') AS active, SUM(status='completed') AS completed, AVG(progress_pct) AS avg_progress FROM lms_enrollments")->fetch(\PDO::FETCH_ASSOC);
        return ['total' => (int)$row['total'], 'active' => (int)$row['active'], 'completed' => (int)$row['completed'], 'avg_progress' => round((float)$row['avg_progress'], 1)];
    }
}
