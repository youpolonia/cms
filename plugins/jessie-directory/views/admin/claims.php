<?php
/**
 * Jessie Directory — Admin Claims Management
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-directory-listing.php';

$pdo = db();
$claims = $pdo->query("SELECT cl.*, l.title AS listing_title, l.slug AS listing_slug FROM directory_claims cl JOIN directory_listings l ON cl.listing_id = l.id ORDER BY cl.status = 'pending' DESC, cl.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
$pending = array_filter($claims, fn($c) => $c['status'] === 'pending');

ob_start();
?>
<style>
.dir-wrap{max-width:900px;margin:0 auto;padding:24px 20px}
.dir-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.dir-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.claim-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;margin-bottom:12px}
.claim-card.pending{border-left:3px solid #f59e0b}
.claim-card.approved{border-left:3px solid #10b981}
.claim-card.rejected{border-left:3px solid #ef4444}
.claim-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.claim-header h4{margin:0;font-size:.9rem;color:var(--text,#e2e8f0)}
.claim-meta{font-size:.75rem;color:var(--muted,#94a3b8);margin-bottom:8px}
.claim-proof{font-size:.85rem;color:var(--text,#e2e8f0);line-height:1.6;padding:12px;background:rgba(99,102,241,.05);border-radius:8px;margin-bottom:10px}
.claim-actions{display:flex;gap:8px}
.claim-actions button{padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:none;cursor:pointer}
.btn-approve{background:rgba(16,185,129,.15);color:#34d399}
.btn-reject{background:rgba(239,68,68,.1);color:#fca5a5}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.status-approved{background:rgba(16,185,129,.15);color:#34d399}
.status-rejected{background:rgba(239,68,68,.15);color:#fca5a5}
.stat-row{display:flex;gap:16px;margin-bottom:20px}
.stat-row .stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:14px 20px;text-align:center;flex:1}
.stat-row .val{font-size:1.5rem;font-weight:800;line-height:1}
.stat-row .lbl{font-size:.7rem;color:var(--muted,#94a3b8);text-transform:uppercase;letter-spacing:.05em;margin-top:2px}
</style>
<div class="dir-wrap">
    <div class="dir-header"><h1>🏢 Claims Management</h1><a href="/admin/directory" class="btn-secondary">← Dashboard</a></div>

    <div class="stat-row">
        <div class="stat"><div class="val" style="color:#f59e0b"><?= count($pending) ?></div><div class="lbl">Pending</div></div>
        <div class="stat"><div class="val" style="color:#10b981"><?= count(array_filter($claims, fn($c) => $c['status'] === 'approved')) ?></div><div class="lbl">Approved</div></div>
        <div class="stat"><div class="val" style="color:#ef4444"><?= count(array_filter($claims, fn($c) => $c['status'] === 'rejected')) ?></div><div class="lbl">Rejected</div></div>
        <div class="stat"><div class="val" style="color:#a5b4fc"><?= count($claims) ?></div><div class="lbl">Total</div></div>
    </div>

    <?php if (empty($claims)): ?>
        <div style="text-align:center;padding:60px;color:var(--muted,#94a3b8)">
            <p style="font-size:1.2rem">📋 No claims yet.</p>
            <p style="font-size:.85rem;margin-top:8px">Claims will appear here when business owners request to claim their listings.</p>
        </div>
    <?php else: ?>
        <?php foreach ($claims as $cl): ?>
        <div class="claim-card <?= h($cl['status']) ?>">
            <div class="claim-header">
                <h4>
                    <a href="/admin/directory/listings/<?= (int)$cl['listing_id'] ?>/edit" style="color:var(--text,#e2e8f0);text-decoration:none"><?= h($cl['listing_title']) ?></a>
                </h4>
                <span class="status-badge status-<?= h($cl['status']) ?>"><?= h($cl['status']) ?></span>
            </div>
            <div class="claim-meta">
                👤 <?= h($cl['name']) ?> · ✉️ <?= h($cl['email']) ?> · 📅 <?= date('M j, Y H:i', strtotime($cl['created_at'])) ?>
            </div>
            <div class="claim-proof"><?= nl2br(h($cl['proof'])) ?></div>
            <?php if ($cl['status'] === 'pending'): ?>
            <div class="claim-actions">
                <button class="btn-approve" onclick="handleClaim(<?= (int)$cl['id'] ?>, <?= (int)$cl['listing_id'] ?>, 'approve')">✓ Approve</button>
                <button class="btn-reject" onclick="handleClaim(<?= (int)$cl['id'] ?>, <?= (int)$cl['listing_id'] ?>, 'reject')">✕ Reject</button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
function handleClaim(claimId, listingId, action) {
    fetch('/api/directory/' + action + '-claim/' + claimId, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({listing_id: listingId}),
        credentials: 'same-origin'
    })
    .then(function(r) { return r.json(); })
    .then(function(d) { if (d.ok) location.reload(); else alert(d.error || 'Error'); })
    .catch(function() { alert('Network error'); });
}
</script>
<?php $content = ob_get_clean(); $title = 'Claims Management'; require CMS_APP . '/views/admin/layouts/topbar.php';
