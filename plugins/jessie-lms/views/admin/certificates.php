<?php
/**
 * LMS — Certificates Management
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-certificate.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$result = \LmsCertificate::getAll($page, 25);

// Handle verify
$verifyResult = null;
if (!empty($_GET['verify'])) {
    $verifyResult = \LmsCertificate::verify($_GET['verify']);
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap" style="max-width:900px">
    <div class="j-settings-header">
        <h1>🏅 Certificates</h1>
        <a href="/admin/lms" class="j-btn-secondary">← Dashboard</a>
    </div>

    <!-- Verify Tool -->
    <div class="j-card">
        <h3>🔍 Verify Certificate</h3>
        <form method="get" style="display:flex;gap:12px;align-items:center">
            <input type="text" name="verify" placeholder="Enter certificate code..." value="<?= h($_GET['verify'] ?? '') ?>" style="flex:1;background:var(--j-bg);border:1px solid var(--j-border);color:var(--j-text);padding:10px 12px;border-radius:8px;font-size:.85rem;font-family:monospace">
            <button type="submit" class="j-btn" style="padding:10px 20px;font-size:.85rem">Verify</button>
        </form>
        <?php if ($verifyResult !== null): ?>
        <div style="margin-top:12px;padding:12px;border-radius:8px;<?= $verifyResult ? 'background:rgba(34,197,94,.1);border:1px solid #22c55e;color:#86efac' : 'background:rgba(239,68,68,.1);border:1px solid #ef4444;color:#fca5a5' ?>;font-size:.85rem">
            <?php if ($verifyResult): ?>
            ✅ <strong>Valid Certificate</strong><br>
            Student: <?= h($verifyResult['student_name'] ?? $verifyResult['email'] ?? 'N/A') ?><br>
            Course: <?= h($verifyResult['course_title'] ?? 'N/A') ?><br>
            Issued: <?= date('M j, Y', strtotime($verifyResult['issued_at'] ?? 'now')) ?>
            <?php else: ?>
            ❌ Invalid certificate code
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Certificates List -->
    <div class="j-card" style="overflow-x:auto">
        <h3>Issued Certificates</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.85rem">
            <thead>
                <tr style="border-bottom:1px solid var(--j-border);text-align:left">
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Code</th>
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Student</th>
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Course</th>
                    <th style="padding:10px;color:var(--j-muted);font-size:.75rem;text-transform:uppercase">Issued</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['certificates'] ?? [] as $c): ?>
                <tr style="border-bottom:1px solid rgba(51,65,85,.5)">
                    <td style="padding:10px"><code style="background:var(--j-bg);padding:2px 8px;border-radius:4px;font-size:.8rem"><?= h($c['code'] ?? '') ?></code></td>
                    <td style="padding:10px"><strong><?= h($c['student_name'] ?? $c['email'] ?? 'N/A') ?></strong></td>
                    <td style="padding:10px"><?= h($c['course_title'] ?? 'N/A') ?></td>
                    <td style="padding:10px;color:var(--j-muted);font-size:.78rem"><?= date('M j, Y', strtotime($c['issued_at'] ?? 'now')) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($result['certificates'])): ?>
                <tr><td colspan="4" style="padding:30px;text-align:center;color:var(--j-muted)">No certificates issued yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($result['pages'] ?? 1) > 1): ?>
    <div style="text-align:center;margin-top:16px;display:flex;gap:6px;justify-content:center">
        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
        <a href="?page=<?= $p ?>" style="padding:6px 12px;border-radius:6px;font-size:.8rem;<?= $p == $page ? 'background:var(--j-accent);color:#fff' : 'background:var(--j-card);color:var(--j-text);border:1px solid var(--j-border)' ?>;text-decoration:none"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
