<?php
/**
 * Admin-editable image placeholder for theme sections.
 * 
 * When admin is logged in and there's no image set, shows a clickable
 * upload zone instead of a plain placeholder. Uploads go directly
 * to /api/theme-studio/upload and auto-save to theme_customizations.
 * 
 * Usage in section templates:
 *   <?php cms_admin_image_placeholder('about.image', 'fas fa-utensils'); ?>
 * 
 * The first param is "section.field" matching the data-ts-bg attribute.
 * The second param is the fallback icon class for non-admin visitors.
 */

if (!function_exists('cms_admin_image_placeholder')) {
    function cms_admin_image_placeholder(string $sectionField, string $iconClass = 'fas fa-image'): void {
        $isAdmin = function_exists('cms_is_admin_logged_in') && cms_is_admin_logged_in();
        
        if (!$isAdmin) {
            // Regular visitors see the icon placeholder
            echo '<div class="img-placeholder"><i class="' . htmlspecialchars($iconClass) . '"></i></div>';
            return;
        }
        
        // Admins see an upload zone
        $parts = explode('.', $sectionField, 2);
        $section = $parts[0] ?? '';
        $field = $parts[1] ?? '';
        $uid = 'aip-' . md5($sectionField);
        $themeSlug = function_exists('get_active_theme') ? get_active_theme() : '';
        $csrf = $_SESSION['csrf_token'] ?? '';
        
        echo <<<HTML
<div class="cms-admin-upload-zone" id="{$uid}" onclick="document.getElementById('{$uid}-input').click()" title="Click to upload image">
    <input type="file" id="{$uid}-input" accept="image/*" style="display:none"
           onchange="cmsUploadSectionImage(this, '{$section}', '{$field}', '{$uid}', '{$themeSlug}', '{$csrf}')">
    <div class="cms-aup-icon"><i class="fas fa-cloud-upload-alt"></i></div>
    <div class="cms-aup-text">Click to add image</div>
    <div class="cms-aup-hint">or drag & drop</div>
    <div class="cms-aup-progress" id="{$uid}-progress" style="display:none">
        <div class="cms-aup-spinner"></div>
        <span>Uploading...</span>
    </div>
</div>
HTML;
    }
}

if (!function_exists('cms_admin_image_placeholder_assets')) {
    /**
     * Outputs CSS + JS for the admin upload zones. Call once per page, in footer.
     */
    function cms_admin_image_placeholder_assets(): void {
        if (!function_exists('cms_is_admin_logged_in') || !cms_is_admin_logged_in()) return;
        
        echo <<<'ASSETS'
<style>
.cms-admin-upload-zone {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    cursor: pointer;
    border: 2px dashed rgba(255,255,255,0.2);
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    background: rgba(255,255,255,0.03);
}
.cms-admin-upload-zone:hover {
    border-color: rgba(212,165,116,0.6);
    background: rgba(212,165,116,0.08);
}
.cms-admin-upload-zone.dragover {
    border-color: rgba(212,165,116,0.8);
    background: rgba(212,165,116,0.12);
    transform: scale(1.01);
}
.cms-aup-icon {
    font-size: 2.5rem;
    color: rgba(255,255,255,0.35);
    margin-bottom: 12px;
    transition: color 0.3s;
}
.cms-admin-upload-zone:hover .cms-aup-icon {
    color: rgba(212,165,116,0.7);
}
.cms-aup-text {
    font-size: 14px; font-weight: 600;
    color: rgba(255,255,255,0.5);
    letter-spacing: 0.5px;
}
.cms-aup-hint {
    font-size: 11px;
    color: rgba(255,255,255,0.25);
    margin-top: 4px;
}
.cms-aup-progress {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: rgba(0,0,0,0.7);
    border-radius: 12px;
    gap: 8px;
    color: rgba(255,255,255,0.7);
    font-size: 13px;
}
.cms-aup-spinner {
    width: 28px; height: 28px;
    border: 3px solid rgba(255,255,255,0.15);
    border-top-color: rgba(212,165,116,0.8);
    border-radius: 50%;
    animation: cmsSpin 0.7s linear infinite;
}
@keyframes cmsSpin { to { transform: rotate(360deg); } }
</style>
<script>
function cmsUploadSectionImage(input, section, field, uid, theme, csrf) {
    const file = input.files[0];
    if (!file) return;
    
    const zone = document.getElementById(uid);
    const progress = document.getElementById(uid + '-progress');
    if (progress) progress.style.display = 'flex';
    
    const fd = new FormData();
    fd.append('file', file);
    fd.append('section', section);
    fd.append('field', field);
    
    const url = '/api/theme-studio/upload' + (theme ? '?theme=' + encodeURIComponent(theme) : '');
    
    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf },
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok && data.url) {
            // Find the parent element with data-ts-bg and set the background
            const parent = zone.closest('[data-ts-bg]');
            if (parent) {
                parent.style.background = 'url(' + data.url + ') center/cover no-repeat';
            }
            zone.remove();
        } else {
            alert('Upload failed: ' + (data.error || 'Unknown error'));
            if (progress) progress.style.display = 'none';
        }
    })
    .catch(err => {
        alert('Upload error: ' + err.message);
        if (progress) progress.style.display = 'none';
    });
    
    // Reset input
    input.value = '';
}

// Drag & drop support
document.querySelectorAll('.cms-admin-upload-zone').forEach(zone => {
    ['dragenter','dragover'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.add('dragover'); }));
    ['dragleave','drop'].forEach(e => zone.addEventListener(e, ev => { ev.preventDefault(); zone.classList.remove('dragover'); }));
    zone.addEventListener('drop', ev => {
        const file = ev.dataTransfer.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        const input = zone.querySelector('input[type="file"]');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    });
});
</script>
ASSETS;
    }
}
