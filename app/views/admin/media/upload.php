<?php
$title = 'Upload File';
ob_start();
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Upload File</h2>
        <a href="/admin/media" class="btn btn-secondary btn-sm">Back to Library</a>
    </div>
    <div class="card-body">
        <form method="post" action="/admin/media/" enctype="multipart/form-data" style="max-width: 500px;">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="file">Select File *</label>
                <input type="file" id="file" name="file" required style="padding: 1rem; border: 2px dashed var(--border); border-radius: 8px; width: 100%; cursor: pointer;">
                <p class="form-hint">Max size: <?= number_format($maxFileSize / 1048576, 0) ?> MB</p>
            </div>

            <div id="preview" style="display: none; margin-bottom: 1rem;">
                <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Upload</button>
                <a href="/admin/media" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <h3 style="font-size: 0.875rem; margin-bottom: 0.5rem;">Allowed file types:</h3>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
                Images: JPG, PNG, GIF, WebP, SVG<br>
                Documents: PDF, DOC, DOCX, XLS, XLSX, TXT, CSV, ZIP
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');
    const previewImg = document.getElementById('previewImg');

    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
