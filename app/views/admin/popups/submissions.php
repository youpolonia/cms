<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
ob_start();
?>
<style>
.sub-card{background:var(--bg-primary);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.sub-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.sub-tbl th,.sub-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border)}
.sub-tbl th{color:var(--text-muted);font-weight:600;font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;background:var(--bg-secondary)}
.sub-tbl tr:hover{background:var(--bg-secondary)}
.sub-tbl .new-row{border-left:3px solid var(--accent)}
.sub-pag{display:flex;gap:4px;margin-top:16px;justify-content:center}
.sub-pag a,.sub-pag span{padding:6px 12px;border-radius:6px;font-size:.8rem;text-decoration:none;border:1px solid var(--border);color:var(--text-secondary)}
.sub-pag a:hover{background:var(--accent-muted);color:var(--accent);border-color:var(--accent)}
.sub-pag .cur{background:var(--accent);color:#fff;border-color:var(--accent)}
.sub-empty{padding:40px;text-align:center;color:var(--text-muted)}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="/admin/popups" style="color:var(--text-muted);font-size:1.2rem">←</a>
        <h1 style="font-size:1.3rem;font-weight:700">📩 Submissions: <?= h($popup['name']) ?></h1>
        <span style="font-size:.8rem;color:var(--text-muted)">(<?= $total ?> total)</span>
    </div>
    <?php if ($total > 0): ?>
    <a href="/admin/popups/<?= $popup['id'] ?>/export" class="btn btn-secondary" style="font-size:.8rem">📥 Export CSV</a>
    <?php endif; ?>
</div>

<div class="sub-card">
<?php if (empty($submissions)): ?>
    <div class="sub-empty">
        <p style="font-size:2rem">📭</p>
        <p>No submissions yet for this popup.</p>
    </div>
<?php else: ?>
    <table class="sub-tbl">
        <thead>
            <tr>
                <th>#</th>
                <th>Email</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Page URL</th>
                <th>IP</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($submissions as $s): ?>
            <tr>
                <td style="color:var(--text-muted)"><?= $s['id'] ?></td>
                <td style="font-weight:600"><?= h($s['parsed']['email'] ?? '—') ?></td>
                <td><?= h($s['parsed']['name'] ?? '—') ?></td>
                <td><?= h($s['parsed']['phone'] ?? '—') ?></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= h($s['page_url'] ?? '') ?>"><?= h($s['page_url'] ?? '—') ?></td>
                <td style="color:var(--text-muted);font-size:.75rem"><?= h($s['ip_address'] ?? '') ?></td>
                <td style="white-space:nowrap;font-size:.8rem"><?= h($s['created_at'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="sub-pag">
    <?php if ($page > 1): ?>
        <a href="/admin/popups/<?= $popup['id'] ?>/submissions?page=<?= $page - 1 ?>">← Prev</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="cur"><?= $i ?></span>
        <?php else: ?>
            <a href="/admin/popups/<?= $popup['id'] ?>/submissions?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
        <a href="/admin/popups/<?= $popup['id'] ?>/submissions?page=<?= $page + 1 ?>">Next →</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); require CMS_APP . '/views/admin/layouts/topbar.php';
