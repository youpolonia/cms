<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-event-manager.php';
require_once $pluginDir . '/includes/class-event-order.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \EventOrder::getAll($_GET, $page);
$sym = \EventManager::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.ew{max-width:1100px;margin:0 auto;padding:24px 20px}
.eh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.eh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:600;border:none;cursor:pointer}
.tb{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.tb th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.tb td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.tb tr:last-child td{border-bottom:none}
.sb{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.st-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.st-paid{background:rgba(16,185,129,.15);color:#34d399}
.st-refunded{background:rgba(239,68,68,.15);color:#fca5a5}
.ci-yes{color:#34d399;font-weight:700}.ci-no{color:var(--muted,#94a3b8)}
.fb{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.fb select,.fb input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.checkin-box{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.checkin-box h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 12px}
.checkin-row{display:flex;gap:10px;align-items:center}
.checkin-row input{flex:1}
#checkin-result{margin-top:10px;padding:10px;border-radius:8px;font-size:.85rem;display:none}
.pag{display:flex;gap:6px;margin-top:16px;justify-content:center}
.pag a,.pag span{padding:6px 12px;border-radius:6px;font-size:.82rem;text-decoration:none}
.pag a{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0)}
.pag a:hover{border-color:#6366f1}.pag span{background:#6366f1;color:#fff}
</style>
<div class="ew">
    <div class="eh"><h1>📋 Orders & Check-in</h1><a href="/admin/events" class="btn-s">← Dashboard</a></div>

    <!-- Check-in box -->
    <div class="checkin-box">
        <h3>✅ QR Check-in</h3>
        <div class="checkin-row">
            <input type="text" id="qr-input" placeholder="Scan or paste QR code..." style="background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 14px;border-radius:8px;font-size:.9rem">
            <button class="btn-p" onclick="doCheckin()">✅ Check In</button>
        </div>
        <div id="checkin-result"></div>
    </div>

    <div class="fb">
        <select onchange="location.href='?payment_status='+this.value"><option value="">All Payment</option><?php foreach (['pending','paid','refunded'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['payment_status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search name/order#..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="padding:8px;color:var(--muted);font-size:.82rem"><?= $result['total'] ?> orders</span>
    </div>

    <table class="tb"><thead><tr><th>Order</th><th>Buyer</th><th>Event</th><th>Ticket</th><th>Qty</th><th>Total</th><th>Payment</th><th>Check-in</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($result['orders'] as $o): ?>
        <tr>
            <td style="font-weight:700;color:#a5b4fc;font-size:.82rem"><?= h($o['order_number']) ?></td>
            <td><?= h($o['buyer_name']) ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h($o['buyer_email']) ?></span></td>
            <td style="font-size:.82rem"><?= h($o['event_title'] ?? '—') ?></td>
            <td style="font-size:.82rem"><?= h($o['ticket_name'] ?? '—') ?></td>
            <td style="text-align:center"><?= $o['quantity'] ?></td>
            <td style="font-weight:600"><?= $sym ?><?= number_format((float)$o['total'], 2) ?></td>
            <td><span class="sb st-<?= h($o['payment_status']) ?>"><?= h($o['payment_status']) ?></span></td>
            <td><?php if ($o['checked_in']): ?><span class="ci-yes">✅ <?= date('H:i', strtotime($o['checked_in_at'])) ?></span><?php else: ?><span class="ci-no">—</span><?php endif; ?></td>
            <td style="font-size:.78rem;color:var(--muted);white-space:nowrap"><?= date('M j H:i', strtotime($o['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['orders'])): ?><tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted)">No orders yet.</td></tr><?php endif; ?>
    </tbody></table>
    <?php if ($result['pages'] > 1): ?>
    <div class="pag">
        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
            <?php if ($p == $page): ?><span><?= $p ?></span><?php else: ?><a href="?page=<?= $p ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<script>
function doCheckin(){
    var qr=document.getElementById('qr-input').value.trim();
    if(!qr){alert('Enter QR code');return;}
    var res=document.getElementById('checkin-result');
    res.style.display='block';res.textContent='Checking...';res.style.background='rgba(99,102,241,.1)';res.style.color='#a5b4fc';
    fetch('/api/events/check-in',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({qr_code:qr}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok){
            res.style.background='rgba(16,185,129,.15)';res.style.color='#34d399';
            res.textContent='✅ Checked in: '+d.order.buyer_name+' — '+d.order.event_title+' ('+d.order.ticket_name+', qty: '+d.order.quantity+')';
        }else{
            res.style.background='rgba(239,68,68,.15)';res.style.color='#fca5a5';
            res.textContent='❌ '+d.error;
        }
        document.getElementById('qr-input').value='';document.getElementById('qr-input').focus();
    }).catch(function(){res.style.background='rgba(239,68,68,.15)';res.style.color='#fca5a5';res.textContent='❌ Network error';});
}
document.getElementById('qr-input').addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();doCheckin();}});
</script>
<?php $content = ob_get_clean(); $title = 'Event Orders'; require CMS_APP . '/views/admin/layouts/topbar.php';
