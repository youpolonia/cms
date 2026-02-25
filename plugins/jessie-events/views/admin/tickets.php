<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-event-manager.php';
require_once $pluginDir . '/includes/class-event-ticket.php';
$tickets = \EventTicket::getByEvent($eventId);
$sym = \EventManager::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.ew{max-width:900px;margin:0 auto;padding:24px 20px}
.eh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.eh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-d{background:rgba(239,68,68,.1);color:#fca5a5;padding:6px 12px;border-radius:6px;font-size:.75rem;font-weight:600;border:1px solid rgba(239,68,68,.3);cursor:pointer}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.fg{margin-bottom:14px}.fg label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:5px}
.fg input,.fg select,.fg textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.fr3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
@media(max-width:600px){.fr,.fr3{grid-template-columns:1fr}}
.ticket-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:14px}
.ticket-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.ticket-head h4{font-size:1rem;font-weight:700;margin:0;color:var(--text,#e2e8f0)}
.sb{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.sb-active{background:rgba(16,185,129,.15);color:#34d399}.sb-soldout{background:rgba(239,68,68,.15);color:#fca5a5}.sb-hidden{background:rgba(100,116,139,.15);color:#94a3b8}
.tstat{display:flex;gap:16px;font-size:.82rem;color:var(--muted,#94a3b8);margin-bottom:10px}
.tstat strong{color:var(--text,#e2e8f0)}
.prog{height:6px;background:rgba(51,65,85,.5);border-radius:3px;overflow:hidden;margin-top:6px}
.prog-fill{height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;transition:.3s}
</style>
<div class="ew">
    <div class="eh">
        <div><h1>🎫 Tickets: <?= h($event['title']) ?></h1><span style="font-size:.82rem;color:var(--muted)"><?= date('M j, Y H:i', strtotime($event['start_date'])) ?></span></div>
        <div style="display:flex;gap:10px"><a href="/admin/events/<?= $eventId ?>/edit" class="btn-s">✏️ Edit Event</a><a href="/admin/events/list" class="btn-s">← Events</a></div>
    </div>

    <!-- Existing tickets -->
    <?php foreach ($tickets as $t):
        $pct = $t['quantity_total'] > 0 ? round(($t['quantity_sold'] / $t['quantity_total']) * 100) : 0;
    ?>
    <div class="ticket-card">
        <div class="ticket-head">
            <h4><?= h($t['name']) ?> <span class="sb sb-<?= h($t['status']) ?>"><?= h($t['status']) ?></span></h4>
            <span style="font-size:1.1rem;font-weight:700;color:#10b981"><?= (float)$t['price'] > 0 ? $sym . number_format((float)$t['price'], 2) : 'FREE' ?></span>
        </div>
        <?php if ($t['description']): ?><p style="font-size:.82rem;color:var(--muted);margin-bottom:8px"><?= h($t['description']) ?></p><?php endif; ?>
        <div class="tstat">
            <span>Sold: <strong><?= $t['quantity_sold'] ?>/<?= $t['quantity_total'] ?></strong></span>
            <span>Max/order: <strong><?= $t['max_per_order'] ?></strong></span>
            <?php if ($t['sale_start']): ?><span>Sale: <strong><?= date('M j', strtotime($t['sale_start'])) ?></strong></span><?php endif; ?>
        </div>
        <div class="prog"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>

        <!-- Inline edit form -->
        <details style="margin-top:12px">
            <summary style="cursor:pointer;font-size:.78rem;color:#a5b4fc;font-weight:600">✏️ Edit Ticket</summary>
            <form method="post" action="/admin/events/tickets/<?= $t['id'] ?>/update" style="margin-top:10px">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="fr3">
                    <div class="fg"><label>Name</label><input type="text" name="name" value="<?= h($t['name']) ?>" required></div>
                    <div class="fg"><label>Price</label><input type="number" name="price" step="0.01" min="0" value="<?= $t['price'] ?>"></div>
                    <div class="fg"><label>Status</label><select name="status"><?php foreach (['active'=>'Active','soldout'=>'Sold Out','hidden'=>'Hidden'] as $sk=>$sl): ?><option value="<?= $sk ?>" <?= $t['status']===$sk?'selected':'' ?>><?= $sl ?></option><?php endforeach; ?></select></div>
                </div>
                <div class="fr3">
                    <div class="fg"><label>Total Qty</label><input type="number" name="quantity_total" min="1" value="<?= $t['quantity_total'] ?>"></div>
                    <div class="fg"><label>Max/Order</label><input type="number" name="max_per_order" min="1" value="<?= $t['max_per_order'] ?>"></div>
                    <div class="fg"><label>Currency</label><input type="text" name="currency" value="<?= h($t['currency']) ?>" maxlength="10"></div>
                </div>
                <div class="fg"><label>Description</label><input type="text" name="description" value="<?= h($t['description']) ?>"></div>
                <div class="fr">
                    <div class="fg"><label>Sale Start</label><input type="datetime-local" name="sale_start" value="<?= $t['sale_start'] ? date('Y-m-d\TH:i', strtotime($t['sale_start'])) : '' ?>"></div>
                    <div class="fg"><label>Sale End</label><input type="datetime-local" name="sale_end" value="<?= $t['sale_end'] ? date('Y-m-d\TH:i', strtotime($t['sale_end'])) : '' ?>"></div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end">
                    <form method="post" action="/admin/events/tickets/<?= $t['id'] ?>/delete" style="display:inline"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" onclick="return confirm('Delete ticket?')" class="btn-d">🗑 Delete</button></form>
                    <button type="submit" class="btn-p" style="padding:8px 16px;font-size:.82rem">💾 Update</button>
                </div>
            </form>
        </details>
    </div>
    <?php endforeach; ?>
    <?php if (empty($tickets)): ?>
    <div class="card" style="text-align:center;padding:40px;color:var(--muted)">No tickets yet. Add the first ticket below.</div>
    <?php endif; ?>

    <!-- Add new ticket -->
    <div class="card">
        <h3>➕ Add New Ticket</h3>
        <form method="post" action="/admin/events/<?= $eventId ?>/tickets/store">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="fr3">
                <div class="fg"><label>Ticket Name *</label><input type="text" name="name" required placeholder="General Admission"></div>
                <div class="fg"><label>Price</label><input type="number" name="price" step="0.01" min="0" value="0.00"></div>
                <div class="fg"><label>Currency</label><input type="text" name="currency" value="<?= h(\EventManager::getSetting('currency', 'GBP')) ?>" maxlength="10"></div>
            </div>
            <div class="fr3">
                <div class="fg"><label>Total Quantity</label><input type="number" name="quantity_total" min="1" value="100"></div>
                <div class="fg"><label>Max per Order</label><input type="number" name="max_per_order" min="1" value="10"></div>
                <div class="fg"><label>Status</label><select name="status"><option value="active">Active</option><option value="hidden">Hidden</option></select></div>
            </div>
            <div class="fg"><label>Description</label><input type="text" name="description" placeholder="Optional ticket description"></div>
            <div class="fr">
                <div class="fg"><label>Sale Start</label><input type="datetime-local" name="sale_start"></div>
                <div class="fg"><label>Sale End</label><input type="datetime-local" name="sale_end"></div>
            </div>
            <div style="display:flex;justify-content:flex-end"><button type="submit" class="btn-p">➕ Add Ticket</button></div>
        </form>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Tickets: ' . h($event['title']); require CMS_APP . '/views/admin/layouts/topbar.php';
