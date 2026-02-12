<?php
$title = 'Edit File';
ob_start();

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' bytes';
}
?>

<div style="display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem;">
    <div class="card">
        <div class="card-body" style="text-align: center;">
            <?php if ($file['is_image']): ?>
                <img src="<?= esc($file['url']) ?>" alt="<?= esc($file['alt_text'] ?? '') ?>" style="max-width: 100%; max-height: 250px; border-radius: 8px; margin-bottom: 1rem;">
            <?php else: ?>
                <div style="padding: 2rem; background: #f1f5f9; border-radius: 8px; margin-bottom: 1rem;">
                    <span style="font-size: 3rem;">📄</span>
                    <p style="margin: 0.5rem 0 0; color: var(--text-muted);"><?= esc(strtoupper(pathinfo($file['filename'], PATHINFO_EXTENSION))) ?></p>
                </div>
            <?php endif; ?>

            <p style="font-size: 0.875rem; word-break: break-all; margin: 0 0 0.5rem;"><?= esc($file['original_name']) ?></p>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
                <?= esc($file['mime_type']) ?><br>
                <?= formatBytes((int)$file['size']) ?>
            </p>

            <div style="margin-top: 1rem;">
                <a href="<?= esc($file['url']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%;">View Full Size</a>
            </div>

            <div style="margin-top: 0.5rem;">
                <input type="text" value="<?= esc($file['url']) ?>" readonly style="font-size: 0.75rem; text-align: center;" onclick="this.select();">
                <p class="form-hint">Click to select URL</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Edit File Details</h2>
            <a href="/admin/media" class="btn btn-secondary btn-sm">Back to Library</a>
        </div>
        <div class="card-body">
            <form method="post" action="/admin/media/<?= (int)$file['id'] ?>" style="max-width: 500px;">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="title">Title</label><span class="tip"><span class="tip-text">Display name of the media file.</span></span>
                    <input type="text" id="title" name="title" value="<?= esc($file['title'] ?? '') ?>" placeholder="File title">
                </div>

                <div class="form-group">
                    <label for="alt_text">Alt Text</label><span class="tip"><span class="tip-text">Describes the image for screen readers and SEO. Important for accessibility.</span></span>
                    <input type="text" id="alt_text" name="alt_text" value="<?= esc($file['alt_text'] ?? '') ?>" placeholder="Alternative text for accessibility">
                    <p class="form-hint">Describes the image for screen readers and SEO</p>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Optional description"><?= esc($file['description'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="/admin/media" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

            <hr style="margin: 2rem 0;">

            <form method="post" action="/admin/media/<?= (int)$file['id'] ?>/delete" onsubmit="return confirm('Permanently delete this file?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Delete File</button>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
