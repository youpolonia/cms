<?php
/**
 * Jessie Real Estate — Public Property Detail Page
 * URL: /properties/{slug}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-realestate/includes/class-realestate-property.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$slug = $propertySlug ?? '';
$property = \RealEstateProperty::getBySlug($slug);

if (!$property || !in_array($property['status'], ['active','sold','rented'])) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body style="background:#0f172a;color:#e2e8f0;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh"><div style="text-align:center"><h1>404</h1><p>Property not found.</p><a href="/properties" style="color:#6366f1">← Back to Properties</a></div></body></html>';
    exit;
}

\RealEstateProperty::incrementViews((int)$property['id']);
$symbol = \RealEstateProperty::getSetting('currency_symbol', '£');

// Related properties (same city or type)
$related = [];
$r = \RealEstateProperty::getAll(['status' => 'active', 'city' => $property['city'], 'property_type' => $property['property_type']], 1, 4);
$related = array_filter($r['properties'], fn($p) => (int)$p['id'] !== (int)$property['id']);
$related = array_slice($related, 0, 3);

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($property['title']) ?> — <?= h($siteTitle ?: 'Properties') ?></title>
    <meta name="description" content="<?= h($property['short_description'] ?: substr(strip_tags($property['description'] ?? ''), 0, 160)) ?>">
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}

        .detail-header{background:linear-gradient(135deg,rgba(99,102,241,.12) 0%,rgba(139,92,246,.08) 100%);border-bottom:1px solid var(--border);padding:32px 20px}
        .detail-header-inner{max-width:1000px;margin:0 auto}
        .breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:12px}
        .breadcrumb a{color:var(--muted)}
        .breadcrumb a:hover{color:var(--accent)}
        .badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
        .badge{padding:3px 10px;border-radius:5px;font-size:.7rem;font-weight:700;text-transform:uppercase}
        .badge-sale{background:rgba(16,185,129,.15);color:#34d399}
        .badge-rent{background:rgba(99,102,241,.15);color:#a5b4fc}
        .badge-lease{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-type{background:rgba(99,102,241,.1);color:#a5b4fc}
        .badge-sold{background:rgba(239,68,68,.15);color:#fca5a5}
        .detail-header h1{font-size:1.8rem;font-weight:800;margin-bottom:6px}
        .price-big{font-size:1.5rem;font-weight:800;color:#10b981;margin-bottom:8px}
        .detail-header .meta{display:flex;gap:16px;flex-wrap:wrap;font-size:.85rem;color:var(--muted)}
        .detail-header .meta span{display:flex;align-items:center;gap:4px}

        .container{max-width:1000px;margin:0 auto;padding:24px 20px}
        .detail-grid{display:grid;grid-template-columns:1fr 340px;gap:24px}
        @media(max-width:768px){.detail-grid{grid-template-columns:1fr}}

        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:22px;margin-bottom:20px}
        .card h3{font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}

        .description{font-size:.92rem;line-height:1.75;color:var(--text)}
        .description p{margin-bottom:12px}

        /* Gallery */
        .gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;margin-bottom:12px}
        .gallery img{width:100%;height:160px;object-fit:cover;border-radius:8px;cursor:pointer;transition:.2s;border:1px solid var(--border)}
        .gallery img:hover{transform:scale(1.02);border-color:var(--accent)}

        /* Specs grid */
        .specs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px}
        .spec-item{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:12px;text-align:center}
        .spec-item .val{font-size:1.2rem;font-weight:700;color:var(--accent)}
        .spec-item .lbl{font-size:.72rem;color:var(--muted);margin-top:2px;text-transform:uppercase}

        /* Features */
        .features-list{display:flex;flex-wrap:wrap;gap:8px}
        .feature-tag{background:rgba(99,102,241,.1);color:#a5b4fc;padding:4px 12px;border-radius:6px;font-size:.78rem}

        /* Agent card */
        .agent-card{display:flex;gap:14px;align-items:center;margin-bottom:16px}
        .agent-photo{width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--border)}
        .agent-placeholder{width:60px;height:60px;border-radius:50%;background:rgba(99,102,241,.2);display:flex;align-items:center;justify-content:center;font-size:1.5rem}
        .agent-info .name{font-weight:700;font-size:.95rem}
        .agent-info .title{font-size:.75rem;color:var(--muted)}

        .contact-row{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:.85rem;border-bottom:1px solid rgba(51,65,85,.4)}
        .contact-row:last-child{border-bottom:none}
        .contact-row .icon{font-size:1rem;width:24px;text-align:center}

        /* Inquiry form */
        .inq-form label{display:block;font-size:.78rem;font-weight:600;color:var(--text);margin-bottom:4px}
        .inq-form input,.inq-form textarea{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit;margin-bottom:10px}
        .inq-form textarea{min-height:80px;resize:vertical}
        .btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:10px 24px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.88rem;width:100%}
        .msg-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#34d399;padding:12px;border-radius:8px;font-size:.85rem;margin-bottom:12px}

        /* Related */
        .related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px}
        .related-card{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:16px;transition:.2s}
        .related-card:hover{border-color:var(--accent);transform:translateY(-2px)}
        .related-card a{color:var(--text);display:block}
        .related-card h4{font-size:.9rem;margin-bottom:4px}
        .related-card .price{font-weight:700;color:#10b981;font-size:.88rem}
        .related-card .desc{font-size:.78rem;color:var(--muted)}
    </style>
</head>
<body>
    <div class="detail-header">
        <div class="detail-header-inner">
            <div class="breadcrumb"><a href="/properties">Properties</a> › <?= h($property['title']) ?></div>
            <div class="badges">
                <?php if ($property['status'] === 'sold'): ?><span class="badge badge-sold">SOLD</span>
                <?php elseif ($property['status'] === 'rented'): ?><span class="badge badge-sold">RENTED</span>
                <?php else: ?><span class="badge badge-<?= h($property['listing_type']) ?>">For <?= h($property['listing_type']) ?></span><?php endif; ?>
                <span class="badge badge-type"><?= ucfirst(h($property['property_type'])) ?></span>
                <?php if ($property['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
            </div>
            <h1><?= h($property['title']) ?></h1>
            <div class="price-big"><?= $symbol ?><?= number_format((float)$property['price']) ?><?= $property['listing_type']==='rent'?' <span style="font-size:.8rem;font-weight:400;color:var(--muted)">/month</span>':'' ?></div>
            <div class="meta">
                <?php if ($property['bedrooms'] !== null): ?><span>🛏 <?= $property['bedrooms'] ?> bedroom<?= $property['bedrooms']!=1?'s':'' ?></span><?php endif; ?>
                <?php if ($property['bathrooms'] !== null): ?><span>🚿 <?= $property['bathrooms'] ?> bathroom<?= $property['bathrooms']!=1?'s':'' ?></span><?php endif; ?>
                <?php if ($property['area_sqft']): ?><span>📐 <?= number_format($property['area_sqft']) ?> sq ft</span><?php endif; ?>
                <?php if ($property['city']): ?><span>📍 <?= h($property['city']) ?><?= $property['state'] ? ', ' . h($property['state']) : '' ?></span><?php endif; ?>
                <span style="color:var(--muted);font-size:.75rem">👁 <?= number_format($property['view_count']) ?> views</span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="detail-grid">
            <div>
                <?php if (!empty($property['images'])): ?>
                <div class="card">
                    <h3>🖼️ Gallery</h3>
                    <div class="gallery">
                        <?php foreach ($property['images'] as $img): ?>
                        <img src="<?= h($img) ?>" alt="<?= h($property['title']) ?>" onclick="window.open(this.src)">
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($property['short_description']): ?>
                <div class="card">
                    <p style="font-size:1rem;font-style:italic;color:var(--muted)"><?= h($property['short_description']) ?></p>
                </div>
                <?php endif; ?>

                <div class="card">
                    <h3>📝 Description</h3>
                    <div class="description"><?= nl2br(h($property['description'] ?? '')) ?></div>
                </div>

                <div class="card">
                    <h3>📐 Specifications</h3>
                    <div class="specs-grid">
                        <?php if ($property['bedrooms'] !== null): ?><div class="spec-item"><div class="val"><?= $property['bedrooms'] ?></div><div class="lbl">Bedrooms</div></div><?php endif; ?>
                        <?php if ($property['bathrooms'] !== null): ?><div class="spec-item"><div class="val"><?= $property['bathrooms'] ?></div><div class="lbl">Bathrooms</div></div><?php endif; ?>
                        <?php if ($property['area_sqft']): ?><div class="spec-item"><div class="val"><?= number_format($property['area_sqft']) ?></div><div class="lbl">Sq Ft</div></div><?php endif; ?>
                        <?php if ($property['lot_size']): ?><div class="spec-item"><div class="val"><?= number_format($property['lot_size']) ?></div><div class="lbl">Lot (sqft)</div></div><?php endif; ?>
                        <?php if ($property['year_built']): ?><div class="spec-item"><div class="val"><?= $property['year_built'] ?></div><div class="lbl">Year Built</div></div><?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($property['features'])): ?>
                <div class="card">
                    <h3>✨ Features</h3>
                    <div class="features-list">
                        <?php foreach ($property['features'] as $f): ?><span class="feature-tag">✓ <?= h($f) ?></span><?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($related)): ?>
                <div class="card">
                    <h3>🔗 Similar Properties</h3>
                    <div class="related-grid">
                        <?php foreach ($related as $rel): ?>
                        <div class="related-card">
                            <a href="/properties/<?= h($rel['slug']) ?>">
                                <div class="price"><?= $symbol ?><?= number_format((float)$rel['price']) ?></div>
                                <h4><?= h($rel['title']) ?></h4>
                                <div class="desc"><?= $rel['bedrooms'] !== null ? $rel['bedrooms'] . ' bed' : '' ?><?= $rel['bathrooms'] !== null ? ' · ' . $rel['bathrooms'] . ' bath' : '' ?><?= $rel['city'] ? ' · ' . h($rel['city']) : '' ?></div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <aside>
                <?php if ($property['agent_name']): ?>
                <div class="card">
                    <h3>👤 Listed By</h3>
                    <div class="agent-card">
                        <?php if ($property['agent_photo']): ?><img src="<?= h($property['agent_photo']) ?>" class="agent-photo" alt="<?= h($property['agent_name']) ?>"><?php else: ?><div class="agent-placeholder">👤</div><?php endif; ?>
                        <div class="agent-info">
                            <div class="name"><?= h($property['agent_name']) ?></div>
                            <?php if ($property['agent_license']): ?><div class="title">License: <?= h($property['agent_license']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <?php if ($property['agent_phone']): ?><div class="contact-row"><span class="icon">📱</span><a href="tel:<?= h($property['agent_phone']) ?>"><?= h($property['agent_phone']) ?></a></div><?php endif; ?>
                    <?php if ($property['agent_email']): ?><div class="contact-row"><span class="icon">✉️</span><a href="mailto:<?= h($property['agent_email']) ?>"><?= h($property['agent_email']) ?></a></div><?php endif; ?>
                    <?php if ($property['agent_bio']): ?><p style="font-size:.82rem;color:var(--muted);margin-top:10px"><?= h(substr($property['agent_bio'], 0, 200)) ?></p><?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <h3>📍 Location</h3>
                    <?php if ($property['address']): ?><div class="contact-row"><span class="icon">📍</span><span><?= h($property['address']) ?><?= $property['city'] ? ', ' . h($property['city']) : '' ?><?= $property['state'] ? ', ' . h($property['state']) : '' ?> <?= h($property['zip'] ?? '') ?></span></div><?php endif; ?>
                    <?php if ($property['virtual_tour']): ?><div class="contact-row"><span class="icon">🎥</span><a href="<?= h($property['virtual_tour']) ?>" target="_blank">Virtual Tour</a></div><?php endif; ?>
                    <?php if ($property['floor_plan']): ?><div class="contact-row"><span class="icon">📋</span><a href="<?= h($property['floor_plan']) ?>" target="_blank">Floor Plan</a></div><?php endif; ?>
                </div>

                <?php if ($property['status'] === 'active'): ?>
                <div class="card">
                    <h3>📩 Inquire About This Property</h3>
                    <div id="inq-msg"></div>
                    <form class="inq-form" id="inquiryForm" onsubmit="submitInquiry(event)">
                        <label>Your Name *</label><input type="text" id="inq-name" required>
                        <label>Email *</label><input type="email" id="inq-email" required>
                        <label>Phone</label><input type="tel" id="inq-phone">
                        <label>Message</label><textarea id="inq-message" placeholder="I'm interested in this property..."></textarea>
                        <button type="submit" class="btn-primary">📤 Send Inquiry</button>
                    </form>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>

    <script>
    function submitInquiry(e) {
        e.preventDefault();
        var data = {
            property_id: <?= (int)$property['id'] ?>,
            name: document.getElementById('inq-name').value,
            email: document.getElementById('inq-email').value,
            phone: document.getElementById('inq-phone').value,
            message: document.getElementById('inq-message').value
        };
        fetch('/api/realestate/inquiry', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) {
                document.getElementById('inq-msg').innerHTML = '<div class="msg-success">✅ Thank you! Your inquiry has been submitted. We\'ll be in touch soon.</div>';
                document.getElementById('inquiryForm').reset();
            } else {
                alert(d.error || 'Error submitting inquiry.');
            }
        })
        .catch(function() { alert('Network error.'); });
    }
    </script>
</body>
</html>
