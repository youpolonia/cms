<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'CRM Dashboard';
ob_start();
$fmt = fn($v) => number_format((float)$v, 0, '.', ',');
$fmtMoney = fn($v) => '$' . number_format((float)$v, 2, '.', ',');
?>
<style>
.crm-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:24px}
.crm-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:20px;text-align:center}
.crm-stat .num{font-size:1.8rem;font-weight:700;color:var(--text,#e2e8f0)}
.crm-stat .lbl{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.crm-stat.highlight{border-color:var(--primary,#6366f1)}
.crm-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:20px;margin-bottom:20px}
.crm-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin-bottom:16px}
.crm-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}
.crm-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.crm-tbl th,.crm-tbl td{padding:8px 12px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.crm-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase}
.crm-tbl tr:hover{background:rgba(99,102,241,.05)}
.crm-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600}
.crm-badge.new{background:#22c55e22;color:#22c55e}
.crm-badge.contacted{background:#3b82f622;color:#3b82f6}
.crm-badge.qualified{background:#a855f722;color:#a855f7}
.crm-badge.proposal{background:#f59e0b22;color:#f59e0b}
.crm-badge.won{background:#10b98122;color:#10b981}
.crm-badge.lost{background:#ef444422;color:#ef4444}
.crm-pipeline-mini{display:flex;gap:8px;overflow-x:auto;padding-bottom:8px}
.crm-pipe-col{flex:1;min-width:140px;background:var(--bg,#0f172a);border-radius:8px;padding:12px}
.crm-pipe-col h4{font-size:.75rem;color:var(--muted);margin-bottom:8px;text-transform:uppercase}
.crm-pipe-col .val{font-size:1.1rem;font-weight:700;color:var(--text)}
.crm-pipe-col .cnt{font-size:.7rem;color:var(--muted)}
a.crm-link{color:var(--primary,#6366f1);text-decoration:none}
a.crm-link:hover{text-decoration:underline}
.task-item{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border,#334155)}
.task-item:last-child{border:none}
.task-icon{font-size:1.2rem}
.task-info{flex:1}
.task-info .title{font-size:.85rem;color:var(--text)}
.task-info .meta{font-size:.75rem;color:var(--muted)}
.task-date{font-size:.75rem;color:var(--muted)}
.task-date.overdue{color:#ef4444}
@media(max-width:768px){.crm-grid{grid-template-columns:1fr}}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 style="font-size:1.5rem;font-weight:700">👥 CRM Dashboard</h1>
    <div style="display:flex;gap:8px">
        <a href="/admin/crm/contacts/create" style="padding:8px 16px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;text-decoration:none;font-size:.85rem">+ Add Contact</a>
        <a href="/admin/crm/pipeline" style="padding:8px 16px;border-radius:8px;background:#334155;color:#e2e8f0;text-decoration:none;font-size:.85rem">Pipeline →</a>
    </div>
</div>

<div class="crm-stats">
    <div class="crm-stat highlight"><div class="num"><?= $fmt($stats['total_contacts']) ?></div><div class="lbl">Total Contacts</div></div>
    <div class="crm-stat"><div class="num"><?= $fmt($stats['new_contacts']) ?></div><div class="lbl">New Leads</div></div>
    <div class="crm-stat"><div class="num"><?= $fmt($stats['this_month']) ?></div><div class="lbl">This Month</div></div>
    <div class="crm-stat"><div class="num"><?= $fmt($stats['total_deals']) ?></div><div class="lbl">Active Deals</div></div>
    <div class="crm-stat highlight"><div class="num"><?= $fmtMoney($stats['deals_value']) ?></div><div class="lbl">Pipeline Value</div></div>
    <div class="crm-stat"><div class="num"><?= $fmtMoney($stats['won_value']) ?></div><div class="lbl">Won Revenue</div></div>
    <div class="crm-stat"><div class="num"><?= $fmt($stats['pending_tasks']) ?></div><div class="lbl">Pending Tasks</div></div>
</div>

<div class="crm-card">
    <h3>Pipeline Overview</h3>
    <div class="crm-pipeline-mini">
        <?php 
        $stageLabels = ['lead'=>'Lead','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negotiation','won'=>'Won','lost'=>'Lost'];
        $stageColors = ['lead'=>'#94a3b8','qualified'=>'#a855f7','proposal'=>'#f59e0b','negotiation'=>'#3b82f6','won'=>'#10b981','lost'=>'#ef4444'];
        foreach ($pipeline as $stage => $data): 
        ?>
        <div class="crm-pipe-col">
            <h4 style="color:<?= $stageColors[$stage] ?>"><?= $stageLabels[$stage] ?></h4>
            <div class="val"><?= $fmtMoney($data['value']) ?></div>
            <div class="cnt"><?= $data['count'] ?> deal<?= $data['count'] === 1 ? '' : 's' ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="crm-grid">
    <div class="crm-card">
        <h3>Recent Contacts</h3>
        <?php if (empty($recent)): ?>
            <p style="color:var(--muted);font-size:.85rem">No contacts yet. <a href="/admin/crm/contacts/create" class="crm-link">Add your first contact</a> or <a href="/admin/crm/import" class="crm-link">import from form submissions</a>.</p>
        <?php else: ?>
            <table class="crm-tbl">
                <thead><tr><th>Name</th><th>Company</th><th>Status</th><th>Score</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $c): ?>
                    <tr>
                        <td><a href="/admin/crm/contacts/<?= $c['id'] ?>" class="crm-link"><?= h($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></a><br><span style="font-size:.75rem;color:var(--muted)"><?= h($c['email'] ?? '') ?></span></td>
                        <td><?= h($c['company'] ?? '—') ?></td>
                        <td><span class="crm-badge <?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                        <td><?= (int)$c['score'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:12px;text-align:center"><a href="/admin/crm/contacts" class="crm-link">View all contacts →</a></div>
        <?php endif; ?>
    </div>

    <div>
        <div class="crm-card">
            <h3>Upcoming Tasks</h3>
            <?php if (empty($tasks)): ?>
                <p style="color:var(--muted);font-size:.85rem;text-align:center">No pending tasks 🎉</p>
            <?php else: ?>
                <?php 
                $typeIcons = ['note'=>'📝','email'=>'✉️','call'=>'📞','meeting'=>'📅','task'=>'✅','chat'=>'💬','form_submit'=>'📋'];
                foreach ($tasks as $t): 
                    $overdue = $t['due_date'] && strtotime($t['due_date']) < time();
                ?>
                <div class="task-item">
                    <span class="task-icon"><?= $typeIcons[$t['type']] ?? '📌' ?></span>
                    <div class="task-info">
                        <div class="title"><?= h($t['title']) ?></div>
                        <div class="meta"><?= h($t['first_name'] . ' ' . ($t['last_name'] ?? '')) ?> · <?= h($t['company'] ?? '') ?></div>
                    </div>
                    <span class="task-date <?= $overdue ? 'overdue' : '' ?>"><?= date('M j', strtotime($t['due_date'])) ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="crm-card">
            <h3>Quick Actions</h3>
            <div style="display:flex;flex-direction:column;gap:8px">
                <a href="/admin/crm/contacts/create" style="padding:10px;background:var(--bg,#0f172a);border-radius:8px;color:var(--text);text-decoration:none;font-size:.85rem">➕ Add New Contact</a>
                <a href="/admin/crm/import" style="padding:10px;background:var(--bg,#0f172a);border-radius:8px;color:var(--text);text-decoration:none;font-size:.85rem">📥 Import from Forms</a>
                <a href="/admin/crm/pipeline" style="padding:10px;background:var(--bg,#0f172a);border-radius:8px;color:var(--text);text-decoration:none;font-size:.85rem">🏗️ Deal Pipeline</a>
                <a href="/admin/crm/contacts?sort=score+DESC" style="padding:10px;background:var(--bg,#0f172a);border-radius:8px;color:var(--text);text-decoration:none;font-size:.85rem">⭐ Top Scored Leads</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
