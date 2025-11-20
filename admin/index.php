<?php
if (!defined('DEV_MODE')) { require_once __DIR__ . '/../config.php'; }
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>
<main class="container">
  <h1>Dashboard</h1>
  <div class="card-grid">
    <div class="card">
      <h2>Content</h2>
      <p class="muted">Manage articles, pages and media.</p>
      <div class="card-actions">
        <a class="btn primary" href="/admin/articles.php">Articles</a>
        <a class="btn" href="/admin/pages.php">Pages</a>
        <a class="btn" href="/admin/galleries.php">Galleries</a>
        <a class="btn" href="/admin/media.php">Media</a>
      </div>
    </div>
    <div class="card">
      <h2>Design</h2>
      <p class="muted">Themes and builders.</p>
      <div class="card-actions">
        <a class="btn primary" href="/admin/themes.php">Themes</a>
        <a class="btn" href="/admin/theme-builder.php">Builder</a>
        <a class="btn" href="/admin/ai-theme-builder.php">Builder (AI)</a>
      </div>
    </div>
    <div class="card">
      <h2>AI Tools</h2>
      <p class="muted">Generate and optimize content.</p>
      <div class="card-actions">
        <a class="btn primary" href="/admin/ai-content-creator.php">AI Content</a>
        <a class="btn" href="/admin/seo.php">SEO</a>
      </div>
    </div>
    <div class="card">
      <h2>System</h2>
      <p class="muted">Scheduler, maintenance and backup.</p>
      <div class="card-actions">
        <a class="btn primary" href="/admin/scheduler.php">Scheduler</a>
        <a class="btn" href="/admin/maintenance.php">Maintenance</a>
        <a class="btn" href="/admin/backup.php">Backup</a>
        <a class="btn" href="/admin/logs/">Logs</a>
      </div>
    </div>
    <div class="card">
      <h2>Navigation</h2>
      <p class="muted">Menus, widgets and URLs.</p>
      <div class="card-actions">
        <a class="btn primary" href="/admin/menus.php">Menus</a>
        <a class="btn" href="/admin/widgets.php">Widgets</a>
        <a class="btn" href="/admin/urls.php">URLs</a>
      </div>
    </div>
    <div class="card">
      <h2>Users & Modules</h2>
      <p class="muted">Manage access and extensions.</p>
      <div class="card-actions">
        <a class="btn primary" href="/admin/users.php">Users</a>
        <a class="btn" href="/admin/modules.php">Modules</a>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php';
