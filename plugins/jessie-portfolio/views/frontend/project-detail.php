<?php
/**
 * Jessie Portfolio — Public Project Detail Page
 * URL: /portfolio/{slug}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-portfolio/includes/class-portfolio-project.php';
require_once CMS_ROOT . '/plugins/jessie-portfolio/includes/class-portfolio-testimonial.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$slug = $portfolioSlug ?? '';
$project = \PortfolioProject::getBySlug($slug);

if (!$project || $project['status'] !== 'published') {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Not Found</title></head><body style="background:#0f172a;color:#e2e8f0;font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh"><div style="text-align:center"><h1>404</h1><p>Project not found.</p><a href="/portfolio" style="color:#7c3aed">← Back to Portfolio</a></div></body></html>';
    exit;
}

\PortfolioProject::incrementViews((int)$project['id']);
$testimonials = \PortfolioTestimonial::getForProject((int)$project['id']);
$related = [];
if ($project['category_id']) {
    $related = \PortfolioProject::getRelated((int)$project['id'], (int)$project['category_id'], 3);
}

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($project['title']) ?> — <?= h($siteTitle ?: 'Portfolio') ?></title>
    <meta name="description" content="<?= h($project['short_description'] ?: mb_substr(strip_tags($project['description']), 0, 160)) ?>">
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#7c3aed;--accent2:#a855f7}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}

        .detail-header{background:linear-gradient(135deg,rgba(124,58,237,.12) 0%,rgba(168,85,247,.08) 100%);border-bottom:1px solid var(--border);padding:32px 20px}
        .detail-header-inner{max-width:1000px;margin:0 auto}
        .breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:12px}
        .breadcrumb a{color:var(--muted)}.breadcrumb a:hover{color:var(--accent)}
        .detail-header h1{font-size:1.8rem;font-weight:800;margin-bottom:8px}
        .detail-header .badges{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
        .badge{padding:3px 10px;border-radius:5px;font-size:.7rem;font-weight:700;text-transform:uppercase}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-cat{background:rgba(124,58,237,.15);color:#c4b5fd}
        .detail-header .meta{display:flex;gap:16px;flex-wrap:wrap;font-size:.85rem;color:var(--muted)}
        .detail-header .meta span{display:flex;align-items:center;gap:4px}

        .detail-container{max-width:1000px;margin:0 auto;padding:24px 20px}
        .detail-grid{display:grid;grid-template-columns:1fr 320px;gap:24px}
        @media(max-width:768px){.detail-grid{grid-template-columns:1fr}}

        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:22px;margin-bottom:20px}
        .card h3{font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border)}

        .description{font-size:.92rem;line-height:1.75;color:var(--text)}
        .description p{margin-bottom:12px}

        /* Gallery */
        .gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px}
        .gallery img{width:100%;height:120px;object-fit:cover;border-radius:8px;cursor:pointer;transition:.2s;border:1px solid var(--border)}
        .gallery img:hover{transform:scale(1.03);border-color:var(--accent)}

        /* Lightbox */
        .lightbox{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.9);display:none;align-items:center;justify-content:center;z-index:1000;cursor:pointer}
        .lightbox.show{display:flex}
        .lightbox img{max-width:90%;max-height:90%;border-radius:8px;box-shadow:0 20px 60px rgba(0,0,0,.5)}
        .lightbox-close{position:absolute;top:20px;right:24px;color:#fff;font-size:2rem;cursor:pointer;z-index:1001}
        .lightbox-nav{position:absolute;top:50%;transform:translateY(-50%);color:#fff;font-size:2rem;cursor:pointer;padding:20px;z-index:1001}
        .lightbox-prev{left:10px}
        .lightbox-next{right:10px}

        /* Tech stack */
        .tech-list{display:flex;gap:8px;flex-wrap:wrap}
        .tech-chip{background:rgba(124,58,237,.12);color:#c4b5fd;padding:6px 14px;border-radius:20px;font-size:.8rem;font-weight:600;border:1px solid rgba(124,58,237,.2)}

        /* Sidebar */
        .info-row{display:flex;align-items:center;gap:10px;padding:8px 0;font-size:.85rem;border-bottom:1px solid rgba(51,65,85,.4)}
        .info-row:last-child{border-bottom:none}
        .info-row .icon{font-size:1rem;width:24px;text-align:center}
        .btn-project{display:block;text-align:center;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;padding:12px;border-radius:10px;font-weight:700;font-size:.9rem;margin-top:12px;transition:.2s}
        .btn-project:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(124,58,237,.3);color:#fff}

        /* Testimonials */
        .testimonial{padding:14px 0;border-bottom:1px solid rgba(51,65,85,.4)}
        .testimonial:last-child{border-bottom:none}
        .stars{color:#f59e0b;font-size:.8rem}

        /* Related */
        .related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px}
        .related-card{background:var(--bg-card);border:1px solid var(--border);border-radius:10px;overflow:hidden;transition:.2s}
        .related-card:hover{border-color:var(--accent);transform:translateY(-2px)}
        .related-card a{color:inherit;text-decoration:none;display:block;padding:16px}
        .related-card h4{font-size:.9rem;font-weight:600;margin-bottom:4px}
        .related-card .meta{font-size:.75rem;color:var(--muted)}

        .cover-hero{width:100%;max-height:400px;object-fit:cover;border-radius:12px;margin-bottom:20px;border:1px solid var(--border)}
    </style>
</head>
<body>
<div class="detail-header">
    <div class="detail-header-inner">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/portfolio">Portfolio</a> / <?= h($project['title']) ?></div>
        <div class="badges">
            <?php if ($project['category_name']): ?><span class="badge badge-cat"><?= h($project['category_name']) ?></span><?php endif; ?>
            <?php if ($project['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
        </div>
        <h1><?= h($project['title']) ?></h1>
        <div class="meta">
            <?php if ($project['client_name']): ?><span>👤 <?= h($project['client_name']) ?></span><?php endif; ?>
            <?php if ($project['completion_date']): ?><span>📅 <?= date('F Y', strtotime($project['completion_date'])) ?></span><?php endif; ?>
            <span>👁 <?= number_format($project['view_count']) ?> views</span>
        </div>
    </div>
</div>

<div class="detail-container">
    <?php if ($project['cover_image']): ?>
    <img src="<?= h($project['cover_image']) ?>" alt="<?= h($project['title']) ?>" class="cover-hero">
    <?php endif; ?>

    <div class="detail-grid">
        <div class="main-content">
            <?php if ($project['description']): ?>
            <div class="card">
                <h3>📋 About This Project</h3>
                <div class="description"><?= nl2br(h($project['description'])) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($project['images'])): ?>
            <div class="card">
                <h3>🖼️ Gallery</h3>
                <div class="gallery">
                    <?php foreach ($project['images'] as $i => $img): ?>
                    <img src="<?= h($img) ?>" alt="<?= h($project['title']) ?> - Image <?= $i + 1 ?>" onclick="openLightbox(<?= $i ?>)" loading="lazy">
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($project['technologies'])): ?>
            <div class="card">
                <h3>🛠️ Tech Stack</h3>
                <div class="tech-list">
                    <?php foreach ($project['technologies'] as $tech): ?>
                    <span class="tech-chip"><?= h($tech) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($testimonials)): ?>
            <div class="card">
                <h3>💬 Client Testimonials</h3>
                <?php foreach ($testimonials as $t): ?>
                <div class="testimonial">
                    <div class="stars"><?= str_repeat('★', (int)$t['rating']) ?><?= str_repeat('☆', 5 - (int)$t['rating']) ?></div>
                    <p style="font-size:.88rem;line-height:1.6;margin:8px 0;font-style:italic">"<?= h($t['content']) ?>"</p>
                    <div style="font-size:.82rem;font-weight:600"><?= h($t['client_name']) ?></div>
                    <?php if ($t['client_title'] || $t['client_company']): ?>
                    <div style="font-size:.72rem;color:var(--muted)"><?= h(implode(', ', array_filter([$t['client_title'], $t['client_company']]))) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="sidebar">
            <div class="card">
                <h3>📌 Project Details</h3>
                <?php if ($project['client_name']): ?>
                <div class="info-row"><span class="icon">👤</span><div><strong>Client</strong><br><span style="color:var(--muted)"><?= h($project['client_name']) ?></span></div></div>
                <?php endif; ?>
                <?php if ($project['category_name']): ?>
                <div class="info-row"><span class="icon">📁</span><div><strong>Category</strong><br><span style="color:var(--muted)"><?= h($project['category_name']) ?></span></div></div>
                <?php endif; ?>
                <?php if ($project['completion_date']): ?>
                <div class="info-row"><span class="icon">📅</span><div><strong>Completed</strong><br><span style="color:var(--muted)"><?= date('F j, Y', strtotime($project['completion_date'])) ?></span></div></div>
                <?php endif; ?>
                <?php if (!empty($project['technologies'])): ?>
                <div class="info-row"><span class="icon">🛠️</span><div><strong>Technologies</strong><br><span style="color:var(--muted)"><?= h(implode(', ', $project['technologies'])) ?></span></div></div>
                <?php endif; ?>
                <?php if ($project['project_url']): ?>
                <a href="<?= h($project['project_url']) ?>" target="_blank" class="btn-project">🔗 View Live Project</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div style="margin-top:20px">
        <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:16px">🔗 Related Projects</h2>
        <div class="related-grid">
            <?php foreach ($related as $r): ?>
            <div class="related-card">
                <a href="/portfolio/<?= h($r['slug']) ?>">
                    <h4><?= h($r['title']) ?></h4>
                    <div class="meta">
                        <?php if ($r['category_name']): ?><span><?= h($r['category_name']) ?></span><?php endif; ?>
                        <?php if ($r['client_name']): ?><span> · <?= h($r['client_name']) ?></span><?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($project['images'])): ?>
<div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <span class="lightbox-nav lightbox-prev" onclick="event.stopPropagation();navLightbox(-1)">&#8249;</span>
    <img id="lightbox-img" src="" alt="">
    <span class="lightbox-nav lightbox-next" onclick="event.stopPropagation();navLightbox(1)">&#8250;</span>
</div>
<script>
var lbImages=<?= json_encode($project['images']) ?>;
var lbIndex=0;
function openLightbox(i){lbIndex=i;document.getElementById('lightbox-img').src=lbImages[i];document.getElementById('lightbox').classList.add('show');document.body.style.overflow='hidden';}
function closeLightbox(e){if(e&&e.target.tagName==='IMG')return;document.getElementById('lightbox').classList.remove('show');document.body.style.overflow='';}
function navLightbox(dir){lbIndex=(lbIndex+dir+lbImages.length)%lbImages.length;document.getElementById('lightbox-img').src=lbImages[lbIndex];}
document.addEventListener('keydown',function(e){if(!document.getElementById('lightbox').classList.contains('show'))return;if(e.key==='Escape')closeLightbox();if(e.key==='ArrowLeft')navLightbox(-1);if(e.key==='ArrowRight')navLightbox(1);});
</script>
<?php endif; ?>

</body>
</html>
