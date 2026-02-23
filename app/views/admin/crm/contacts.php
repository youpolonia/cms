<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'CRM Contacts';
ob_start();
$statuses = ['new','contacted','qualified','proposal','won','lost'];
$sources = ['manual','contact_form','chatbot','import','api'];
$statusColors = ['new'=>'#22c55e','contacted'=>'#3b82f6','qualified'=>'#a855f7','proposal'=>'#f59e0b','won'=>'#10b981','lost'=>'#ef4444'];
?>
<style>
.ct-bar{display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap}
.ct-search{flex:1;min-width:200px;padding:10px 14px;border-radius:8px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text,#e2e8f0);font-size:.9rem}
.ct-filter{padding:8px 12px;border-radius:8px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text,#e2e8f0);font-size:.85rem}
.ct-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;overflow:hidden}
.ct-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.ct-tbl th,.ct-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.ct-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em}
.ct-tbl tr:hover{background:rgba(99,102,241,.05)}
.ct-name{font-weight:600;color:var(--text,#e2e8f0)}
.ct-email{font-size:.75rem;color:var(--muted,#94a3b8)}
.ct-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600}
.ct-avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#fff}
.ct-pag{display:flex;gap:6px;justify-content:center;margin-top:16px}
.ct-pag a,.ct-pag span{padding:6px 12px;border-radius:6px;font-size:.85rem;text-decoration:none}
.ct-pag a{background:#334155;color:#e2e8f0}
.ct-pag span{background:var(--primary,#6366f1);color:#fff}
a.ct-link{color:var(--primary,#6366f1);text-decoration:none}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 style="font-size:1.5rem;font-weight:700">👥 Contacts <span style="font-size:.9rem;font-weight:400;color:var(--muted)">(<?= $total ?>)</span></h1>
    <div style="display:flex;gap:8px">
        <a href="/admin/crm/import" style="padding:8px 14px;border-radius:8px;background:#334155;color:#e2e8f0;text-decoration:none;font-size:.85rem">📥 Import</a>
        <a href="/admin/crm/contacts/create" style="padding:8px 14px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;text-decoration:none;font-size:.85rem">+ Add Contact</a>
    </div>
</div>

<form method="get" class="ct-bar">
    <input type="text" name="q" value="<?= h($filters['search']) ?>" placeholder="Search contacts..." class="ct-search">
    <select name="status" class="ct-filter" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="source" class="ct-filter" onchange="this.form.submit()">
        <option value="">All Sources</option>
        <?php foreach ($sources as $s): ?>
            <option value="<?= $s ?>" <?= $filters['source'] === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div class="ct-card">
    <?php if (empty($contacts)): ?>
        <div style="padding:40px;text-align:center;color:var(--muted)">
            <p style="font-size:2rem;margin-bottom:8px">👥</p>
            <p>No contacts found. <a href="/admin/crm/contacts/create" class="ct-link">Add your first contact</a>.</p>
        </div>
    <?php else: ?>
        <table class="ct-tbl">
            <thead><tr><th></th><th>Name</th><th>Company</th><th>Source</th><th>Status</th><th>Score</th><th>Deals</th><th>Added</th></tr></thead>
            <tbody>
            <?php foreach ($contacts as $c): 
                $initials = strtoupper(mb_substr($c['first_name'],0,1) . mb_substr($c['last_name'] ?? '',0,1));
                $color = $statusColors[$c['status']] ?? '#94a3b8';
            ?>
                <tr>
                    <td><div class="ct-avatar" style="background:<?= $color ?>33;color:<?= $color ?>"><?= $initials ?></div></td>
                    <td>
                        <a href="/admin/crm/contacts/<?= $c['id'] ?>" class="ct-link">
                            <span class="ct-name"><?= h($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></span>
                        </a>
                        <div class="ct-email"><?= h($c['email'] ?? '') ?></div>
                    </td>
                    <td><?= h($c['company'] ?? '—') ?><br><span class="ct-email"><?= h($c['job_title'] ?? '') ?></span></td>
                    <td><span class="ct-email"><?= ucwords(str_replace('_', ' ', $c['source'])) ?></span></td>
                    <td><span class="ct-badge" style="background:<?= $color ?>22;color:<?= $color ?>"><?= ucfirst($c['status']) ?></span></td>
                    <td style="font-weight:600"><?= (int)$c['score'] ?></td>
                    <td><?= (int)($c['deals_count'] ?? 0) ?><?php if (($c['deals_value'] ?? 0) > 0): ?><br><span class="ct-email">$<?= number_format((float)$c['deals_value'], 0) ?></span><?php endif; ?></td>
                    <td><span class="ct-email"><?= date('M j, Y', strtotime($c['created_at'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="ct-pag">
    <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
        <?php if ($i === $page): ?><span><?= $i ?></span>
        <?php else: ?><a href="?page=<?= $i ?>&q=<?= urlencode($filters['search']) ?>&status=<?= $filters['status'] ?>&source=<?= $filters['source'] ?>"><?= $i ?></a><?php endif; ?>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
