<?php
/**
 * Galleries Management - Modern Dark Theme
 */
$title = 'Galleries';
ob_start();

// Stats
$totalGalleries = count($galleries);
$publicCount = count(array_filter($galleries, fn($g) => $g['is_public']));
$totalImages = array_sum(array_column($galleries, 'image_count'));
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">🖼️ Galleries</h1>
        <p class="page-description">Manage your photo galleries and collections</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/galleries/create" class="btn btn-primary">+ New Gallery</a>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon">🖼️</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalGalleries ?></div>
            <div class="stat-label">Total Galleries</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🌐</div>
        <div class="stat-info">
            <div class="stat-value"><?= $publicCount ?></div>
            <div class="stat-label">Public</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📷</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalImages ?></div>
            <div class="stat-label">Total Images</div>
        </div>
    </div>
</div>

<?php if (empty($galleries)): ?>
<div class="card">
    <div class="empty-state">
        <div class="empty-icon">🖼️</div>
        <h3>No galleries yet</h3>
        <p>Create your first gallery to organize your photos.</p>
        <a href="/admin/galleries/create" class="btn btn-primary">Create First Gallery</a>
    </div>
</div>
<?php else: ?>

<!-- Galleries Grid -->
<div class="galleries-grid">
    <?php foreach ($galleries as $gallery): ?>
    <div class="gallery-card">
        <div class="gallery-cover">
            <?php if (!empty($gallery['cover_image'])): ?>
                <img src="<?= esc($gallery['cover_image']) ?>" alt="<?= esc($gallery['name']) ?>">
            <?php else: ?>
                <div class="cover-placeholder">
                    <span>🖼️</span>
                    <small><?= (int)$gallery['image_count'] ?> images</small>
                </div>
            <?php endif; ?>
            <div class="gallery-overlay">
                <a href="/admin/galleries/<?= (int)$gallery['id'] ?>/images" class="overlay-btn primary">
                    📷 Manage Images
                </a>
            </div>
        </div>
        <div class="gallery-info">
            <div class="gallery-header">
                <h3 class="gallery-name"><?= esc($gallery['name']) ?></h3>
                <span class="gallery-status <?= $gallery['is_public'] ? 'public' : 'private' ?>">
                    <?= $gallery['is_public'] ? '🌐 Public' : '🔒 Private' ?>
                </span>
            </div>
            <code class="gallery-slug">/gallery/<?= esc($gallery['slug']) ?></code>
            <?php if (!empty($gallery['description'])): ?>
                <p class="gallery-desc"><?= esc($gallery['description']) ?></p>
            <?php endif; ?>
            <div class="gallery-meta">
                <span class="meta-item">📷 <?= (int)$gallery['image_count'] ?> image<?= $gallery['image_count'] != 1 ? 's' : '' ?></span>
                <span class="meta-item">📅 <?= date('M j, Y', strtotime($gallery['created_at'])) ?></span>
            </div>
        </div>
        <div class="gallery-actions">
            <a href="/admin/galleries/<?= (int)$gallery['id'] ?>/images" class="action-btn images" title="Manage Images">
                📷 Images
            </a>
            <a href="/admin/galleries/<?= (int)$gallery['id'] ?>/edit" class="action-btn edit" title="Edit">
                ✏️ Edit
            </a>
            <?php if ($gallery['is_public']): ?>
                <a href="/gallery/<?= esc($gallery['slug']) ?>" target="_blank" class="action-btn view" title="View">
                    🔗 View
                </a>
            <?php endif; ?>
            <form method="post" action="/admin/galleries/<?= (int)$gallery['id'] ?>/delete" 
                  onsubmit="return confirm('Delete gallery \'<?= esc(addslashes($gallery['name'])) ?>\' and all its images?');"
                  class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="action-btn delete" title="Delete">🗑️</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}
.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}
.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}
.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0;
}
.page-description {
    color: var(--text-muted);
    margin: 0.25rem 0 0 0;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    font-size: 1.5rem;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}
.stat-label {
    font-size: 0.8125rem;
    color: var(--text-muted);
}

/* Empty State */
.card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
.empty-state h3 {
    margin: 0 0 0.5rem 0;
}
.empty-state p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

/* Galleries Grid */
.galleries-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.gallery-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
}
.gallery-card:hover {
    border-color: var(--accent-color);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

/* Gallery Cover */
.gallery-cover {
    aspect-ratio: 16/10;
    background: var(--bg-primary);
    position: relative;
    overflow: hidden;
}
.gallery-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cover-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
}
.cover-placeholder span {
    font-size: 3rem;
}
.cover-placeholder small {
    color: var(--text-muted);
    font-size: 0.875rem;
}

/* Gallery Overlay */
.gallery-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
.gallery-card:hover .gallery-overlay {
    opacity: 1;
}
.overlay-btn {
    padding: 0.75rem 1.5rem;
    background: var(--accent-color);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: transform 0.2s;
}
.overlay-btn:hover {
    transform: scale(1.05);
}

/* Gallery Info */
.gallery-info {
    padding: 1.25rem;
}
.gallery-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 0.5rem;
}
.gallery-name {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-primary);
}
.gallery-status {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    white-space: nowrap;
}
.gallery-status.public {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
}
.gallery-status.private {
    background: rgba(107, 114, 128, 0.15);
    color: #9ca3af;
}
.gallery-slug {
    font-size: 0.75rem;
    color: var(--accent-color);
    background: rgba(59, 130, 246, 0.1);
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
}
.gallery-desc {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0.75rem 0 0 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.gallery-meta {
    display: flex;
    gap: 1rem;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
}
.meta-item {
    font-size: 0.8125rem;
    color: var(--text-muted);
}

/* Gallery Actions */
.gallery-actions {
    display: flex;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--border-color);
    background: var(--bg-primary);
}
.inline-form {
    display: inline;
}
.action-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.action-btn:hover {
    background: var(--bg-primary);
}
.action-btn.images {
    background: var(--accent-color);
    border-color: var(--accent-color);
    color: white;
}
.action-btn.images:hover {
    background: #2563eb;
}
.action-btn.edit:hover {
    border-color: #f59e0b;
    color: #f59e0b;
}
.action-btn.view:hover {
    border-color: #22c55e;
    color: #22c55e;
}
.action-btn.delete:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
    color: #ef4444;
}

/* Button */
.btn-primary {
    background: var(--accent-color);
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-primary:hover {
    background: #2563eb;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    .galleries-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
