<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$c = $contact;
$pageTitle = h($c['first_name'] . ' ' . ($c['last_name'] ?? ''));
ob_start();
$statusColors = ['new'=>'#22c55e','contacted'=>'#3b82f6','qualified'=>'#a855f7','proposal'=>'#f59e0b','won'=>'#10b981','lost'=>'#ef4444'];
$color = $statusColors[$c['status']] ?? '#94a3b8';
$typeIcons = ['note'=>'📝','email'=>'✉️','call'=>'📞','meeting'=>'📅','task'=>'✅','chat'=>'💬','form_submit'=>'📋'];
?>
<style>
.cv-grid{display:grid;grid-template-columns:1fr 2fr;gap:20px}
.cv-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:20px;margin-bottom:16px}
.cv-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin-bottom:16px}
.cv-avatar{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;margin:0 auto 16px}
.cv-name{text-align:center;font-size:1.2rem;font-weight:700;color:var(--text,#e2e8f0)}
.cv-company{text-align:center;font-size:.85rem;color:var(--muted,#94a3b8)}
.cv-info{font-size:.85rem;color:var(--text,#e2e8f0)}
.cv-info dt{color:var(--muted);font-size:.75rem;margin-top:12px}
.cv-info dd{margin:2px 0 0 0}
.cv-badge{display:inline-block;padding:4px 12px;border-radius:12px;font-size:.8rem;font-weight:600}
.cv-timeline{position:relative;padding-left:20px}
.cv-timeline::before{content:'';position:absolute;left:6px;top:0;bottom:0;width:2px;background:var(--border,#334155)}
.cv-tl-item{position:relative;padding-bottom:16px}
.cv-tl-item::before{content:'';position:absolute;left:-17px;top:4px;width:10px;height:10px;border-radius:50%;background:var(--border,#334155);border:2px solid var(--bg-card,#1e293b)}
.cv-tl-title{font-size:.85rem;color:var(--text)}
.cv-tl-meta{font-size:.75rem;color:var(--muted)}
.cv-tl-desc{font-size:.8rem;color:var(--muted);margin-top:4px}
.cv-deal{background:var(--bg,#0f172a);border-radius:8px;padding:12px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center}
.cv-deal-title{font-size:.85rem;font-weight:600;color:var(--text)}
.cv-deal-val{font-size:.9rem;font-weight:700;color:var(--text)}
.cv-deal-stage{font-size:.7rem;color:var(--muted)}
.cv-act-form{display:grid;grid-template-columns:auto 1fr auto;gap:8px;align-items:end}
.cv-act-form select,.cv-act-form input{padding:8px;border-radius:6px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text);font-size:.85rem}
.cv-act-form button{padding:8px 14px;border-radius:6px;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;font-size:.85rem}
.cv-btn-row{display:flex;gap:8px;margin-top:16px;justify-content:center}
.cv-btn{padding:8px 14px;border-radius:8px;font-size:.8rem;text-decoration:none;border:none;cursor:pointer}
@media(max-width:768px){.cv-grid{grid-template-columns:1fr}}
</style>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <a href="/admin/crm/contacts" style="color:var(--muted);text-decoration:none;font-size:1.2rem">←</a>
    <h1 style="font-size:1.5rem;font-weight:700"><?= h($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></h1>
    <span class="cv-badge" style="background:<?= $color ?>22;color:<?= $color ?>"><?= ucfirst($c['status']) ?></span>
</div>

<div class="cv-grid">
    <div>
        <div class="cv-card">
            <?php $initials = strtoupper(mb_substr($c['first_name'],0,1) . mb_substr($c['last_name'] ?? '',0,1)); ?>
            <div class="cv-avatar" style="background:<?= $color ?>33;color:<?= $color ?>"><?= $initials ?></div>
            <div class="cv-name"><?= h($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></div>
            <div class="cv-company"><?= h(($c['job_title'] ?? '') . ($c['company'] ? ' @ ' . $c['company'] : '')) ?></div>

            <dl class="cv-info">
                <?php if ($c['email']): ?><dt>Email</dt><dd><a href="mailto:<?= h($c['email']) ?>" style="color:var(--primary,#6366f1)"><?= h($c['email']) ?></a></dd><?php endif; ?>
                <?php if ($c['phone']): ?><dt>Phone</dt><dd><?= h($c['phone']) ?></dd><?php endif; ?>
                <dt>Source</dt><dd><?= ucwords(str_replace('_', ' ', $c['source'])) ?></dd>
                <dt>Score</dt><dd><strong><?= (int)$c['score'] ?></strong> / 100</dd>
                <?php if ($c['tags']): ?><dt>Tags</dt><dd><?php foreach (explode(',', $c['tags']) as $t): ?><span style="display:inline-block;padding:2px 6px;margin:2px;border-radius:4px;background:#334155;font-size:.75rem"><?= h(trim($t)) ?></span><?php endforeach; ?></dd><?php endif; ?>
                <dt>Added</dt><dd><?= date('M j, Y H:i', strtotime($c['created_at'])) ?></dd>
                <?php if ($c['last_contacted_at']): ?><dt>Last Contact</dt><dd><?= date('M j, Y', strtotime($c['last_contacted_at'])) ?></dd><?php endif; ?>
            </dl>

            <div class="cv-btn-row">
                <a href="/admin/crm/contacts/<?= $c['id'] ?>/edit" class="cv-btn" style="background:#334155;color:#e2e8f0">✏️ Edit</a>
                <form method="post" action="/admin/crm/contacts/<?= $c['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this contact?')">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="cv-btn" style="background:#ef444433;color:#ef4444">🗑️ Delete</button>
                </form>
            </div>
        </div>

        <div class="cv-card">
            <h3>Deals (<?= count($deals) ?>)</h3>
            <?php if (empty($deals)): ?>
                <p style="font-size:.85rem;color:var(--muted);text-align:center">No deals yet</p>
            <?php else: foreach ($deals as $d): ?>
                <div class="cv-deal">
                    <div>
                        <div class="cv-deal-title"><?= h($d['title']) ?></div>
                        <div class="cv-deal-stage"><?= ucfirst($d['stage']) ?> · <?= (int)$d['probability'] ?>%</div>
                    </div>
                    <div class="cv-deal-val">$<?= number_format((float)$d['value'], 0) ?></div>
                </div>
            <?php endforeach; endif; ?>

            <details style="margin-top:12px">
                <summary style="cursor:pointer;font-size:.85rem;color:var(--primary,#6366f1)">+ Add Deal</summary>
                <form method="post" action="/admin/crm/deals/create" style="margin-top:8px">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="contact_id" value="<?= $c['id'] ?>">
                    <div style="display:grid;gap:8px">
                        <input type="text" name="title" placeholder="Deal title" required style="padding:8px;border-radius:6px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text);font-size:.85rem">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                            <input type="number" name="value" placeholder="Value" step="0.01" style="padding:8px;border-radius:6px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text);font-size:.85rem">
                            <select name="stage" style="padding:8px;border-radius:6px;border:1px solid var(--border,#334155);background:var(--bg,#0f172a);color:var(--text);font-size:.85rem">
                                <option value="lead">Lead</option><option value="qualified">Qualified</option><option value="proposal">Proposal</option><option value="negotiation">Negotiation</option>
                            </select>
                        </div>
                        <button type="submit" style="padding:8px;border-radius:6px;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;font-size:.85rem">Create Deal</button>
                    </div>
                </form>
            </details>
        </div>
    </div>

    <div>
        <div class="cv-card">
            <h3>Add Activity</h3>
            <form method="post" action="/admin/crm/activities/add" class="cv-act-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="contact_id" value="<?= $c['id'] ?>">
                <select name="type">
                    <option value="note">📝 Note</option><option value="email">✉️ Email</option>
                    <option value="call">📞 Call</option><option value="meeting">📅 Meeting</option>
                    <option value="task">✅ Task</option>
                </select>
                <input type="text" name="title" placeholder="Activity title..." required>
                <button type="submit">Add</button>
            </form>
        </div>

        <?php if ($c['notes']): ?>
        <div class="cv-card">
            <h3>Notes</h3>
            <p style="font-size:.85rem;color:var(--text);line-height:1.6;white-space:pre-wrap"><?= h($c['notes']) ?></p>
        </div>
        <?php endif; ?>

        <div class="cv-card">
            <h3>Activity Timeline</h3>
            <?php if (empty($activities)): ?>
                <p style="font-size:.85rem;color:var(--muted);text-align:center">No activities recorded</p>
            <?php else: ?>
                <div class="cv-timeline">
                    <?php foreach ($activities as $a): ?>
                    <div class="cv-tl-item">
                        <div class="cv-tl-title"><?= $typeIcons[$a['type']] ?? '📌' ?> <?= h($a['title']) ?></div>
                        <div class="cv-tl-meta"><?= date('M j, Y H:i', strtotime($a['created_at'])) ?> · <?= h($a['created_by'] ?? '') ?><?= $a['completed'] ? ' ✅' : '' ?></div>
                        <?php if ($a['description']): ?><div class="cv-tl-desc"><?= h($a['description']) ?></div><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
