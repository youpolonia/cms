<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$t = $test;
$pageTitle = 'Test Results: ' . h($t['name']);
ob_start();
$maxRate = max($t['rate_a'], $t['rate_b'], 0.1);
?>
<style>
.abr-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.abr-card{background:var(--bg-card,#1e293b);border:2px solid var(--border,#334155);border-radius:10px;padding:24px;text-align:center}
.abr-card.a{border-color:#6366f1}.abr-card.b{border-color:#f59e0b}
.abr-card.winner{box-shadow:0 0 20px rgba(16,185,129,.2);border-color:#10b981}
.abr-label{font-size:.85rem;font-weight:600;margin-bottom:16px}
.abr-rate{font-size:3rem;font-weight:700;color:var(--text,#e2e8f0)}
.abr-rate small{font-size:1rem;color:var(--muted,#94a3b8)}
.abr-detail{font-size:.8rem;color:var(--muted,#94a3b8);margin-top:8px}
.abr-bar{height:20px;border-radius:10px;background:#0f172a;overflow:hidden;margin-top:12px}
.abr-bar-fill{height:100%;border-radius:10px;transition:width .5s}
.abr-info{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:20px;margin-bottom:20px}
.abr-info dl{display:grid;grid-template-columns:120px 1fr;gap:8px;font-size:.85rem}
.abr-info dt{color:var(--muted,#94a3b8)}.abr-info dd{color:var(--text,#e2e8f0);margin:0}
.abr-actions{display:flex;gap:8px;margin-top:20px;justify-content:center}
.abr-actions form{display:inline}
.abr-btn{padding:10px 20px;border-radius:8px;font-size:.85rem;border:none;cursor:pointer;text-decoration:none}
</style>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
    <a href="/admin/ab-testing" style="color:var(--muted);text-decoration:none;font-size:1.2rem">←</a>
    <h1 style="font-size:1.5rem;font-weight:700">📊 <?= h($t['name']) ?></h1>
    <span style="padding:4px 12px;border-radius:12px;font-size:.8rem;font-weight:600;background:<?= $t['status']==='running'?'#22c55e22':'#94a3b822' ?>;color:<?= $t['status']==='running'?'#22c55e':'#94a3b8' ?>"><?= ucfirst($t['status']) ?></span>
</div>

<?php if ($t['lift'] != 0): ?>
<div style="text-align:center;margin-bottom:20px;padding:16px;background:<?= $t['lift'] > 0 ? '#10b98115' : '#ef444415' ?>;border-radius:10px;border:1px solid <?= $t['lift'] > 0 ? '#10b98133' : '#ef444433' ?>">
    <span style="font-size:1.5rem;font-weight:700;color:<?= $t['lift'] > 0 ? '#10b981' : '#ef4444' ?>"><?= $t['lift'] > 0 ? '+' : '' ?><?= $t['lift'] ?>% lift</span>
    <span style="font-size:.85rem;color:var(--muted)"> — Variant B vs A<?= !$t['significant'] ? ' (not yet significant)' : '' ?></span>
</div>
<?php endif; ?>

<div class="abr-grid">
    <div class="abr-card a <?= $t['winner'] === 'a' ? 'winner' : '' ?>">
        <div class="abr-label" style="color:#6366f1">🅰️ Variant A (Control) <?= $t['winner'] === 'a' ? '🏆' : '' ?></div>
        <div class="abr-rate"><?= $t['rate_a'] ?>%<small> conversion</small></div>
        <div class="abr-detail"><?= number_format($t['conversions_a']) ?> conversions / <?= number_format($t['views_a']) ?> views</div>
        <div class="abr-bar"><div class="abr-bar-fill" style="width:<?= round($t['rate_a']/$maxRate*100) ?>%;background:#6366f1"></div></div>
        <div style="margin-top:12px;padding:8px;background:#0f172a;border-radius:6px;font-size:.8rem;color:var(--muted)"><?= h(mb_substr($t['variant_a'], 0, 100)) ?></div>
    </div>
    <div class="abr-card b <?= $t['winner'] === 'b' ? 'winner' : '' ?>">
        <div class="abr-label" style="color:#f59e0b">🅱️ Variant B (Challenger) <?= $t['winner'] === 'b' ? '🏆' : '' ?></div>
        <div class="abr-rate"><?= $t['rate_b'] ?>%<small> conversion</small></div>
        <div class="abr-detail"><?= number_format($t['conversions_b']) ?> conversions / <?= number_format($t['views_b']) ?> views</div>
        <div class="abr-bar"><div class="abr-bar-fill" style="width:<?= round($t['rate_b']/$maxRate*100) ?>%;background:#f59e0b"></div></div>
        <div style="margin-top:12px;padding:8px;background:#0f172a;border-radius:6px;font-size:.8rem;color:var(--muted)"><?= h(mb_substr($t['variant_b'], 0, 100)) ?></div>
    </div>
</div>

<div class="abr-info">
    <dl>
        <dt>Type</dt><dd><?= ucfirst($t['type']) ?></dd>
        <dt>Selector</dt><dd><code><?= h($t['selector']) ?></code></dd>
        <dt>Goal</dt><dd><?= ucfirst(str_replace('_', ' ', $t['goal'])) ?> <?= $t['goal_selector'] ? '→ <code>' . h($t['goal_selector']) . '</code>' : '' ?></dd>
        <?php if ($t['page_title']): ?><dt>Page</dt><dd><?= h($t['page_title']) ?></dd><?php endif; ?>
        <dt>Started</dt><dd><?= $t['started_at'] ? date('M j, Y H:i', strtotime($t['started_at'])) : '—' ?></dd>
        <?php if ($t['ended_at']): ?><dt>Ended</dt><dd><?= date('M j, Y H:i', strtotime($t['ended_at'])) ?></dd><?php endif; ?>
        <dt>Total Traffic</dt><dd><?= number_format($t['total_views']) ?> views, <?= number_format($t['total_conversions']) ?> conversions</dd>
        <dt>Confidence</dt><dd><?= $t['significant'] ? '✅ Statistically significant (100+ views/variant)' : '⏳ Need more data (' . min($t['views_a'],$t['views_b']) . '/100 minimum)' ?></dd>
    </dl>
</div>

<?php if ($t['status'] !== 'completed'): ?>
<div class="abr-actions">
    <form method="post" action="/admin/ab-testing/<?= $t['id'] ?>/complete">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="winner" value="a">
        <button class="abr-btn" style="background:#6366f1;color:#fff">🏆 Declare A Winner</button>
    </form>
    <form method="post" action="/admin/ab-testing/<?= $t['id'] ?>/complete">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="winner" value="b">
        <button class="abr-btn" style="background:#f59e0b;color:#000">🏆 Declare B Winner</button>
    </form>
    <form method="post" action="/admin/ab-testing/<?= $t['id'] ?>/toggle">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <button class="abr-btn" style="background:#334155;color:#e2e8f0"><?= $t['status'] === 'running' ? '⏸ Pause' : '▶️ Resume' ?></button>
    </form>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); require CMS_APP . '/views/admin/layouts/topbar.php';
