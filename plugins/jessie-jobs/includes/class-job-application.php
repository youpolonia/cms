<?php
declare(strict_types=1);

class JobApplication
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];

        if (!empty($filters['status'])) { $where[] = 'a.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['job_id'])) { $where[] = 'a.job_id = ?'; $params[] = (int)$filters['job_id']; }
        if (!empty($filters['search'])) { $where[] = '(a.applicant_name LIKE ? OR a.applicant_email LIKE ?)'; $params[] = '%' . $filters['search'] . '%'; $params[] = '%' . $filters['search'] . '%'; }

        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_applications a WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT a.*, j.title AS job_title, j.company_name FROM job_applications a LEFT JOIN job_listings j ON a.job_id = j.id WHERE {$wSql} ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return ['applications' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT a.*, j.title AS job_title, j.company_name FROM job_applications a LEFT JOIN job_listings j ON a.job_id = j.id WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getForJob(int $jobId): array
    {
        $stmt = db()->prepare("SELECT * FROM job_applications WHERE job_id = ? ORDER BY created_at DESC");
        $stmt->execute([$jobId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO job_applications (job_id, applicant_name, applicant_email, applicant_phone, cover_letter, resume_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            (int)($data['job_id'] ?? 0),
            $data['applicant_name'] ?? '',
            $data['applicant_email'] ?? '',
            ($data['applicant_phone'] ?? null) ?: '',
            ($data['cover_letter'] ?? null) ?: '',
            ($data['resume_path'] ?? null) ?: '',
            'new',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('jobs.application.created', ['application_id' => $id, 'job_id' => (int)($data['job_id'] ?? 0)]);
        return $id;
    }

    public static function updateStatus(int $id, string $status): void
    {
        $allowed = ['new', 'reviewed', 'shortlisted', 'rejected'];
        if (!in_array($status, $allowed)) return;
        db()->prepare("UPDATE job_applications SET status = ? WHERE id = ?")->execute([$status, $id]);
    }

    public static function delete(int $id): void
    {
        db()->prepare("DELETE FROM job_applications WHERE id = ?")->execute([$id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS total, SUM(status='new') AS new_count, SUM(status='reviewed') AS reviewed, SUM(status='shortlisted') AS shortlisted, SUM(status='rejected') AS rejected FROM job_applications")->fetch(\PDO::FETCH_ASSOC);
        return array_map('intval', $row);
    }
}
