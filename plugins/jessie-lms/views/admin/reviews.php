<?php
/**
 * LMS — Reviews Management
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-review.php';
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-course.php';

$pending = \LmsReview::getPending();
$tab = $_GET['tab'] ?? 'pending';
$courseId = (int)($_GET['course_id'] ?? 0);
$approved = $courseId ? \LmsReview::getForCourse($courseId) : [];
$courses = \LmsCourse::getAll(1, 100)['courses'] ?? [];

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap" style="max-width:900px">
    <div class="j-settings-header">
        <h1>⭐ Course Reviews</h1>
        <a href="/admin/lms" class="j-btn-secondary">← Dashboard</a>
    </div>

    <!-- Tabs -->
    <div style="display:flex;gap:4px;margin-bottom:20px">
        <a href="?tab=pending" style="padding:8px 18px;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;<?= $tab === 'pending' ? 'background:var(--j-accent);color:#fff' : 'background:var(--j-card);color:var(--j-text);border:1px solid var(--j-border)' ?>">
            Pending <?php if (count($pending)): ?><span style="background:var(--j-danger);color:#fff;padding:1px 7px;border-radius:10px;font-size:.7rem;margin-left:4px"><?= count($pending) ?></span><?php endif; ?>
        </a>
        <a href="?tab=approved" style="padding:8px 18px;border-radius:8px;font-size:.85rem;font-weight:600;text-decoration:none;<?= $tab === 'approved' ? 'background:var(--j-accent);color:#fff' : 'background:var(--j-card);color:var(--j-text);border:1px solid var(--j-border)' ?>">Approved</a>
    </div>

    <?php if ($tab === 'pending'): ?>
    <!-- Pending Reviews -->
    <div class="j-card">
        <h3>Awaiting Moderation</h3>
        <?php if (empty($pending)): ?>
        <p style="text-align:center;color:var(--j-muted);padding:20px">No pending reviews 🎉</p>
        <?php else: ?>
        <?php foreach ($pending as $r): ?>
        <div style="border:1px solid var(--j-border);border-radius:10px;padding:16px;margin-bottom:12px">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px">
                <div>
                    <strong><?= h($r['name'] ?? 'Anonymous') ?></strong>
                    <span style="color:var(--j-muted);font-size:.8rem"> — <?= h($r['email'] ?? '') ?></span>
                    <div style="color:var(--j-warning);font-size:.9rem;margin-top:2px"><?= str_repeat('⭐', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                </div>
                <span style="font-size:.75rem;color:var(--j-muted)"><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></span>
            </div>
            <p style="font-size:.85rem;color:var(--j-text);margin-bottom:12px;line-height:1.5"><?= nl2br(h($r['review'] ?? '')) ?></p>
            <div style="font-size:.78rem;color:var(--j-muted);margin-bottom:12px">Course: <?= h($r['course_title'] ?? 'N/A') ?></div>
            <div style="display:flex;gap:8px">
                <form method="post" action="/admin/lms/reviews/<?= $r['id'] ?>/approve" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <button type="submit" class="j-btn" style="padding:6px 16px;font-size:.8rem">✅ Approve</button>
                </form>
                <form method="post" action="/admin/lms/reviews/<?= $r['id'] ?>/reject" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <button type="submit" class="j-btn-secondary" style="padding:6px 16px;font-size:.8rem;color:var(--j-danger);border-color:var(--j-danger)">🗑️ Reject</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- Approved Reviews -->
    <div class="j-card" style="padding:16px;margin-bottom:16px">
        <form method="get" style="display:flex;gap:12px;align-items:center">
            <input type="hidden" name="tab" value="approved">
            <select name="course_id" style="background:var(--j-bg);border:1px solid var(--j-border);color:var(--j-text);padding:8px 12px;border-radius:8px;font-size:.85rem">
                <option value="">Select Course</option>
                <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= h($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="j-btn" style="padding:8px 16px;font-size:.85rem">Show</button>
        </form>
    </div>

    <?php if ($courseId && !empty($approved)): ?>
    <div class="j-card">
        <h3>Approved Reviews for "<?= h($courses[array_search($courseId, array_column($courses, 'id'))]['title'] ?? 'Course') ?>"</h3>
        <?php foreach ($approved as $r): ?>
        <div style="border-bottom:1px solid var(--j-border);padding:12px 0;<?= $r === end($approved) ? 'border:none' : '' ?>">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <div><strong style="font-size:.9rem"><?= h($r['name'] ?? 'Anonymous') ?></strong> <span style="color:var(--j-warning);font-size:.85rem"><?= str_repeat('⭐', (int)$r['rating']) ?></span></div>
                <span style="font-size:.75rem;color:var(--j-muted)"><?= date('M j', strtotime($r['created_at'] ?? 'now')) ?></span>
            </div>
            <p style="font-size:.85rem;color:var(--j-muted);margin-top:4px"><?= h($r['review'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php elseif ($courseId): ?>
    <div class="j-card" style="text-align:center;color:var(--j-muted);padding:30px">No approved reviews for this course</div>
    <?php else: ?>
    <div class="j-card" style="text-align:center;color:var(--j-muted);padding:30px">Select a course to view reviews</div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
