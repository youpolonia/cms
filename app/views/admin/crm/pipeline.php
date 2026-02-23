<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Deal Pipeline';
ob_start();
$stageLabels = ['lead'=>'🎯 Lead','qualified'=>'✅ Qualified','proposal'=>'📄 Proposal','negotiation'=>'🤝 Negotiation','won'=>'🏆 Won','lost'=>'❌ Lost'];
$stageColors = ['lead'=>'#94a3b8','qualified'=>'#a855f7','proposal'=>'#f59e0b','negotiation'=>'#3b82f6','won'=>'#10b981','lost'=>'#ef4444'];
$fmtMoney = fn($v) => '$' . number_format((float)$v, 0, '.', ',');
?>
<style>
.pp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
.pp-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-align:center}
.pp-stat .num{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0)}
.pp-stat .lbl{font-size:.75rem;color:var(--muted,#94a3b8)}
.pp-board{display:flex;gap:12px;overflow-x:auto;padding-bottom:12px;min-height:400px}
.pp-col{flex:0 0 220px;background:var(--bg,#0f172a);border-radius:10px;padding:12px;display:flex;flex-direction:column}
.pp-col-head{padding:8px;margin-bottom:12px;border-radius:8px;text-align:center}
.pp-col-head h4{font-size:.85rem;margin:0;font-weight:700}
.pp-col-head .sub{font-size:.75rem;margin-top:2px}
.pp-col-body{flex:1;display:flex;flex-direction:column;gap:8px}
.pp-deal{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:8px;padding:12px;cursor:pointer;transition:border-color .2s}
.pp-deal:hover{border-color:var(--primary,#6366f1)}
.pp-deal-name{font-size:.85rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:4px}
.pp-deal-company{font-size:.75rem;color:var(--muted,#94a3b8)}
.pp-deal-val{font-size:1rem;font-weight:700;color:var(--text,#e2e8f0);margin-top:8px}
.pp-deal-prob{font-size:.7rem;color:var(--muted)}
.pp-empty{text-align:center;padding:20px;font-size:.8rem;color:var(--muted);border:2px dashed var(--border,#334155);border-radius:8px}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
    <h1 style="font-size:1.5rem;font-weight:700">🏗️ Deal Pipeline</h1>
    <a href="/admin/crm" style="color:var(--primary,#6366f1);text-decoration:none;font-size:.85rem">← Back to CRM</a>
</div>

<div class="pp-stats">
    <div class="pp-stat"><div class="num"><?= $stats['total_deals'] ?></div><div class="lbl">Active Deals</div></div>
    <div class="pp-stat"><div class="num"><?= $fmtMoney($stats['deals_value']) ?></div><div class="lbl">Pipeline Value</div></div>
    <div class="pp-stat"><div class="num"><?= $fmtMoney($stats['won_value']) ?></div><div class="lbl">Won Revenue</div></div>
</div>

<div class="pp-board">
    <?php foreach ($pipeline as $stage => $data): $color = $stageColors[$stage]; ?>
    <div class="pp-col">
        <div class="pp-col-head" style="background:<?= $color ?>15">
            <h4 style="color:<?= $color ?>"><?= $stageLabels[$stage] ?></h4>
            <div class="sub" style="color:<?= $color ?>"><?= $data['count'] ?> deals · <?= $fmtMoney($data['value']) ?></div>
        </div>
        <div class="pp-col-body">
            <?php if (empty($data['deals'])): ?>
                <div class="pp-empty">No deals</div>
            <?php else: foreach ($data['deals'] as $d): ?>
                <a href="/admin/crm/contacts/<?= $d['contact_id'] ?>" class="pp-deal" style="text-decoration:none">
                    <div class="pp-deal-name"><?= h($d['title']) ?></div>
                    <div class="pp-deal-company"><?= h($d['first_name'] . ' ' . ($d['last_name'] ?? '')) ?><?= $d['company'] ? ' · ' . h($d['company']) : '' ?></div>
                    <div class="pp-deal-val"><?= $fmtMoney($d['value']) ?></div>
                    <div class="pp-deal-prob"><?= (int)$d['probability'] ?>% probability<?= $d['expected_close'] ? ' · Close ' . date('M j', strtotime($d['expected_close'])) : '' ?></div>
                </a>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
