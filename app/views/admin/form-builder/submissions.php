<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = $title ?? 'Submissions';
$form = $form ?? [];
$submissions = $submissions ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;

$formFields = $form['fields'] ?? [];
$displayFields = [];
foreach ($formFields as $f) {
    if (in_array($f['type'] ?? '', ['heading', 'paragraph'])) continue;
    $displayFields[] = $f;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">📊 <?= h($form['name'] ?? 'Form') ?> — Submissions</h1>
        <div style="font-size:14px;color:var(--text-muted);margin-top:4px;"><?= $total ?> total submissions</div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/form-builder/export/<?= (int)($form['id'] ?? 0) ?>" class="btn btn-secondary btn-sm">📥 Export CSV</a>
        <a href="/admin/form-builder" class="btn btn-secondary btn-sm">← Back to Forms</a>
    </div>
</div>

<?php if (empty($submissions)): ?>
    <div class="card">
        <div class="card-body" style="text-align:center;padding:60px 20px;">
            <div style="font-size:48px;margin-bottom:16px;">📭</div>
            <h2 style="margin-bottom:8px;">No submissions yet</h2>
            <p style="color:var(--text-muted);">Submissions will appear here when users fill in the form.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <?php
                    $maxCols = min(count($displayFields), 5);
                    for ($i = 0; $i < $maxCols; $i++):
                    ?>
                        <th><?= h($displayFields[$i]['label'] ?? $displayFields[$i]['name'] ?? '') ?></th>
                    <?php endfor; ?>
                    <th>Date</th>
                    <th>IP</th>
                    <th style="width:60px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $sub): ?>
                    <?php $isUnread = !$sub['is_read']; ?>
                    <tr class="fb-sub-row" data-sub-id="<?= (int)$sub['id'] ?>" style="cursor:pointer;<?= $isUnread ? 'font-weight:600;' : '' ?>">
                        <td>
                            <?php if ($isUnread): ?>
                                <span style="display:inline-block;width:8px;height:8px;background:var(--accent);border-radius:50;" title="Unread"></span>
                            <?php endif; ?>
                            <?= (int)$sub['id'] ?>
                        </td>
                        <?php
                        $data = $sub['data'] ?? [];
                        for ($i = 0; $i < $maxCols; $i++):
                            $fn = $displayFields[$i]['name'] ?? '';
                            $val = $data[$fn] ?? '';
                            if (is_array($val)) $val = implode(', ', $val);
                            $val = mb_substr((string)$val, 0, 60);
                        ?>
                            <td><?= h($val) ?></td>
                        <?php endfor; ?>
                        <td style="font-size:12px;white-space:nowrap;"><?= date('M j, Y H:i', strtotime($sub['created_at'])) ?></td>
                        <td style="font-size:12px;color:var(--text-muted);"><?= h($sub['ip_address'] ?? '') ?></td>
                        <td>
                            <button class="btn btn-ghost btn-sm fb-expand-btn" data-sub-id="<?= (int)$sub['id'] ?>" title="Expand">▾</button>
                        </td>
                    </tr>
                    <tr class="fb-sub-detail" id="sub-detail-<?= (int)$sub['id'] ?>" style="display:none;">
                        <td colspan="<?= $maxCols + 4 ?>">
                            <div style="padding:12px;background:var(--bg-tertiary);border-radius:var(--radius);">
                                <table style="width:100%;font-size:13px;">
                                    <?php foreach ($displayFields as $df): ?>
                                        <?php
                                        $fn = $df['name'] ?? '';
                                        $val = $data[$fn] ?? '';
                                        if (is_array($val)) $val = implode(', ', $val);
                                        ?>
                                        <tr>
                                            <td style="padding:4px 12px 4px 0;font-weight:600;color:var(--text-secondary);width:160px;vertical-align:top;"><?= h($df['label'] ?? $fn) ?></td>
                                            <td style="padding:4px 0;">
                                                <?php if ($df['type'] === 'file' && $val): ?>
                                                    <a href="<?= h($val) ?>" target="_blank">📎 Download</a>
                                                <?php else: ?>
                                                    <?= h((string)$val) ?: '<span style="color:var(--text-muted);">—</span>' ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td style="padding:4px 12px 4px 0;font-weight:600;color:var(--text-secondary);">Page URL</td>
                                        <td style="padding:4px 0;font-size:12px;color:var(--text-muted);"><?= h($sub['page_url'] ?? '') ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:4px 12px 4px 0;font-weight:600;color:var(--text-secondary);">User Agent</td>
                                        <td style="padding:4px 0;font-size:11px;color:var(--text-muted);word-break:break-all;"><?= h($sub['user_agent'] ?? '') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:4px;margin-top:20px;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === $page): ?>
                    <span class="btn btn-primary btn-sm" style="pointer-events:none;"><?= $p ?></span>
                <?php else: ?>
                    <a href="/admin/form-builder/submissions/<?= (int)$form['id'] ?>?page=<?= $p ?>" class="btn btn-secondary btn-sm"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
document.querySelectorAll('.fb-sub-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.closest('.fb-expand-btn')) return;
        const id = this.getAttribute('data-sub-id');
        toggleDetail(id, this);
    });
});

document.querySelectorAll('.fb-expand-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-sub-id');
        const row = this.closest('tr');
        toggleDetail(id, row);
    });
});

function toggleDetail(id, row) {
    const detail = document.getElementById('sub-detail-' + id);
    if (!detail) return;

    const isVisible = detail.style.display !== 'none';
    detail.style.display = isVisible ? 'none' : 'table-row';

    // Mark as read
    if (!isVisible && row.style.fontWeight === '600') {
        row.style.fontWeight = '';
        fetch('/admin/form-builder/mark-read/' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= h(csrf_token()) ?>'},
            body: JSON.stringify({csrf_token: '<?= h(csrf_token()) ?>'})
        });
    }
}
</script>
