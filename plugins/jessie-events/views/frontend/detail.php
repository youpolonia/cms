<?php
/**
 * Jessie Events — Public Event Detail + Ticket Purchase
 * URL: /events/{slug}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-events/includes/class-event-manager.php';
require_once CMS_ROOT . '/plugins/jessie-events/includes/class-event-ticket.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$event = \EventManager::getBySlug($eventSlug ?? '');
if (!$event) { http_response_code(404); echo '<!DOCTYPE html><html><body style="background:#0f172a;color:#e2e8f0;font-family:sans-serif;text-align:center;padding:80px"><h1>Event not found</h1><a href="/events" style="color:#6366f1">← Back to events</a></body></html>'; exit; }

\EventManager::incrementViews((int)$event['id']);
$tickets = \EventTicket::getAvailable((int)$event['id']);
$settings = \EventManager::getAllSettings();
$sym = $settings['currency_symbol'] ?? '£';
$requirePhone = ($settings['require_phone'] ?? '0') === '1';

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($event['title']) ?> — <?= h($siteTitle ?: 'Events') ?></title>
    <meta name="description" content="<?= h($event['short_description'] ?: mb_substr(strip_tags($event['description'] ?? ''), 0, 160)) ?>">
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6;--green:#10b981}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}a:hover{color:var(--accent2)}

        .back{max-width:1000px;margin:0 auto;padding:20px 20px 0}
        .back a{font-size:.85rem;color:var(--muted)}
        .back a:hover{color:var(--accent)}

        .event-hero{max-width:1000px;margin:0 auto;padding:24px 20px}
        .event-hero img{width:100%;max-height:400px;object-fit:cover;border-radius:12px;margin-bottom:20px}
        .event-hero .placeholder-img{width:100%;height:250px;background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(139,92,246,.1));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:4rem;margin-bottom:20px}

        .event-layout{max-width:1000px;margin:0 auto;padding:0 20px 40px;display:grid;grid-template-columns:1fr 360px;gap:24px}
        @media(max-width:768px){.event-layout{grid-template-columns:1fr}}

        .badge-row{display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap}
        .badge{padding:3px 10px;border-radius:5px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-free{background:rgba(16,185,129,.15);color:#34d399}
        .badge-cat{background:rgba(99,102,241,.1);color:#a5b4fc}
        .badge-status{background:rgba(59,130,246,.1);color:#60a5fa}

        h1{font-size:1.8rem;font-weight:800;margin-bottom:12px;line-height:1.2}
        .meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
        @media(max-width:500px){.meta-grid{grid-template-columns:1fr}}
        .meta-item{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:14px;display:flex;align-items:center;gap:10px}
        .meta-item .icon{font-size:1.3rem}
        .meta-item .label{font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}
        .meta-item .value{font-size:.9rem;font-weight:600}

        .desc{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px;line-height:1.7;font-size:.95rem}
        .desc h3{font-size:.85rem;text-transform:uppercase;color:var(--muted);margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)}

        /* Ticket sidebar */
        .ticket-sidebar{position:sticky;top:24px;align-self:start}
        .ticket-box{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:16px}
        .ticket-box h3{font-size:.85rem;text-transform:uppercase;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .ticket-option{border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:10px;cursor:pointer;transition:.15s}
        .ticket-option:hover{border-color:var(--accent)}
        .ticket-option.selected{border-color:var(--accent);background:rgba(99,102,241,.05)}
        .ticket-option .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px}
        .ticket-option .name{font-weight:700;font-size:.95rem}
        .ticket-option .price{font-weight:700;color:var(--green);font-size:1rem}
        .ticket-option .info{font-size:.78rem;color:var(--muted)}
        .ticket-option .avail{font-size:.72rem;color:var(--muted);margin-top:4px}

        .qty-row{display:flex;align-items:center;gap:8px;margin:14px 0}
        .qty-row label{font-size:.85rem;font-weight:600}
        .qty-row select{background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 12px;border-radius:8px;font-size:.85rem}

        .purchase-form .fg{margin-bottom:12px}
        .purchase-form .fg label{display:block;font-size:.8rem;font-weight:600;color:var(--text);margin-bottom:5px}
        .purchase-form .fg input{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}

        .total-row{display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-top:1px solid var(--border);margin-top:14px}
        .total-row .label{font-size:.9rem;font-weight:600}
        .total-row .amount{font-size:1.3rem;font-weight:800;color:var(--green)}

        .btn-buy{width:100%;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:14px;border-radius:10px;font-weight:700;font-size:1rem;cursor:pointer;margin-top:10px}
        .btn-buy:disabled{opacity:.5;cursor:not-allowed}

        #purchase-result{margin-top:12px;padding:14px;border-radius:10px;display:none;font-size:.9rem}
    </style>
</head>
<body>
    <div class="back"><a href="/events">← Back to Events</a></div>

    <div class="event-hero">
        <?php if ($event['image']): ?><img src="<?= h($event['image']) ?>" alt="<?= h($event['title']) ?>"><?php else: ?><div class="placeholder-img">🎪</div><?php endif; ?>
    </div>

    <div class="event-layout">
        <div>
            <div class="badge-row">
                <span class="badge badge-status"><?= h(ucfirst($event['status'])) ?></span>
                <?php if ($event['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                <?php if ($event['is_free']): ?><span class="badge badge-free">🆓 Free</span><?php endif; ?>
                <?php if ($event['category']): ?><span class="badge badge-cat"><?= h($event['category']) ?></span><?php endif; ?>
            </div>
            <h1><?= h($event['title']) ?></h1>

            <div class="meta-grid">
                <div class="meta-item"><span class="icon">📅</span><div><div class="label">Date & Time</div><div class="value"><?= date('M j, Y', strtotime($event['start_date'])) ?> at <?= date('H:i', strtotime($event['start_date'])) ?><?php if ($event['end_date']): ?><br><span style="font-size:.78rem;color:var(--muted)">Until <?= date('M j, Y H:i', strtotime($event['end_date'])) ?></span><?php endif; ?></div></div></div>
                <?php if ($event['venue_name'] || $event['venue_address']): ?>
                <div class="meta-item"><span class="icon">📍</span><div><div class="label">Venue</div><div class="value"><?= h($event['venue_name']) ?><?php if ($event['venue_address']): ?><br><span style="font-size:.78rem;color:var(--muted)"><?= h($event['venue_address']) ?></span><?php endif; ?></div></div></div>
                <?php endif; ?>
                <?php if ($event['city'] || $event['country']): ?>
                <div class="meta-item"><span class="icon">🌍</span><div><div class="label">Location</div><div class="value"><?= h(trim(($event['city'] ?: '') . ($event['country'] ? ', ' . $event['country'] : ''), ', ')) ?></div></div></div>
                <?php endif; ?>
                <?php if ($event['organizer_name']): ?>
                <div class="meta-item"><span class="icon">👤</span><div><div class="label">Organizer</div><div class="value"><?= h($event['organizer_name']) ?><?php if ($event['organizer_email']): ?><br><span style="font-size:.78rem;color:var(--muted)"><?= h($event['organizer_email']) ?></span><?php endif; ?></div></div></div>
                <?php endif; ?>
            </div>

            <?php if ($event['description']): ?>
            <div class="desc">
                <h3>About This Event</h3>
                <?= nl2br(h($event['description'])) ?>
            </div>
            <?php endif; ?>

            <?php if ($event['max_capacity']): ?>
            <div style="font-size:.85rem;color:var(--muted);margin-bottom:20px">📊 Capacity: <?= number_format((int)$event['max_capacity']) ?> • 👁️ <?= number_format((int)$event['view_count']) ?> views</div>
            <?php endif; ?>
        </div>

        <!-- Ticket Purchase Sidebar -->
        <div class="ticket-sidebar">
            <?php if ($event['status'] === 'cancelled'): ?>
            <div class="ticket-box" style="text-align:center;color:#fca5a5"><p style="font-size:1.1rem;font-weight:700">❌ Event Cancelled</p></div>
            <?php elseif ($event['status'] === 'completed'): ?>
            <div class="ticket-box" style="text-align:center;color:var(--muted)"><p style="font-size:1.1rem;font-weight:700">✅ Event Completed</p></div>
            <?php elseif (empty($tickets)): ?>
            <div class="ticket-box" style="text-align:center;color:var(--muted)"><p>No tickets available</p></div>
            <?php else: ?>
            <div class="ticket-box">
                <h3>🎫 Get Tickets</h3>
                <div id="ticket-list">
                    <?php foreach ($tickets as $idx => $t):
                        $avail = (int)$t['quantity_total'] - (int)$t['quantity_sold'];
                    ?>
                    <div class="ticket-option <?= $idx === 0 ? 'selected' : '' ?>" data-id="<?= $t['id'] ?>" data-price="<?= $t['price'] ?>" data-max="<?= min($avail, (int)$t['max_per_order']) ?>" onclick="selectTicket(this)">
                        <div class="top">
                            <span class="name"><?= h($t['name']) ?></span>
                            <span class="price"><?= (float)$t['price'] > 0 ? $sym . number_format((float)$t['price'], 2) : 'FREE' ?></span>
                        </div>
                        <?php if ($t['description']): ?><div class="info"><?= h($t['description']) ?></div><?php endif; ?>
                        <div class="avail"><?= $avail ?> remaining</div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="qty-row">
                    <label>Quantity:</label>
                    <select id="qty-select" onchange="updateTotal()">
                        <?php
                        $firstT = $tickets[0];
                        $maxQ = min((int)$firstT['quantity_total'] - (int)$firstT['quantity_sold'], (int)$firstT['max_per_order']);
                        for ($i = 1; $i <= $maxQ; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="total-row">
                    <span class="label">Total:</span>
                    <span class="amount" id="total-amount"><?= (float)$firstT['price'] > 0 ? $sym . number_format((float)$firstT['price'], 2) : 'FREE' ?></span>
                </div>

                <form class="purchase-form" onsubmit="return doPurchase(event)">
                    <div class="fg"><label>Full Name *</label><input type="text" id="buyer-name" required></div>
                    <div class="fg"><label>Email *</label><input type="email" id="buyer-email" required></div>
                    <div class="fg"><label>Phone<?= $requirePhone ? ' *' : '' ?></label><input type="tel" id="buyer-phone" <?= $requirePhone ? 'required' : '' ?>></div>
                    <button type="submit" class="btn-buy" id="buy-btn">🎫 Get Tickets</button>
                </form>
                <div id="purchase-result"></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    var selectedTicketId=<?= !empty($tickets) ? (int)$tickets[0]['id'] : 0 ?>;
    var selectedPrice=<?= !empty($tickets) ? (float)$tickets[0]['price'] : 0 ?>;
    var sym='<?= $sym ?>';
    var eventId=<?= (int)$event['id'] ?>;

    function selectTicket(el){
        document.querySelectorAll('.ticket-option').forEach(function(t){t.classList.remove('selected')});
        el.classList.add('selected');
        selectedTicketId=parseInt(el.dataset.id);
        selectedPrice=parseFloat(el.dataset.price);
        var maxQ=parseInt(el.dataset.max);
        var sel=document.getElementById('qty-select');
        sel.innerHTML='';
        for(var i=1;i<=maxQ;i++){var o=document.createElement('option');o.value=i;o.textContent=i;sel.appendChild(o);}
        updateTotal();
    }
    function updateTotal(){
        var qty=parseInt(document.getElementById('qty-select').value);
        var total=selectedPrice*qty;
        document.getElementById('total-amount').textContent=total>0?sym+total.toFixed(2):'FREE';
    }
    function doPurchase(e){
        e.preventDefault();
        var btn=document.getElementById('buy-btn');
        btn.disabled=true;btn.textContent='⏳ Processing...';
        var res=document.getElementById('purchase-result');
        var data={
            event_id:eventId,
            ticket_id:selectedTicketId,
            quantity:parseInt(document.getElementById('qty-select').value),
            buyer_name:document.getElementById('buyer-name').value,
            buyer_email:document.getElementById('buyer-email').value,
            buyer_phone:document.getElementById('buyer-phone').value,
        };
        fetch('/api/events/purchase',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data),credentials:'same-origin'})
        .then(function(r){return r.json()}).then(function(d){
            res.style.display='block';
            if(d.ok){
                res.style.background='rgba(16,185,129,.15)';res.style.color='#34d399';
                res.innerHTML='<strong>✅ Order Confirmed!</strong><br>Order: '+d.order_number+'<br>QR Code: <code>'+d.qr_code+'</code><br>Total: '+sym+(parseFloat(d.total).toFixed(2));
                btn.textContent='✅ Done';
            }else{
                res.style.background='rgba(239,68,68,.15)';res.style.color='#fca5a5';
                res.textContent='❌ '+d.error;
                btn.disabled=false;btn.textContent='🎫 Get Tickets';
            }
        }).catch(function(){
            res.style.display='block';res.style.background='rgba(239,68,68,.15)';res.style.color='#fca5a5';res.textContent='❌ Network error';
            btn.disabled=false;btn.textContent='🎫 Get Tickets';
        });
        return false;
    }
    </script>
</body>
</html>
