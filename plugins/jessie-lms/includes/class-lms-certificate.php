<?php
declare(strict_types=1);

class LmsCertificate
{
    public static function generate(int $enrollmentId): array
    {
        $pdo = db();
        $enrollment = $pdo->prepare("SELECT e.*, c.title AS course_title FROM lms_enrollments e JOIN lms_courses c ON e.course_id = c.id WHERE e.id = ?");
        $enrollment->execute([$enrollmentId]);
        $e = $enrollment->fetch(\PDO::FETCH_ASSOC);
        if (!$e) return ['ok' => false, 'error' => 'Enrollment not found'];
        if ($e['status'] !== 'completed') return ['ok' => false, 'error' => 'Course not completed'];

        $existing = $pdo->prepare("SELECT * FROM lms_certificates WHERE enrollment_id = ?");
        $existing->execute([$enrollmentId]);
        if ($row = $existing->fetch(\PDO::FETCH_ASSOC)) return ['ok' => true, 'certificate' => $row, 'already_exists' => true];

        $code = 'CERT-' . strtoupper(substr(md5((string)$enrollmentId . time() . random_bytes(4)), 0, 10));
        $stmt = $pdo->prepare("INSERT INTO lms_certificates (enrollment_id, course_id, student_name, student_email, certificate_code) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$enrollmentId, $e['course_id'], $e['name'] ?: $e['email'], $e['email'], $code]);

        $pdo->prepare("UPDATE lms_enrollments SET certificate_id = ? WHERE id = ?")->execute([$code, $enrollmentId]);

        return ['ok' => true, 'certificate' => [
            'id' => (int)$pdo->lastInsertId(),
            'certificate_code' => $code,
            'student_name' => $e['name'] ?: $e['email'],
            'course_title' => $e['course_title'],
            'issued_at' => date('Y-m-d H:i:s'),
        ]];
    }

    public static function verify(string $code): ?array
    {
        $stmt = db()->prepare("SELECT cert.*, c.title AS course_title, c.instructor_name FROM lms_certificates cert JOIN lms_courses c ON cert.course_id = c.id WHERE cert.certificate_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getForStudent(string $email): array
    {
        $stmt = db()->prepare("SELECT cert.*, c.title AS course_title FROM lms_certificates cert JOIN lms_courses c ON cert.course_id = c.id WHERE cert.student_email = ? ORDER BY cert.issued_at DESC");
        $stmt->execute([$email]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getAll(int $page = 1, int $perPage = 25): array
    {
        $pdo = db();
        $total = (int)$pdo->query("SELECT COUNT(*) FROM lms_certificates")->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT cert.*, c.title AS course_title FROM lms_certificates cert JOIN lms_courses c ON cert.course_id = c.id ORDER BY cert.issued_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$perPage, $offset]);
        return ['certificates' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total];
    }
}
