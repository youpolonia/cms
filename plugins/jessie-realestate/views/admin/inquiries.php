<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
$statusFilter = ($_GET['status'] ?? null) ?: null;
$sql = "SELECT i.*, p.title AS property_title, p.slug AS property_slug FROM re_inquiries i LEFT JOIN re_properties p ON i.property_id = p.id";
if ($statusFilter) { $sql .= " WHERE i.status = ?"; }
$sql .= " ORDER BY i.created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($statusFilter ? [$statusFilter] : []);
$inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);
$counts = $pdo->query("SELECT status, COUNT(*) AS cnt FROM re_inquiries GROUP BY status")->fetchAll(\PDO::FETCH_KEY_PAIR);
ob_start();
?>
<style>
.re-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.re-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.re-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-btn{padding:6px 14px;border-radius:8px;font-size:.78rem;border:1px solid var(--border,#334155);background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);text-decoration:none}
.filter-btn.active{background:rgba(99,102,241,.15);border-color:#6366f1;color:#a5b4fc}
.re-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.re-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.re-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0);vertical-align:top}
.re-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase}
.status-new{background:rgba(245,158,11,.15);color:#fbbf24}
.status-read{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-replied{background:rgba(16,185,129,.15);color:#34d399}
.status-archived{background:rgba(148,163,184,.15);color:#94a3b8}
.msg-preview{font-size:.78rem;color:var(--muted,#94a3b8);max-width:300px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.action-btn{background:none;border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:3px 8px;border-radius:4px;cursor:pointer;font-size:.72rem}
.action-btn:hover{border-color:#6366f1;color:#a5b4fc}
</style>
<div class="re-wrap">
    <div class="re-header"><h1>📩 Inquiries</h1><a href="/admin/realestate" class="btn-secondary">← Dashboard</a></div>
    <div class="filter-bar">
        <a href="/admin/realestate/inquiries" class="filter-btn <?= !$statusFilter?'active':'' ?>">All (<?= array_sum($counts) ?>)</a>
        <?php foreach (['new','read','replied','archived'] as $s): ?><a href="?status=<?= $s ?>" class="filter-btn <?= $statusFilter===$s?'active':'' ?>"><?= ucfirst($s) ?> (<?= $counts[$s]??0 ?>)</a><?php endforeach; ?>
    </div>
    <table class="re-table"><thead><tr><th>Contact</th><th>Property</th><th>Message</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>
        <?php foreach ($inquiries as $inq): ?>
        <tr>
            <td><strong><?= h($inq['name']) ?></strong><br><span style="font-size:.75rem;color:var(--muted)"><?= h($inq['email']) ?><?= $inq['phone'] ? ' · ' . h($inq['phone']) : '' ?></span></td>
            <td style="font-size:.82rem"><a href="/properties/<?= h($inq['property_slug'] ?? '') ?>" style="color:#a5b4fc"><?= h($inq['property_title'] ?? 'Unknown') ?></a></td>
            <td><div class="msg-preview"><?= h($inq['message'] ?? '—') ?></div></td>
            <td><span class="status-badge status-<?= h($inq['status']) ?>"><?= h($inq['status']) ?></span></td>
            <td style="font-size:.78rem;color:var(--muted);white-space:nowrap"><?= date('M j, H:i', strtotime($inq['created_at'])) ?></td>
            <td style="white-space:nowrap">
                <form method="post" action="/admin/realestate/inquiries/<?= $inq['id'] ?>/status" style="display:inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <?php if ($inq['status'] === 'new'): ?><input type="hidden" name="status" value="read"><button class="action-btn" title="Mark Read">👁</button>
                    <?php elseif ($inq['status'] === 'read'): ?><input type="hidden" name="status" value="replied"><button class="action-btn" title="Mark Replied">✅</button>
                    <?php else: ?><input type="hidden" name="status" value="archived"><button class="action-btn" title="Archive">📦</button><?php endif; ?>
                </form>
                <form method="post" action="/admin/realestate/inquiries/<?= $inq['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button class="action-btn" title="Delete">🗑️</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($inquiries)): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No inquiries found.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Inquiries'; require CMS_APP . '/views/admin/layouts/topbar.php';
