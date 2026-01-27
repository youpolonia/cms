<?php
/**
 * Theme Builder 4.0 - Page List View
 * Simple page management dashboard (Pure PHP)
 */
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 5));
}

$pageTitle = $pageTitle ?? 'Theme Builder 4.0';
$csrfToken = $csrfToken ?? csrf_token();

// Fetch pages from database
$db = db();
$stmt = $db->prepare("SELECT id, title, slug, status, created_at, updated_at FROM tb_pages ORDER BY updated_at DESC LIMIT 50");
$stmt->execute();
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> - CMS Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/tb4/design-system.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--tb4-bg-secondary);
            color: var(--tb4-text-primary);
        }
        
        .tb4-header {
            background: var(--tb4-sidebar-bg);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--tb4-sidebar-border);
        }
        .tb4-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .tb4-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--tb4-sidebar-text-active);
            font-weight: 600;
            font-size: 18px;
        }
        .tb4-logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--tb4-primary-500), var(--tb4-primary-700));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }
        .tb4-back-link {
            color: var(--tb4-sidebar-text);
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.15s;
        }
        .tb4-back-link:hover {
            background: var(--tb4-sidebar-bg-hover);
            color: var(--tb4-sidebar-text-active);
        }
        
        .tb4-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }
        .tb4-btn-primary {
            background: var(--tb4-primary-600);
            color: white;
        }
        .tb4-btn-primary:hover {
            background: var(--tb4-primary-700);
        }
        
        .tb4-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        
        .tb4-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }
        .tb4-page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--tb4-text-primary);
        }
        .tb4-page-subtitle {
            font-size: 14px;
            color: var(--tb4-text-tertiary);
            margin-top: 4px;
        }
        
        .tb4-pages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        
        .tb4-page-card {
            background: var(--tb4-bg-primary);
            border: 1px solid var(--tb4-border-default);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s;
        }
        .tb4-page-card:hover {
            border-color: var(--tb4-primary-400);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        
        .tb4-page-card-preview {
            height: 160px;
            background: linear-gradient(135deg, var(--tb4-neutral-100), var(--tb4-neutral-200));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--tb4-text-tertiary);
            font-size: 48px;
        }
        
        .tb4-page-card-body {
            padding: 16px;
        }
        .tb4-page-card-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--tb4-text-primary);
        }
        .tb4-page-card-slug {
            font-size: 12px;
            color: var(--tb4-text-tertiary);
            margin-bottom: 12px;
        }
        .tb4-page-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .tb4-page-card-date {
            font-size: 11px;
            color: var(--tb4-text-muted);
        }
        
        .tb4-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .tb4-status-badge.draft {
            background: var(--tb4-warning-bg);
            color: var(--tb4-warning-text);
        }
        .tb4-status-badge.published {
            background: var(--tb4-success-bg);
            color: var(--tb4-success-text);
        }
        
        .tb4-page-card-actions {
            padding: 12px 16px;
            border-top: 1px solid var(--tb4-border-muted);
            display: flex;
            gap: 8px;
        }
        .tb4-page-card-btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid var(--tb4-border-default);
            background: var(--tb4-bg-secondary);
            color: var(--tb4-text-secondary);
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.15s;
        }
        .tb4-page-card-btn:hover {
            background: var(--tb4-bg-tertiary);
            border-color: var(--tb4-border-strong);
        }
        .tb4-page-card-btn.primary {
            background: var(--tb4-primary-600);
            color: white;
            border-color: var(--tb4-primary-600);
        }
        .tb4-page-card-btn.primary:hover {
            background: var(--tb4-primary-700);
        }
        
        .tb4-empty-state {
            text-align: center;
            padding: 80px 24px;
            background: var(--tb4-bg-primary);
            border: 2px dashed var(--tb4-border-default);
            border-radius: 16px;
        }
        .tb4-empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        .tb4-empty-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--tb4-text-primary);
        }
        .tb4-empty-text {
            font-size: 14px;
            color: var(--tb4-text-tertiary);
            margin-bottom: 24px;
        }
        
        /* Create page modal */
        .tb4-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .tb4-modal.active {
            display: flex;
        }
        .tb4-modal-dialog {
            background: var(--tb4-bg-primary);
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 24px 48px rgba(0,0,0,0.2);
        }
        .tb4-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--tb4-border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .tb4-modal-title {
            font-size: 18px;
            font-weight: 600;
        }
        .tb4-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--tb4-text-tertiary);
            padding: 4px;
        }
        .tb4-modal-close:hover {
            color: var(--tb4-text-primary);
        }
        .tb4-modal-body {
            padding: 24px;
        }
        .tb4-form-group {
            margin-bottom: 20px;
        }
        .tb4-form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--tb4-text-secondary);
        }
        .tb4-form-input {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            border: 1px solid var(--tb4-border-default);
            border-radius: 8px;
            background: var(--tb4-bg-secondary);
            color: var(--tb4-text-primary);
        }
        .tb4-form-input:focus {
            outline: none;
            border-color: var(--tb4-primary-500);
        }
        .tb4-modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--tb4-border-default);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="tb4-header">
        <div class="tb4-header-left">
            <a href="/admin" class="tb4-back-link">← Back to Admin</a>
            <div class="tb4-logo">
                <div class="tb4-logo-icon">TB</div>
                <span>Theme Builder 4.0</span>
            </div>
        </div>
        <button class="tb4-btn tb4-btn-primary" id="btnNewPage">
            <span>+</span> New Page
        </button>
    </header>
    
    <!-- Content -->
    <div class="tb4-container">
        <div class="tb4-page-header">
            <div>
                <h1 class="tb4-page-title">Your Pages</h1>
                <p class="tb4-page-subtitle"><?= count($pages) ?> pages total</p>
            </div>
        </div>
        
        <?php if (empty($pages)): ?>
        <div class="tb4-empty-state">
            <div class="tb4-empty-icon">📄</div>
            <h2 class="tb4-empty-title">No pages yet</h2>
            <p class="tb4-empty-text">Create your first page to get started with Theme Builder 4.0</p>
            <button class="tb4-btn tb4-btn-primary" onclick="document.getElementById('createModal').classList.add('active')">
                Create Your First Page
            </button>
        </div>
        <?php else: ?>
        <div class="tb4-pages-grid">
            <?php foreach ($pages as $page): ?>
            <div class="tb4-page-card">
                <div class="tb4-page-card-preview">📄</div>
                <div class="tb4-page-card-body">
                    <h3 class="tb4-page-card-title"><?= esc($page['title']) ?></h3>
                    <p class="tb4-page-card-slug">/<?= esc($page['slug']) ?></p>
                    <div class="tb4-page-card-meta">
                        <span class="tb4-status-badge <?= $page['status'] ?>"><?= $page['status'] ?></span>
                        <span class="tb4-page-card-date">Updated <?= date('M j, Y', strtotime($page['updated_at'])) ?></span>
                    </div>
                </div>
                <div class="tb4-page-card-actions">
                    <a href="/admin/tb4/edit/<?= (int)$page['id'] ?>" class="tb4-page-card-btn primary">Edit</a>
                    <a href="/<?= esc($page['slug']) ?>" target="_blank" class="tb4-page-card-btn">View</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Create Page Modal -->
    <div class="tb4-modal" id="createModal">
        <div class="tb4-modal-dialog">
            <div class="tb4-modal-header">
                <h2 class="tb4-modal-title">Create New Page</h2>
                <button class="tb4-modal-close" onclick="document.getElementById('createModal').classList.remove('active')">&times;</button>
            </div>
            <form method="POST" action="/admin/tb4-builder/create">
                <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">
                <div class="tb4-modal-body">
                    <div class="tb4-form-group">
                        <label class="tb4-form-label" for="pageTitle">Page Title</label>
                        <input type="text" class="tb4-form-input" id="pageTitle" name="title" placeholder="My New Page" required>
                    </div>
                    <div class="tb4-form-group">
                        <label class="tb4-form-label" for="pageSlug">URL Slug</label>
                        <input type="text" class="tb4-form-input" id="pageSlug" name="slug" placeholder="my-new-page">
                    </div>
                </div>
                <div class="tb4-modal-footer">
                    <button type="button" class="tb4-btn" onclick="document.getElementById('createModal').classList.remove('active')">Cancel</button>
                    <button type="submit" class="tb4-btn tb4-btn-primary">Create Page</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Open modal
        document.getElementById('btnNewPage').addEventListener('click', () => {
            document.getElementById('createModal').classList.add('active');
        });
        
        // Auto-generate slug from title
        document.getElementById('pageTitle').addEventListener('input', (e) => {
            const slug = e.target.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
            document.getElementById('pageSlug').value = slug;
        });
        
        // Close modal on backdrop click
        document.getElementById('createModal').addEventListener('click', (e) => {
            if (e.target.id === 'createModal') {
                e.target.classList.remove('active');
            }
        });
    </script>
</body>
</html>
