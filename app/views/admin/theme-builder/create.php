<?php
/**
 * Theme Builder 3.0 - Create New Page
 */
$title = 'Create New Page';
ob_start();
?>

<div class="card" style="max-width: 560px;">
    <div class="card-header">
        <h3 class="card-title">📄 Page Details</h3>
    </div>
    
    <form id="create-page-form">
        <?= csrf_field() ?>
        
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" id="title" name="title" class="form-input" required placeholder="Enter page title..." autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label">URL Slug</label>
                <div class="slug-input-wrap">
                    <span class="slug-prefix">/</span>
                    <input type="text" id="slug" name="slug" class="form-input" required placeholder="page-url-slug" pattern="[a-z0-9-]+">
                </div>
                <p class="form-hint">Only lowercase letters, numbers, and hyphens</p>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
        </div>
        
        <div id="error-message" class="alert-error" style="display: none;"></div>
        
        <div class="card-footer">
            <a href="/admin/theme-builder" class="btn btn-secondary">← Back</a>
            <button type="submit" class="btn btn-primary">Create & Open Builder →</button>
        </div>
    </form>
</div>

<style>
.slug-input-wrap {
    display: flex;
    align-items: center;
}
.slug-prefix {
    padding: 10px 12px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-right: none;
    border-radius: var(--radius) 0 0 var(--radius);
    color: var(--text-muted);
}
.slug-input-wrap .form-input {
    border-radius: 0 var(--radius) var(--radius) 0;
}
.form-hint {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 6px;
}
.card-footer {
    display: flex;
    justify-content: space-between;
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    background: var(--bg-secondary);
}
.alert-error {
    margin: 0 20px 16px;
    padding: 12px 16px;
    background: var(--danger-bg);
    border: 1px solid rgba(243, 139, 168, 0.3);
    border-radius: var(--radius);
    color: var(--danger);
    font-size: 14px;
}
</style>

<script>
document.getElementById('title').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    document.getElementById('slug').value = slug;
});

document.getElementById('create-page-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errorDiv = document.getElementById('error-message');
    errorDiv.style.display = 'none';
    
    const csrfToken = document.querySelector('input[name="csrf_token"]').value;
    const data = {
        page_id: 0,
        title: document.getElementById('title').value,
        slug: document.getElementById('slug').value,
        status: document.getElementById('status').value,
        content: {sections: []},
        csrf_token: csrfToken
    };
    
    try {
        const response = await fetch('/admin/theme-builder/save', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
            body: JSON.stringify({...data, _token: csrfToken})
        });
        
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch(parseErr) {
            errorDiv.textContent = 'Server error: ' + text.substring(0, 200);
            errorDiv.style.display = 'block';
            return;
        }
        
        if (result.success && result.page_id) {
            window.location.href = '/admin/theme-builder/' + result.page_id + '/edit';
        } else {
            errorDiv.textContent = result.error || 'Failed to create page';
            errorDiv.style.display = 'block';
        }
    } catch (err) {
        errorDiv.textContent = 'Error: ' + err.message;
        errorDiv.style.display = 'block';
    }
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
