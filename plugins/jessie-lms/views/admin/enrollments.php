<?php
/**
 * LMS — Enrollments Management
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-enrollment.php';
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-course.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$courseFilter = (int)($_GET['course_id'] ?? 0);
$filters = $courseFilter ? ['course_id' => $courseFilter] : [];
$courses = \LmsCourse::getAll(1, 100)['courses'] ?? [];

// Get enrollments by course or all
if ($courseFilter) {
    $result = \LmsEnrollment::getByCourse($courseFilter, $page);
} else {
    // Get all enrollments via direct query
    $pdo = db();
    $perPage = 25;
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS e.*, c.title AS course_title FROM lms_enrollments e LEFT JOIN lms_courses c ON e.course_id = c.id ORDER BY e.enrolled_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $enrollments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $total = (int)$pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
    $result = ['enrollments' => $enrollments, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
}

$stats = \LmsEnrollment::getStats();

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap" style="max-width:900px">
    <div class="j-settings-header">
        <h1>👥 Enrollments</h1>
        <a href="/admin/lms" class="j-btn-secondary">← Dashboard</a>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px">
        <?php foreach ([
            ['📊','Total', $stats['total_enrollments'] ?? 0],
            ['✅','Completed', $stats['completed'] ?? 0],
            ['📖','In Progress', $stats['in_progress'] ?? 0],
            ['📈','Avg Progress', ($stats['avg_progress'] ?? 0).'%'],
        ] as $s): ?>
        <div class="j-card" style="text-align:center;padding:16px">
            <div style="font-size:1.5rem;margin-bottom:4px"><?= $s[0] ?></div>
            <div style="font-size:1.3rem;font-weight:700;color:var(--j-text)"><?= $s[2] ?></div>
            <div style="font-size:.75rem;color:var(--j-muted)"><?= $s[1] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter -->
    <div class="j-card" style="padding:16px">
        <form method="get" style="display:flex;gap:12px;align-items:center">
            <select name="course_id" style="background:var(--j-bg);border:1px solid var(--j-border);color:var(--j-text);padding:8px 12px;border-radius:8px;font-size:.85rem">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseFilter == $c['id'] ? 'selected' : '' ?>><?= h($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="j-btn" style="padding:8px 16px;font-size:.85rem">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="j-card" style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem">
            <thead>
                <tr style="border-bottom:1px solid var(--j-border);text-align:left">
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Student</th>
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Course</th>
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Progress</th>
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Status</th>
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Enrolled</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['enrollments'] ?? [] as $e): ?>
                <tr style="border-bottom:1px solid rgba(51,65,85,.5)">
                    <td style="padding:10px"><strong><?= h($e['student_name'] ?? $e['email'] ?? 'N/A') ?></strong><br><span style="font-size:.75rem;color:var(--j-muted)"><?= h($e['email'] ?? '') ?></span></td>
                    <td style="padding:10px"><?= h($e['course_title'] ?? 'Course #'.$e['course_id']) ?></td>
                    <td style="padding:10px">
                        <div style="background:var(--j-bg);border-radius:4px;height:8px;width:100px;overflow:hidden">
                            <div style="background:var(--j-accent);height:100%;width:<?= min(100, (int)($e['progress'] ?? 0)) ?>%;border-radius:4px"></div>
                        </div>
                        <span style="font-size:.75rem;color:var(--j-muted)"><?= (int)($e['progress'] ?? 0) ?>%</span>
                    </td>
                    <td style="padding:10px">
                        <?php $st = $e['status'] ?? 'active';
                        $colors = ['active'=>'#3b82f6','completed'=>'#22c55e','dropped'=>'#ef4444'];
                        ?>
                        <span style="background:<?= $colors[$st] ?? '#64748b' ?>20;color:<?= $colors[$st] ?? '#64748b' ?>;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:600"><?= ucfirst($st) ?></span>
                    </td>
                    <td style="padding:10px;font-size:.78rem;color:var(--j-muted)"><?= date('M j, Y', strtotime($e['enrolled_at'] ?? 'now')) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($result['enrollments'])): ?>
                <tr><td colspan="5" style="padding:30px;text-align:center;color:var(--j-muted)">No enrollments found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($result['pages'] ?? 1) > 1): ?>
    <div style="text-align:center;margin-top:16px;display:flex;gap:6px;justify-content:center">
        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
        <a href="?page=<?= $p ?><?= $courseFilter ? '&course_id='.$courseFilter : '' ?>" style="padding:6px 12px;border-radius:6px;font-size:.8rem;<?= $p == $page ? 'background:var(--j-accent);color:#fff' : 'background:var(--j-card);color:var(--j-text);border:1px solid var(--j-border)' ?>;text-decoration:none"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
