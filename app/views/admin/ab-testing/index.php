<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'A/B Testing';
ob_start();
$statusColors = ['running'=>'#22c55e','paused'=>'#f59e0b','completed'=>'#94a3b8'];
?>
<style>
.ab-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
.ab-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-align:center}
.ab-stat .num{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0)}.ab-stat .lbl{font-size:.75rem;color:var(--muted,#94a3b8)}
.ab-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;overflow:hidden}
.ab-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.ab-tbl th,.ab-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.ab-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase}
.ab-tbl tr:hover{background:rgba(99,102,241,.05)}
.ab-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.7rem;font-weight:600}
.ab-bar{height:6px;border-radius:3px;background:#334155;position:relative;margin-top:4px}
.ab-bar-fill{height:100%;border-radius:3px;position:absolute;top:0;left:0}
.ab-vs{display:flex;gap:8px;align-items:center;font-size:.8rem}
.ab-vs .a{color:#6366f1}.ab-vs .b{color:#f59e0b}.ab-vs .sep{color:var(--muted)}
a.ab-link{color:var(--primary,#6366f1);text-decoration:none;font-size:.8rem}
.ab-actions{display:flex;gap:4px}
.ab-actions form{display:inline}
.ab-actions button,.ab-actions a{padding:4px 8px;border-radius:4px;font-size:.75rem;border:none;cursor:pointer;text-decoration:none}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 style="font-size:1.5rem;font-weight:700">🔬 A/B Testing</h1>
    <a href="/admin/ab-testing/create" style="padding:8px 16px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;text-decoration:none;font-size:.85rem">+ New Test</a>
</div>

<div class="ab-stats">
    <div class="ab-stat"><div class="num"><?= $stats['total'] ?></div><div class="lbl">Total Tests</div></div>
    <div class="ab-stat"><div class="num" style="color:#22c55e"><?= $stats['running'] ?></div><div class="lbl">Running</div></div>
    <div class="ab-stat"><div class="num"><?= $stats['completed'] ?></div><div class="lbl">Completed</div></div>
</div>

<div class="ab-card">
<?php if (empty($tests)): ?>
    <div style="padding:40px;text-align:center;color:var(--muted)">
        <p style="font-size:2rem">🔬</p>
        <p>No A/B tests yet. <a href="/admin/ab-testing/create" class="ab-link">Create your first test</a></p>
        <p style="font-size:.8rem;margin-top:8px">Test headlines, CTAs, images — find what converts best.</p>
    </div>
<?php else: ?>
    <table class="ab-tbl">
        <thead><tr><th>Test</th><th>Type</th><th>Variants</th><th>Views</th><th>Conversions</th><th>Status</th><th>Winner</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tests as $t): $color = $statusColors[$t['status']] ?? '#94a3b8'; ?>
            <tr>
                <td>
                    <a href="/admin/ab-testing/<?= $t['id'] ?>/results" class="ab-link" style="font-weight:600;font-size:.9rem"><?= h($t['name']) ?></a>
                    <?php if ($t['page_title']): ?><div style="font-size:.7rem;color:var(--muted)">Page: <?= h($t['page_title']) ?></div><?php endif; ?>
                </td>
                <td><span style="font-size:.75rem;color:var(--muted)"><?= ucfirst($t['type']) ?></span></td>
                <td>
                    <div class="ab-vs">
                        <span class="a">A: <?= h(mb_substr($t['variant_a'], 0, 25)) ?></span>
                        <span class="sep">vs</span>
                        <span class="b">B: <?= h(mb_substr($t['variant_b'], 0, 25)) ?></span>
                    </div>
                </td>
                <td>
                    <span style="color:#6366f1"><?= number_format($t['views_a']) ?></span> /
                    <span style="color:#f59e0b"><?= number_format($t['views_b']) ?></span>
                </td>
                <td>
                    <div class="ab-vs">
                        <span class="a"><?= $t['rate_a'] ?>%</span>
                        <span class="sep">vs</span>
                        <span class="b"><?= $t['rate_b'] ?>%</span>
                    </div>
                </td>
                <td><span class="ab-badge" style="background:<?= $color ?>22;color:<?= $color ?>"><?= ucfirst($t['status']) ?></span></td>
                <td>
                    <?php if ($t['winner'] !== 'none'): ?>
                        <span style="font-weight:700;color:<?= $t['winner'] === 'a' ? '#6366f1' : '#f59e0b' ?>">🏆 <?= strtoupper($t['winner']) ?></span>
                    <?php elseif ($t['suggested_winner']): ?>
                        <span style="font-size:.75rem;color:var(--muted)">→ <?= strtoupper($t['suggested_winner']) ?></span>
                    <?php else: ?>
                        <span style="color:var(--muted)">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="ab-actions">
                        <?php if ($t['status'] !== 'completed'): ?>
                        <form method="post" action="/admin/ab-testing/<?= $t['id'] ?>/toggle"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button style="background:#334155;color:#e2e8f0"><?= $t['status'] === 'running' ? '⏸' : '▶️' ?></button>
                        </form>
                        <?php endif; ?>
                        <a href="/admin/ab-testing/<?= $t['id'] ?>/results" style="background:#334155;color:#e2e8f0">📊</a>
                        <a href="/admin/ab-testing/<?= $t['id'] ?>/edit" style="background:#334155;color:#e2e8f0">✏️</a>
                        <form method="post" action="/admin/ab-testing/<?= $t['id'] ?>/delete" onsubmit="return confirm('Delete?')"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <button style="background:#ef444433;color:#ef4444">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>

<div style="margin-top:20px;padding:16px;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px">
    <h3 style="font-size:.85rem;color:var(--muted);margin-bottom:8px">💡 How it works</h3>
    <p style="font-size:.8rem;color:var(--muted);line-height:1.6">
        A/B tests run automatically on your site. Each visitor sees either variant A or B (randomly assigned, persisted via localStorage).
        The system tracks views and conversions. When you have enough data (100+ views per variant), the dashboard will suggest a winner.
        Tip: connect <code>ab.conversion</code> event to n8n for real-time notifications.
    </p>
</div>

<?php $content = ob_get_clean(); require CMS_APP . '/views/admin/layouts/topbar.php';
