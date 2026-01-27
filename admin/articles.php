<?php
/**
 * Articles Management - Modern Dark UI
 */
declare(strict_types=1);
define('CMS_ROOT', realpath(__DIR__ . '/..'));
require_once __DIR__ . '/../includes/init.php'; // Session init
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/core/database.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$db = \core\Database::connection();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $action = $_POST['action'] ?? '';
    $aid = (int)($_POST['article_id'] ?? 0);
    if ($aid > 0) {
        if ($action === 'delete') { $db->prepare("UPDATE articles SET status='archived' WHERE id=?")->execute([$aid]); $msg = 'Moved to archive.'; }
        elseif ($action === 'restore') { $db->prepare("UPDATE articles SET status='draft' WHERE id=?")->execute([$aid]); $msg = 'Restored.'; }
        elseif ($action === 'permanent_delete') { $db->prepare("DELETE FROM articles WHERE id=?")->execute([$aid]); $msg = 'Deleted.'; }
    }
}

$status = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = $params = [];
if ($status !== 'all') { $where[] = "a.status=?"; $params[] = $status; }
else { $where[] = "a.status!='archived'"; }
if ($category) { $where[] = "a.category_id=?"; $params[] = (int)$category; }
if ($search) { $where[] = "(a.title LIKE ? OR a.content LIKE ?)"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT COUNT(*) FROM articles a {$whereClause}");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

$sql = "SELECT a.*, (SELECT name FROM article_categories WHERE id=a.category_id) as cat FROM articles a {$whereClause} ORDER BY a.updated_at DESC LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $db->query("SELECT * FROM article_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$counts = ['all' => 0, 'draft' => 0, 'published' => 0, 'archived' => 0];
$stmt = $db->query("SELECT status, COUNT(*) as c FROM articles GROUP BY status");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (isset($counts[$r['status']])) {
        $counts[$r['status']] = (int)$r['c'];
    }
}
$counts['all'] = $counts['draft'] + $counts['published'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Articles - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.container{max-width:1400px;margin:0 auto;padding:24px 32px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;font-size:13px;font-weight:500;border:none;border-radius:8px;cursor:pointer;transition:.15s;text-decoration:none}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.btn-secondary{background:var(--bg3);color:var(--text);border:1px solid var(--border)}
.btn-danger{background:rgba(243,139,168,.2);color:var(--danger);border:1px solid rgba(243,139,168,.3)}
.btn-sm{padding:6px 12px;font-size:12px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:16px;background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.tabs{display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:12px}
.tab{padding:8px 16px;font-size:13px;color:var(--muted);text-decoration:none;border-radius:6px;transition:.15s}
.tab:hover{color:var(--text);background:rgba(137,180,250,.1)}
.tab.active{color:var(--accent);font-weight:600;border-bottom:2px solid var(--accent)}
.tab .count{background:var(--bg3);padding:2px 8px;border-radius:10px;font-size:11px;margin-left:6px}
.filters{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;align-items:center}
.filters select,.filters input{padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:13px}
.filters input{min-width:200px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
table{width:100%;border-collapse:collapse}
th,td{padding:14px 16px;text-align:left;border-bottom:1px solid var(--border)}
th{font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;background:var(--bg)}
tr:hover td{background:rgba(137,180,250,.03)}
.article-title{font-weight:600;color:var(--text);text-decoration:none;display:block}
.article-title:hover{color:var(--accent)}
.excerpt{font-size:12px;color:var(--muted);margin-top:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.meta{font-size:11px;color:var(--muted);margin-top:6px}
.tag{display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500}
.tag-success{background:rgba(166,227,161,.2);color:var(--success)}
.tag-warning{background:rgba(249,226,175,.2);color:var(--warning)}
.tag-info{background:rgba(137,180,250,.2);color:var(--accent)}
.tag-danger{background:rgba(243,139,168,.2);color:var(--danger)}
.actions{display:flex;gap:6px}
.empty{text-align:center;padding:50px;color:var(--muted)}
.pagination{display:flex;justify-content:center;gap:6px;margin-top:20px}
.pagination a{padding:8px 14px;background:var(--bg2);border:1px solid var(--border);border-radius:6px;color:var(--text);text-decoration:none;font-size:13px}
.pagination a:hover{border-color:var(--accent)}
.pagination a.active{background:var(--accent);color:#000;border-color:var(--accent)}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '📝',
    'title' => 'Articles',
    'description' => 'Manage blog posts',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--accent-color), var(--purple)',
    'actions' => [
        ['type' => 'link', 'url' => '/admin/article-edit.php', 'text' => '✏️ New Article', 'class' => 'primary'],
    ]
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">
<?php if ($msg): ?><div class="alert">✅ <?= esc($msg) ?></div><?php endif; ?>

<div class="tabs">
<a href="?status=all" class="tab <?= $status === 'all' ? 'active' : '' ?>">All <span class="count"><?= $counts['all'] ?></span></a>
<a href="?status=published" class="tab <?= $status === 'published' ? 'active' : '' ?>">Published <span class="count"><?= $counts['published'] ?></span></a>
<a href="?status=draft" class="tab <?= $status === 'draft' ? 'active' : '' ?>">Drafts <span class="count"><?= $counts['draft'] ?></span></a>
<a href="?status=archived" class="tab <?= $status === 'archived' ? 'active' : '' ?>">Archived <span class="count"><?= $counts['archived'] ?></span></a>
</div>

<form method="get" class="filters">
<input type="hidden" name="status" value="<?= esc($status) ?>">
<select name="category" onchange="this.form.submit()">
<option value="">All Categories</option>
<?php foreach ($categories as $c): ?>
<option value="<?= $c['id'] ?>" <?= $category == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
<?php endforeach; ?>
</select>
<input type="search" name="search" placeholder="Search articles..." value="<?= esc($search) ?>">
<button type="submit" class="btn btn-secondary btn-sm">🔍 Search</button>
</form>

<?php if (empty($articles)): ?>
<div class="empty"><p>No articles found.</p><?php if ($status !== 'archived'): ?><a href="/admin/article-edit.php" class="btn btn-primary" style="margin-top:16px">✏️ Create Article</a><?php endif; ?></div>
<?php else: ?>
<div class="card">
<table>
<thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($articles as $a): 
$tagClass = match($a['status']) { 'published' => 'success', 'draft' => 'warning', 'archived' => 'danger', default => 'warning' };
?>
<tr>
<td>
<a href="/admin/article-edit.php?id=<?= $a['id'] ?>" class="article-title"><?= esc($a['title']) ?></a>
<?php if ($a['excerpt']): ?><div class="excerpt"><?= esc($a['excerpt']) ?></div><?php endif; ?>
<div class="meta">👁️ <?= number_format($a['views'] ?? 0) ?> views</div>
</td>
<td><?= $a['cat'] ? esc($a['cat']) : '—' ?></td>
<td><span class="tag tag-<?= $tagClass ?>"><?= ucfirst($a['status']) ?></span></td>
<td><div style="font-size:13px"><?= date('M j, Y', strtotime($a['updated_at'])) ?></div><div style="font-size:11px;color:var(--muted)"><?= date('g:i A', strtotime($a['updated_at'])) ?></div></td>
<td class="actions">
<?php if ($status === 'archived'): ?>
<form method="post" style="display:inline"><?php csrf_field(); ?><input type="hidden" name="action" value="restore"><input type="hidden" name="article_id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-secondary">↩️ Restore</button></form>
<form method="post" style="display:inline" onsubmit="return confirm('Permanently delete?')"><?php csrf_field(); ?><input type="hidden" name="action" value="permanent_delete"><input type="hidden" name="article_id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-danger">🗑️</button></form>
<?php else: ?>
<a href="/admin/article-edit.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-secondary">✏️</a>
<a href="/article/<?= esc($a['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary">👁️</a>
<form method="post" style="display:inline" onsubmit="return confirm('Archive this article?')"><?php csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="article_id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-danger">📥</button></form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
<?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&status=<?= $status ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>">← Prev</a><?php endif; ?>
<?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
<a href="?page=<?= $i ?>&status=<?= $status ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
<?php endfor; ?>
<?php if ($page < $totalPages): ?><a href="?page=<?= $page+1 ?>&status=<?= $status ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>">Next →</a><?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
