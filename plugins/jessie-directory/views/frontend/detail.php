<?php
/**
 * Jessie Directory — Public Listing Detail Page
 * URL: /directory/{slug}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-listing.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-review.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$slug = $directorySlug ?? '';
$listing = DirectoryListing::getBySlug($slug);

if (!$listing || $listing['status'] !== 'active') {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body style="background:#0f172a;color:#e2e8f0;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh"><div style="text-align:center"><h1>404</h1><p>Listing not found.</p><a href="/directory" style="color:#6366f1">← Back to Directory</a></div></body></html>';
    exit;
}

// Increment views
DirectoryListing::incrementViews((int)$listing['id']);

// Get reviews
$reviews = DirectoryReview::getForListing((int)$listing['id'], 'approved');

// Get related listings (same category)
$related = [];
if ($listing['category_id']) {
    $r = DirectoryListing::getAll(['category_id' => $listing['category_id'], 'status' => 'active'], 1, 4);
    $related = array_filter($r['listings'], fn($l) => (int)$l['id'] !== (int)$listing['id']);
    $related = array_slice($related, 0, 3);
}

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($listing['title']) ?> — <?= h($siteTitle ?: 'Directory') ?></title>
    <meta name="description" content="<?= h($listing['short_description'] ?: substr(strip_tags($listing['description']), 0, 160)) ?>">
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
        .detail-header h1{font-size:1.8rem;font-weight:800;margin-bottom:6px}
        .detail-header .badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
        .badge{padding:3px 10px;border-radius:5px;font-size:.7rem;font-weight:700;text-transform:uppercase}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-verified{background:rgba(16,185,129,.15);color:#34d399}
        .badge-cat{background:rgba(99,102,241,.1);color:#a5b4fc}
        .detail-header .meta{display:flex;gap:16px;flex-wrap:wrap;font-size:.85rem;color:var(--muted)}
        .detail-header .meta span{display:flex;align-items:center;gap:4px}
        .stars{color:#f59e0b}

        .detail-container{max-width:1000px;margin:0 auto;padding:24px 20px}
        .detail-grid{display:grid;grid-template-columns:1fr 320px;gap:24px}
        @media(max-width:768px){.detail-grid{grid-template-columns:1fr}}

        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:22px;margin-bottom:20px}
        .card h3{font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}

        .description{font-size:.92rem;line-height:1.75;color:var(--text)}
        .description p{margin-bottom:12px}

        /* Contact sidebar */
        .contact-row{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:.85rem;border-bottom:1px solid rgba(51,65,85,.4)}
        .contact-row:last-child{border-bottom:none}
        .contact-row .icon{font-size:1rem;width:24px;text-align:center}
        .contact-row a{color:var(--accent)}

        /* Hours */
        .hours-row{display:flex;justify-content:space-between;padding:5px 0;font-size:.82rem;border-bottom:1px solid rgba(51,65,85,.3)}
        .hours-row:last-child{border-bottom:none}
        .hours-row .day{font-weight:600}

        /* Tags */
        .tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}
        .tag{background:rgba(99,102,241,.1);color:#a5b4fc;padding:3px 10px;border-radius:6px;font-size:.75rem}

        /* Reviews */
        .review{border-bottom:1px solid var(--border);padding:16px 0}
        .review:last-child{border-bottom:none}
        .review .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px}
        .review .author{font-weight:600;font-size:.88rem}
        .review .date{font-size:.72rem;color:var(--muted)}
        .review .title{font-weight:600;font-size:.85rem;margin-bottom:4px}
        .review .text{font-size:.85rem;line-height:1.6;color:var(--muted)}
        .review .reply{margin-top:10px;padding:10px;background:rgba(99,102,241,.05);border-left:3px solid var(--accent);border-radius:0 8px 8px 0;font-size:.82rem}
        .review .reply strong{display:block;margin-bottom:4px;color:var(--accent);font-size:.75rem}

        /* Review form */
        .review-form{margin-top:16px}
        .review-form .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
        @media(max-width:500px){.review-form .form-row{grid-template-columns:1fr}}
        .review-form label{display:block;font-size:.78rem;font-weight:600;color:var(--text);margin-bottom:4px}
        .review-form input,.review-form textarea,.review-form select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
        .review-form textarea{min-height:80px;resize:vertical}
        .btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:10px 24px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.88rem}
        .msg-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#34d399;padding:12px;border-radius:8px;font-size:.85rem;margin-bottom:12px}

        /* Related */
        .related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px}
        .related-card{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;padding:16px;transition:.2s}
        .related-card:hover{border-color:var(--accent);transform:translateY(-2px)}
        .related-card a{color:var(--text);display:block}
        .related-card h4{font-size:.9rem;margin-bottom:4px}
        .related-card .desc{font-size:.78rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

        /* Map placeholder */
        .map-placeholder{background:var(--bg);border:1px solid var(--border);border-radius:8px;height:160px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.85rem;margin-top:12px}
    </style>
</head>
<body>
    <div class="detail-header">
        <div class="detail-header-inner">
            <div class="breadcrumb">
                <a href="/directory">Directory</a>
                <?php if (!empty($listing['category_name'])): ?> › <a href="/directory?category=<?= (int)$listing['category_id'] ?>"><?= h($listing['category_name']) ?></a><?php endif; ?>
                › <?= h($listing['title']) ?>
            </div>
            <div class="badges">
                <?php if ($listing['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                <?php if ($listing['is_verified']): ?><span class="badge badge-verified">✓ Verified</span><?php endif; ?>
                <?php if (!empty($listing['category_name'])): ?><span class="badge badge-cat"><?= h($listing['category_name']) ?></span><?php endif; ?>
            </div>
            <h1><?= h($listing['title']) ?></h1>
            <div class="meta">
                <?php if ((float)$listing['avg_rating'] > 0): ?>
                <span><span class="stars"><?= str_repeat('★', (int)round((float)$listing['avg_rating'])) ?></span> <?= number_format((float)$listing['avg_rating'], 1) ?> (<?= $listing['review_count'] ?> review<?= $listing['review_count'] != 1 ? 's' : '' ?>)</span>
                <?php endif; ?>
                <?php if ($listing['city']): ?><span>📍 <?= h($listing['city']) ?><?= $listing['state'] ? ', ' . h($listing['state']) : '' ?></span><?php endif; ?>
                <?php if ($listing['price_range']): ?><span style="color:#10b981;font-weight:700"><?= h($listing['price_range']) ?></span><?php endif; ?>
                <span style="color:var(--muted);font-size:.75rem">👁 <?= number_format($listing['view_count']) ?> views</span>
            </div>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-grid">
            <!-- Main Content -->
            <div>
                <?php if ($listing['short_description']): ?>
                <div class="card">
                    <p style="font-size:1rem;font-style:italic;color:var(--muted)"><?= h($listing['short_description']) ?></p>
                </div>
                <?php endif; ?>

                <div class="card">
                    <h3>📝 About</h3>
                    <div class="description"><?= nl2br(h($listing['description'])) ?></div>
                    <?php if ($listing['tags']): ?>
                    <div class="tags">
                        <?php foreach (explode(',', $listing['tags']) as $tag): ?>
                        <span class="tag"><?= h(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Reviews Section -->
                <div class="card">
                    <h3>⭐ Reviews (<?= count($reviews) ?>)</h3>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $rev): ?>
                        <div class="review">
                            <div class="top">
                                <div>
                                    <span class="author"><?= h($rev['reviewer_name']) ?></span>
                                    <span class="stars" style="margin-left:8px"><?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?></span>
                                </div>
                                <span class="date"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
                            </div>
                            <?php if ($rev['title']): ?><div class="title"><?= h($rev['title']) ?></div><?php endif; ?>
                            <div class="text"><?= nl2br(h($rev['content'])) ?></div>
                            <?php if ($rev['owner_reply']): ?>
                            <div class="reply">
                                <strong>🏢 Owner Reply</strong>
                                <?= nl2br(h($rev['owner_reply'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:var(--muted);font-size:.85rem">No reviews yet. Be the first!</p>
                    <?php endif; ?>

                    <!-- Review Form -->
                    <div class="review-form">
                        <h3 style="border:none;padding:0;margin:16px 0 12px">✍️ Write a Review</h3>
                        <div id="review-msg"></div>
                        <form id="reviewForm" onsubmit="submitReview(event)">
                            <div class="form-row">
                                <div><label>Your Name *</label><input type="text" id="rev-name" required></div>
                                <div><label>Email</label><input type="email" id="rev-email"></div>
                            </div>
                            <div class="form-row">
                                <div><label>Rating *</label><select id="rev-rating"><option value="5">★★★★★ Excellent</option><option value="4">★★★★☆ Very Good</option><option value="3">★★★☆☆ Average</option><option value="2">★★☆☆☆ Poor</option><option value="1">★☆☆☆☆ Terrible</option></select></div>
                                <div><label>Review Title</label><input type="text" id="rev-title" placeholder="Summarize your experience"></div>
                            </div>
                            <div style="margin-bottom:12px"><label>Your Review *</label><textarea id="rev-content" required placeholder="Tell others about your experience..."></textarea></div>
                            <button type="submit" class="btn-primary">📤 Submit Review</button>
                        </form>
                    </div>
                </div>

                <!-- Related -->
                <?php if (!empty($related)): ?>
                <div class="card">
                    <h3>🔗 Related Listings</h3>
                    <div class="related-grid">
                        <?php foreach ($related as $rel): ?>
                        <div class="related-card">
                            <a href="/directory/<?= h($rel['slug']) ?>">
                                <h4><?= h($rel['title']) ?></h4>
                                <div class="desc"><?= h($rel['short_description'] ?: substr($rel['description'], 0, 100)) ?></div>
                                <?php if ((float)$rel['avg_rating'] > 0): ?><span class="stars" style="font-size:.75rem"><?= str_repeat('★', (int)round((float)$rel['avg_rating'])) ?></span><?php endif; ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside>
                <div class="card">
                    <h3>📞 Contact Info</h3>
                    <?php if ($listing['address']): ?>
                    <div class="contact-row"><span class="icon">📍</span><span><?= h($listing['address']) ?><?= $listing['city'] ? ', ' . h($listing['city']) : '' ?><?= $listing['state'] ? ', ' . h($listing['state']) : '' ?> <?= h($listing['zip']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($listing['phone']): ?>
                    <div class="contact-row"><span class="icon">📱</span><a href="tel:<?= h($listing['phone']) ?>"><?= h($listing['phone']) ?></a></div>
                    <?php endif; ?>
                    <?php if ($listing['website']): ?>
                    <div class="contact-row"><span class="icon">🌐</span><a href="<?= h($listing['website']) ?>" target="_blank" rel="noopener"><?= h(preg_replace('#^https?://(www\.)?#', '', $listing['website'])) ?></a></div>
                    <?php endif; ?>
                    <?php if ($listing['owner_email']): ?>
                    <div class="contact-row"><span class="icon">✉️</span><a href="mailto:<?= h($listing['owner_email']) ?>"><?= h($listing['owner_email']) ?></a></div>
                    <?php endif; ?>

                    <?php
                    $socials = $listing['social_links'] ?? [];
                    if (is_string($socials)) $socials = json_decode($socials, true) ?: [];
                    foreach ($socials as $platform => $url):
                        if (empty($url)) continue;
                        $icons = ['facebook' => '📘', 'twitter' => '🐦', 'instagram' => '📸', 'linkedin' => '💼', 'youtube' => '📺'];
                    ?>
                    <div class="contact-row"><span class="icon"><?= $icons[$platform] ?? '🔗' ?></span><a href="<?= h($url) ?>" target="_blank" rel="noopener"><?= ucfirst(h($platform)) ?></a></div>
                    <?php endforeach; ?>

                    <?php if ($listing['latitude'] && $listing['longitude']): ?>
                    <div class="map-placeholder">📍 <?= $listing['latitude'] ?>, <?= $listing['longitude'] ?></div>
                    <?php endif; ?>
                </div>

                <?php
                $hours = $listing['hours'] ?? [];
                if (is_string($hours)) $hours = json_decode($hours, true) ?: [];
                if (!empty($hours)):
                ?>
                <div class="card">
                    <h3>🕐 Business Hours</h3>
                    <?php
                    $dayOrder = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                    foreach ($dayOrder as $day):
                        if (!isset($hours[$day])) continue;
                    ?>
                    <div class="hours-row">
                        <span class="day"><?= ucfirst($day) ?></span>
                        <span><?= h($hours[$day]) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!$listing['is_claimed']): ?>
                <div class="card" style="text-align:center">
                    <p style="font-size:.82rem;color:var(--muted);margin-bottom:10px">Is this your business?</p>
                    <a href="/directory/<?= h($listing['slug']) ?>/claim" class="btn-primary" style="display:inline-block;font-size:.82rem;padding:8px 16px">🏢 Claim Listing</a>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>

    <script>
    function submitReview(e) {
        e.preventDefault();
        var data = {
            listing_id: <?= (int)$listing['id'] ?>,
            reviewer_name: document.getElementById('rev-name').value,
            reviewer_email: document.getElementById('rev-email').value,
            rating: parseInt(document.getElementById('rev-rating').value),
            title: document.getElementById('rev-title').value,
            content: document.getElementById('rev-content').value
        };
        fetch('/api/directory/review', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data),
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) {
                document.getElementById('review-msg').innerHTML = '<div class="msg-success">✅ Thank you! Your review has been submitted and is pending approval.</div>';
                document.getElementById('reviewForm').reset();
            } else {
                alert(d.error || 'Error submitting review.');
            }
        })
        .catch(function() { alert('Network error.'); });
    }
    </script>
</body>
</html>
