<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Chat Sessions';
$layout = 'admin';
ob_start();
?>
<style>
.cs-table{width:100%;border-collapse:collapse;font-size:.85rem}
.cs-table th,.cs-table td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.cs-table th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em}
.cs-table tr:hover{background:var(--bg,#0f172a)}
.cs-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.75rem;background:#334155;color:#e2e8f0}
.cs-pag{display:flex;gap:8px;justify-content:center;margin-top:20px}
.cs-pag a,.cs-pag span{padding:6px 12px;border-radius:6px;font-size:.85rem;text-decoration:none}
.cs-pag a{background:#334155;color:#e2e8f0}
.cs-pag a:hover{background:#475569}
.cs-pag span{background:var(--primary,#6366f1);color:#fff}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 style="font-size:1.5rem;font-weight:700">💬 Chat Sessions</h1>
    <a href="/admin/chat-settings" style="color:var(--primary,#6366f1);font-size:.85rem">← Back to Settings</a>
</div>

<div style="background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;overflow:hidden">
    <?php if (empty($sessions)): ?>
        <div style="padding:40px;text-align:center;color:var(--muted,#94a3b8)">
            <p style="font-size:1.5rem;margin-bottom:8px">🤖</p>
            <p>No chat sessions yet. Enable the chatbot and wait for visitors!</p>
        </div>
    <?php else: ?>
        <table class="cs-table">
            <thead>
                <tr>
                    <th>Session</th>
                    <th>IP</th>
                    <th>Page</th>
                    <th>Messages</th>
                    <th>Started</th>
                    <th>Last Active</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $s): ?>
                <tr>
                    <td><code style="font-size:.75rem;color:var(--muted)"><?= h(substr($s['session_id'], 0, 12)) ?>…</code></td>
                    <td><?= h($s['ip_address'] ?? '—') ?></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= h($s['page_url'] ?? '—') ?></td>
                    <td><span class="cs-badge"><?= (int)($s['msg_count'] ?? 0) ?></span></td>
                    <td><?= h($s['created_at'] ?? '') ?></td>
                    <td><?= h($s['updated_at'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="cs-pag">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
            <span><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>

<div style="text-align:center;margin-top:16px;color:var(--muted,#94a3b8);font-size:.8rem">
    Total: <?= $total ?> session<?= $total === 1 ? '' : 's' ?>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
