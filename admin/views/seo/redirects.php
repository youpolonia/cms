<?php
/**
 * SEO Redirects Management View
 */

$redirects = $data['items'] ?? [];
$total = $data['total'] ?? 0;
$page = $data['page'] ?? 1;
$totalPages = $data['total_pages'] ?? 1;
$success = $data['success'] ?? '';
$errors = $data['errors'] ?? [];

function esc($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
?>
<div class="seo-redirects">
    <div class="page-header">
        <h1>URL Redirects</h1>
        <p class="muted">Manage 301/302 redirects for changed or moved URLs.</p>
        <button type="button" class="btn primary" onclick="showAddModal()">Add Redirect</button>
    </div>

    <?php if (!empty($success)): ?>
    <div class="alert success"><?php echo esc($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert error">
        <ul>
            <?php foreach ($errors as $error): ?>
            <li><?php echo esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Source URL</th>
                    <th>Target URL</th>
                    <th>Type</th>
                    <th>Hits</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($redirects)): ?>
                <tr>
                    <td colspan="6" class="empty-state">No redirects configured yet.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($redirects as $redirect): ?>
                <tr>
                    <td><code><?php echo esc($redirect['source_url']); ?></code></td>
                    <td><code><?php echo esc($redirect['target_url']); ?></code></td>
                    <td><?php echo (int) $redirect['redirect_type']; ?></td>
                    <td><?php echo number_format($redirect['hit_count'] ?? 0); ?></td>
                    <td>
                        <span class="status-badge <?php echo $redirect['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $redirect['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn small" onclick="editRedirect(<?php echo (int) $redirect['id']; ?>)">Edit</button>
                        <button type="button" class="btn small danger" onclick="deleteRedirect(<?php echo (int) $redirect['id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?php echo $p; ?>" class="page-link <?php echo $p === $page ? 'active' : ''; ?>">
                <?php echo $p; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="redirect-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Add Redirect</h2>
            <button type="button" class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <form id="redirect-form" method="post" action="">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" id="redirect-id" value="">

            <div class="form-row">
                <label for="source_url">Source URL *</label>
                <input type="text" id="source_url" name="source_url" class="form-control" required
                       placeholder="/old-page">
                <small class="muted">The old URL path (relative to site root).</small>
            </div>

            <div class="form-row">
                <label for="target_url">Target URL *</label>
                <input type="text" id="target_url" name="target_url" class="form-control" required
                       placeholder="/new-page or https://example.com/page">
                <small class="muted">Where to redirect to (relative path or full URL).</small>
            </div>

            <div class="form-row">
                <label for="redirect_type">Redirect Type</label>
                <select id="redirect_type" name="redirect_type" class="form-control">
                    <option value="301">301 - Permanent Redirect</option>
                    <option value="302">302 - Temporary Redirect</option>
                    <option value="307">307 - Temporary (strict)</option>
                    <option value="308">308 - Permanent (strict)</option>
                </select>
            </div>

            <div class="form-row">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active" class="form-control">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-row">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="2"
                          placeholder="Optional notes about this redirect"></textarea>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn primary">Save Redirect</button>
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modal-title').textContent = 'Add Redirect';
    document.getElementById('redirect-id').value = '';
    document.getElementById('redirect-form').reset();
    document.getElementById('redirect-modal').style.display = 'flex';
}

function editRedirect(id) {
    fetch('api/seo-actions.php?action=get_redirect&id=' + id)
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            document.getElementById('modal-title').textContent = 'Edit Redirect';
            document.getElementById('redirect-id').value = data.id;
            document.getElementById('source_url').value = data.source_url || '';
            document.getElementById('target_url').value = data.target_url || '';
            document.getElementById('redirect_type').value = data.redirect_type || '301';
            document.getElementById('is_active').value = data.is_active ? '1' : '0';
            document.getElementById('notes').value = data.notes || '';
            document.getElementById('redirect-modal').style.display = 'flex';
        } else {
            alert('Redirect not found');
        }
    })
    .catch(err => alert('Error loading redirect: ' + err.message));
}

function closeModal() {
    document.getElementById('redirect-modal').style.display = 'none';
}

function deleteRedirect(id) {
    if (!confirm('Delete this redirect?')) return;

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    var csrfInput = document.querySelector('input[name="csrf_token"]').cloneNode();
    form.appendChild(csrfInput);

    var idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'delete_id';
    idInput.value = id;
    form.appendChild(idInput);

    document.body.appendChild(form);
    form.submit();
}

// Close modal on outside click
document.getElementById('redirect-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<style>
/* Dark theme overrides for redirects view */
.status-badge{display:inline-flex;padding:3px 8px;border-radius:5px;font-size:11px;font-weight:600}
.status-badge.active{background:rgba(166,227,161,.15);color:var(--success)}
.status-badge.inactive{background:rgba(243,139,168,.15);color:var(--danger)}
.empty-state{text-align:center;color:var(--muted);padding:40px}
.modal{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:1000}
.modal-content{background:var(--bg2);border:1px solid var(--border);border-radius:16px;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.modal-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border)}
.modal-header h2{margin:0;font-size:18px;font-weight:600}
.close-btn{background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);padding:0;line-height:1}
.close-btn:hover{color:var(--text)}
.modal-content form{padding:24px}
.modal-footer{display:flex;gap:10px;margin-top:16px}
small.muted{display:block;font-size:12px;color:var(--muted);margin-top:4px}
</style>
