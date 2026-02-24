<?php
/**
 * Jessie Portfolio — Public Portfolio Page
 * URL: /portfolio
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-portfolio/includes/class-portfolio-project.php';
require_once CMS_ROOT . '/plugins/jessie-portfolio/includes/class-portfolio-category.php';
require_once CMS_ROOT . '/plugins/jessie-portfolio/includes/class-portfolio-testimonial.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$categories = \PortfolioCategory::getWithCounts();
$activeCategories = array_filter($categories, fn($c) => $c['status'] === 'active' && (int)$c['project_count'] > 0);
$result = \PortfolioProject::getAll(['status' => 'published'], 1, 100);
$featuredTestimonials = \PortfolioTestimonial::getFeatured(4);

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: 'Portfolio'; } catch (\Exception $e) { $siteTitle = 'Portfolio'; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio — <?= h($siteTitle) ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#7c3aed;--accent2:#a855f7}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}
        a:hover{color:var(--accent2)}

        /* Hero */
        .pf-hero{background:linear-gradient(135deg,rgba(124,58,237,.15) 0%,rgba(168,85,247,.1) 100%);border-bottom:1px solid var(--border);padding:48px 20px;text-align:center}
        .pf-hero h1{font-size:2.2rem;font-weight:800;margin-bottom:8px;background:linear-gradient(135deg,#c4b5fd,#e9d5ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .pf-hero p{color:var(--muted);font-size:1.05rem;max-width:600px;margin:0 auto 24px}

        /* Filter bar */
        .filter-bar{max-width:1200px;margin:0 auto;padding:20px 20px 0;display:flex;gap:8px;flex-wrap:wrap;justify-content:center}
        .filter-btn{background:var(--bg-card);border:1px solid var(--border);color:var(--muted);padding:8px 18px;border-radius:20px;font-size:.82rem;font-weight:600;cursor:pointer;transition:.2s}
        .filter-btn:hover,.filter-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}

        /* Grid */
        .pf-container{max-width:1200px;margin:0 auto;padding:24px 20px}
        .pf-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px}
        .project-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.3s;opacity:1}
        .project-card:hover{border-color:var(--accent);transform:translateY(-4px);box-shadow:0 12px 40px rgba(124,58,237,.15)}
        .project-card.hidden{display:none}
        .project-card a{color:inherit;text-decoration:none;display:block}
        .card-cover{width:100%;height:200px;object-fit:cover;background:linear-gradient(135deg,rgba(124,58,237,.2),rgba(168,85,247,.1));display:flex;align-items:center;justify-content:center}
        .card-cover img{width:100%;height:100%;object-fit:cover}
        .card-cover .placeholder{font-size:3rem}
        .card-body{padding:18px}
        .card-badges{display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap}
        .badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .badge-cat{background:rgba(124,58,237,.15);color:#c4b5fd}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .card-body h3{font-size:1.05rem;font-weight:700;margin-bottom:4px;line-height:1.3}
        .card-body .desc{font-size:.82rem;color:var(--muted);line-height:1.5;margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .card-meta{display:flex;gap:12px;flex-wrap:wrap;font-size:.75rem;color:var(--muted)}
        .card-techs{display:flex;gap:4px;flex-wrap:wrap;margin-top:8px}
        .tech-tag{background:rgba(124,58,237,.1);color:#c4b5fd;padding:2px 8px;border-radius:4px;font-size:.68rem;font-weight:600}

        /* Testimonials section */
        .testimonials-section{max-width:1200px;margin:0 auto;padding:40px 20px}
        .testimonials-section h2{font-size:1.4rem;font-weight:700;text-align:center;margin-bottom:24px}
        .testimonials-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
        .testimonial-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px}
        .testimonial-card .quote{font-size:.88rem;line-height:1.6;color:var(--text);margin-bottom:12px;font-style:italic}
        .testimonial-card .author{display:flex;align-items:center;gap:10px}
        .testimonial-card .avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:.85rem;color:#fff;font-weight:700;flex-shrink:0}
        .testimonial-card .avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover}
        .testimonial-card .name{font-weight:600;font-size:.85rem}
        .testimonial-card .role{font-size:.72rem;color:var(--muted)}
        .stars{color:#f59e0b;font-size:.78rem}

        .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:.85rem;text-decoration:none;margin-bottom:20px}
        .back-link:hover{color:var(--accent)}
        .no-results{text-align:center;padding:60px 20px;color:var(--muted)}
    </style>
</head>
<body>
<div class="pf-hero">
    <h1>🎨 Our Portfolio</h1>
    <p>Explore our latest projects and creative work</p>
</div>

<?php if (!empty($activeCategories)): ?>
<div class="filter-bar">
    <button class="filter-btn active" data-category="all">All Projects</button>
    <?php foreach ($activeCategories as $c): ?>
    <button class="filter-btn" data-category="<?= h($c['slug']) ?>"><?= h(($c['icon'] ? $c['icon'] . ' ' : '') . $c['name']) ?> <span style="opacity:.5">(<?= $c['project_count'] ?>)</span></button>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="pf-container">
    <?php if (empty($result['projects'])): ?>
    <div class="no-results"><p style="font-size:1.2rem">No projects yet.</p><p>Check back soon!</p></div>
    <?php else: ?>
    <div class="pf-grid">
        <?php foreach ($result['projects'] as $p): ?>
        <div class="project-card" data-category="<?= h($p['category_slug'] ?? '') ?>">
            <a href="/portfolio/<?= h($p['slug']) ?>">
                <div class="card-cover">
                    <?php if ($p['cover_image']): ?><img src="<?= h($p['cover_image']) ?>" alt="<?= h($p['title']) ?>"><?php else: ?><span class="placeholder">💼</span><?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="card-badges">
                        <?php if ($p['category_name']): ?><span class="badge badge-cat"><?= h($p['category_name']) ?></span><?php endif; ?>
                        <?php if ($p['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                    </div>
                    <h3><?= h($p['title']) ?></h3>
                    <?php if ($p['short_description']): ?><div class="desc"><?= h($p['short_description']) ?></div><?php endif; ?>
                    <div class="card-meta">
                        <?php if ($p['client_name']): ?><span>👤 <?= h($p['client_name']) ?></span><?php endif; ?>
                        <?php if ($p['completion_date']): ?><span>📅 <?= date('M Y', strtotime($p['completion_date'])) ?></span><?php endif; ?>
                    </div>
                    <?php if (!empty($p['technologies'])): ?>
                    <div class="card-techs">
                        <?php foreach (array_slice($p['technologies'], 0, 4) as $tech): ?><span class="tech-tag"><?= h($tech) ?></span><?php endforeach; ?>
                        <?php if (count($p['technologies']) > 4): ?><span class="tech-tag">+<?= count($p['technologies']) - 4 ?></span><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($featuredTestimonials)): ?>
<div class="testimonials-section">
    <h2>💬 What Our Clients Say</h2>
    <div class="testimonials-grid">
        <?php foreach ($featuredTestimonials as $t): ?>
        <div class="testimonial-card">
            <div class="stars"><?= str_repeat('★', (int)$t['rating']) ?><?= str_repeat('☆', 5 - (int)$t['rating']) ?></div>
            <div class="quote">"<?= h($t['content']) ?>"</div>
            <div class="author">
                <div class="avatar">
                    <?php if ($t['client_photo']): ?><img src="<?= h($t['client_photo']) ?>" alt=""><?php else: ?><?= mb_substr($t['client_name'], 0, 1) ?><?php endif; ?>
                </div>
                <div>
                    <div class="name"><?= h($t['client_name']) ?></div>
                    <div class="role"><?= h(implode(', ', array_filter([$t['client_title'] ?? '', $t['client_company'] ?? '']))) ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
(function(){
    var btns=document.querySelectorAll('.filter-btn');
    var cards=document.querySelectorAll('.project-card');
    btns.forEach(function(btn){
        btn.addEventListener('click',function(){
            btns.forEach(function(b){b.classList.remove('active')});
            btn.classList.add('active');
            var cat=btn.getAttribute('data-category');
            cards.forEach(function(card){
                if(cat==='all'||card.getAttribute('data-category')===cat){
                    card.classList.remove('hidden');
                }else{
                    card.classList.add('hidden');
                }
            });
        });
    });
})();
</script>
</body>
</html>
