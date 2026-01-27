<?php
$title = 'Media Library';
ob_start();

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return $bytes . ' bytes';
}
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<!-- Main Tabs Navigation -->
<div style="display: flex; gap: 4px; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border);">
    <button id="tabLibrary" onclick="switchMediaTab('library')" class="media-tab active">
        📁 My Library
    </button>
    <button id="tabStockVideos" onclick="switchMediaTab('stockVideos')" class="media-tab">
        🎬 Stock Videos
    </button>
    <button id="tabStockImages" onclick="switchMediaTab('stockImages')" class="media-tab">
        🖼️ Stock Images
    </button>
    <button id="tabAiImages" onclick="switchMediaTab('aiImages')" class="media-tab">
        🤖 AI Images
    </button>
</div>

<style>
.media-tab {
    padding: 12px 24px;
    border: none;
    background: var(--surface);
    color: var(--text-muted);
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px 8px 0 0;
    transition: all 0.2s;
}
.media-tab:hover {
    background: var(--border);
}
.media-tab.active {
    background: var(--primary);
    color: white;
}
.stock-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}
.stock-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
}
.stock-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.stock-preview {
    aspect-ratio: 16/9;
    background: #1e293b;
    overflow: hidden;
}
.stock-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.stock-actions {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.stock-search-box {
    display: flex;
    gap: 12px;
    margin-bottom: 1rem;
}
.stock-search-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--input-bg);
    color: var(--text);
    font-size: 14px;
}
.stock-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.stock-tag {
    padding: 6px 12px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
}
.stock-tag:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--border);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<!-- Stock Videos Panel -->
<div id="panelStockVideos" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🎬 Stock Videos from Pexels</h2>
        </div>
        <div class="card-body">
            <div class="stock-search-box">
                <input type="text" id="stockVideoSearch" class="stock-search-input" placeholder="Search free stock videos...">
                <button onclick="searchStockVideos()" class="btn btn-primary">🔍 Search</button>
            </div>
            <div class="stock-tags">
                <span style="color: var(--text-muted); font-size: 13px; padding: 6px 0;">Popular:</span>
                <button class="stock-tag" onclick="searchStockVideos('nature')">Nature</button>
                <button class="stock-tag" onclick="searchStockVideos('business')">Business</button>
                <button class="stock-tag" onclick="searchStockVideos('technology')">Technology</button>
                <button class="stock-tag" onclick="searchStockVideos('city')">City</button>
                <button class="stock-tag" onclick="searchStockVideos('ocean')">Ocean</button>
                <button class="stock-tag" onclick="searchStockVideos('abstract')">Abstract</button>
                <button class="stock-tag" onclick="searchStockVideos('office')">Office</button>
            </div>
            <div id="stockVideoResults" class="stock-grid">
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--text-muted);">
                    <div style="font-size: 64px; margin-bottom: 16px;">🎬</div>
                    <p style="font-size: 16px;">Search for free stock videos from Pexels</p>
                    <p style="font-size: 13px; margin-top: 8px;">Videos are free to use, no attribution required</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Images Panel -->
<div id="panelStockImages" style="display: none;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🖼️ Stock Images from Pexels</h2>
        </div>
        <div class="card-body">
            <div class="stock-search-box">
                <input type="text" id="stockImageSearch" class="stock-search-input" placeholder="Search free stock images...">
                <button onclick="searchStockImages()" class="btn btn-primary">🔍 Search</button>
            </div>
            <div class="stock-tags">
                <span style="color: var(--text-muted); font-size: 13px; padding: 6px 0;">Popular:</span>
                <button class="stock-tag" onclick="searchStockImages('office')">Office</button>
                <button class="stock-tag" onclick="searchStockImages('nature')">Nature</button>
                <button class="stock-tag" onclick="searchStockImages('people')">People</button>
                <button class="stock-tag" onclick="searchStockImages('technology')">Technology</button>
                <button class="stock-tag" onclick="searchStockImages('food')">Food</button>
                <button class="stock-tag" onclick="searchStockImages('architecture')">Architecture</button>
                <button class="stock-tag" onclick="searchStockImages('business')">Business</button>
            </div>
            <div id="stockImageResults" class="stock-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--text-muted);">
                    <div style="font-size: 64px; margin-bottom: 16px;">🖼️</div>
                    <p style="font-size: 16px;">Search for free stock images from Pexels</p>
                    <p style="font-size: 13px; margin-top: 8px;">Images are free to use, no attribution required</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Images Panel -->
<div id="panelAiImages" style="display: none;">
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 60px 40px;">
            <div style="font-size: 80px; margin-bottom: 24px;">🤖</div>
            <h2 style="margin-bottom: 16px; font-size: 24px;">AI Image Generator</h2>
            <p style="color: var(--text-muted); margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto;">
                Generate unique images using OpenAI DALL-E. Create photorealistic images, illustrations, 3D renders, and more from text descriptions.
            </p>
            <a href="/admin/ai-images.php" class="btn btn-primary" style="padding: 16px 48px; font-size: 16px;">
                🎨 Open AI Image Generator
            </a>
            <p style="color: var(--text-muted); font-size: 13px; margin-top: 24px;">
                Generated images will automatically appear in your Media Library
            </p>
        </div>
    </div>
</div>

<!-- Library Panel (original content) -->
<div id="panelLibrary">
<div class="card">
    <div class="card-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <h2 class="card-title">Media Library</h2>
            <div style="display: flex; gap: 0.5rem;">
                <a href="/admin/media" class="btn btn-sm <?= !$currentType ? 'btn-primary' : 'btn-secondary' ?>">All (<?= $stats['total'] ?? 0 ?>)</a>
                <a href="/admin/media?type=images" class="btn btn-sm <?= $currentType === 'images' ? 'btn-primary' : 'btn-secondary' ?>">Images (<?= $stats['images'] ?? 0 ?>)</a>
                <a href="/admin/media?type=documents" class="btn btn-sm <?= $currentType === 'documents' ? 'btn-primary' : 'btn-secondary' ?>">Documents (<?= $stats['documents'] ?? 0 ?>)</a>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <form method="get" action="/admin/media" style="display: flex; gap: 0.25rem;">
                <?php if ($currentType): ?><input type="hidden" name="type" value="<?= esc($currentType) ?>"><?php endif; ?>
                <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Search..." style="width: 150px;">
                <button type="submit" class="btn btn-secondary btn-sm">Search</button>
            </form>
            <a href="/admin/media/upload" class="btn btn-primary btn-sm">+ Upload</a>
        </div>
    </div>

    <?php if (empty($files)): ?>
        <div class="card-body">
            <p style="color: var(--text-muted);">No files found. <a href="/admin/media/upload">Upload your first file</a>.</p>
        </div>
    <?php else: ?>
        <form method="post" action="/admin/media/bulk-delete" id="mediaForm">
            <?= csrf_field() ?>
            <div style="padding: 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="checkbox" id="selectAll">
                    <label for="selectAll" style="font-size: 0.875rem; color: var(--text-muted);">Select all</label>
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected files?');">Delete Selected</button>
                </div>
                <span style="font-size: 0.875rem; color: var(--text-muted);">Total: <?= formatBytes((int)($stats['total_size'] ?? 0)) ?></span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; padding: 1rem;">
                <?php foreach ($files as $file): ?>
                    <div style="border: 1px solid var(--border); border-radius: 8px; overflow: hidden; position: relative;">
                        <input type="checkbox" name="ids[]" value="<?= (int)$file['id'] ?>" class="file-checkbox" style="position: absolute; top: 8px; left: 8px; z-index: 10;">

                        <div style="aspect-ratio: 1; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php if ($file['is_image'] && $file['exists']): ?>
                                <img src="<?= esc($file['url']) ?>" alt="<?= esc($file['alt_text'] ?? '') ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="text-align: center; padding: 1rem;">
                                    <span style="font-size: 2rem;">📄</span>
                                    <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0.5rem 0 0;"><?= esc(strtoupper(pathinfo($file['filename'], PATHINFO_EXTENSION))) ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if (!$file['exists']): ?>
                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(239,68,68,0.8); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">MISSING</div>
                            <?php endif; ?>
                        </div>

                        <div style="padding: 0.5rem;">
                            <p style="font-size: 0.75rem; margin: 0 0 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= esc($file['original_name']) ?>">
                                <?= esc($file['original_name']) ?>
                            </p>
                            <p style="font-size: 0.7rem; color: var(--text-muted); margin: 0 0 0.5rem;">
                                <?= formatBytes((int)$file['size']) ?>
                            </p>
                            <div style="display: flex; gap: 0.25rem;">
                                <a href="/admin/media/<?= (int)$file['id'] ?>/edit" class="btn btn-secondary btn-sm" style="flex: 1; text-align: center; padding: 0.25rem;">Edit</a>
                                <form method="post" action="/admin/media/<?= (int)$file['id'] ?>/delete" onsubmit="return confirm('Delete?');" style="flex: 1;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; padding: 0.25rem;">🗑 Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>

        <?php if ($totalPages > 1): ?>
            <div style="padding: 1rem; display: flex; justify-content: center; gap: 0.5rem; border-top: 1px solid var(--border);">
                <?php if ($page > 1): ?>
                    <a href="/admin/media?page=<?= $page - 1 ?><?= $currentType ? '&type=' . esc($currentType) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-secondary btn-sm">Prev</a>
                <?php endif; ?>
                <span style="padding: 0.375rem 0.75rem;">Page <?= $page ?> of <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="/admin/media?page=<?= $page + 1 ?><?= $currentType ? '&type=' . esc($currentType) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="btn btn-secondary btn-sm">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</div><!-- end panelLibrary -->

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.file-checkbox').forEach(cb => cb.checked = this.checked);
});

// Tab switching
function switchMediaTab(tab) {
    document.querySelectorAll('.media-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1))?.classList.add('active');
    
    document.getElementById('panelLibrary').style.display = tab === 'library' ? 'block' : 'none';
    document.getElementById('panelStockVideos').style.display = tab === 'stockVideos' ? 'block' : 'none';
    document.getElementById('panelStockImages').style.display = tab === 'stockImages' ? 'block' : 'none';
    document.getElementById('panelAiImages').style.display = tab === 'aiImages' ? 'block' : 'none';
}

// Stock Videos Search
async function searchStockVideos(query) {
    query = query || document.getElementById('stockVideoSearch').value;
    if (!query) { alert('Please enter a search term'); return; }
    
    document.getElementById('stockVideoSearch').value = query;
    const results = document.getElementById('stockVideoResults');
    results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px;"><div class="spinner"></div><p style="margin-top: 16px;">Searching...</p></div>';
    
    try {
        const response = await fetch(`/api/stock-videos.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.videos && data.videos.length > 0) {
            results.innerHTML = data.videos.map(video => `
                <div class="stock-card">
                    <div class="stock-preview">
                        <img src="${video.preview}" alt="Video preview" loading="lazy">
                        ${video.duration ? `<div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">${video.duration}</div>` : ''}
                    </div>
                    <div class="stock-actions">
                        <button onclick="downloadStock('${encodeURIComponent(video.url)}', 'video', this)" class="btn btn-primary btn-sm" style="width: 100%;">
                            ⬇️ Download to Library
                        </button>
                        <button onclick="copyUrl('${video.url}')" class="btn btn-secondary btn-sm" style="width: 100%;">
                            📋 Copy URL
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">No videos found. Try a different search term.</div>';
        }
    } catch (error) {
        results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--danger);">Error searching videos. Please check API settings.</div>';
    }
}

// Stock Images Search
async function searchStockImages(query) {
    query = query || document.getElementById('stockImageSearch').value;
    if (!query) { alert('Please enter a search term'); return; }
    
    document.getElementById('stockImageSearch').value = query;
    const results = document.getElementById('stockImageResults');
    results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px;"><div class="spinner"></div><p style="margin-top: 16px;">Searching...</p></div>';
    
    try {
        const response = await fetch(`/api/stock-images.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.images && data.images.length > 0) {
            results.innerHTML = data.images.map(img => `
                <div class="stock-card">
                    <div class="stock-preview" style="aspect-ratio: 1;">
                        <img src="${img.preview}" alt="${img.alt || 'Stock image'}" loading="lazy">
                    </div>
                    <div class="stock-actions">
                        <button onclick="downloadStock('${encodeURIComponent(img.url)}', 'image', this)" class="btn btn-primary btn-sm" style="width: 100%;">
                            ⬇️ Download to Library
                        </button>
                        <button onclick="copyUrl('${img.url}')" class="btn btn-secondary btn-sm" style="width: 100%;">
                            📋 Copy URL
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">No images found. Try a different search term.</div>';
        }
    } catch (error) {
        results.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--danger);">Error searching images. Please check API settings.</div>';
    }
}

// Download to library
async function downloadStock(encodedUrl, type, btn) {
    const url = decodeURIComponent(encodedUrl);
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ Downloading...';
    btn.disabled = true;
    
    try {
        const response = await fetch('/api/download-stock-media.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url: url, type: type })
        });
        const data = await response.json();
        
        if (data.success) {
            btn.innerHTML = '✅ Downloaded!';
            btn.style.background = 'var(--success)';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        } else {
            throw new Error(data.error || 'Download failed');
        }
    } catch (error) {
        btn.innerHTML = '❌ Failed';
        btn.style.background = 'var(--danger)';
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            btn.disabled = false;
        }, 2000);
        alert('Download failed: ' + error.message);
    }
}

// Copy URL
function copyUrl(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target;
        const original = btn.innerHTML;
        btn.innerHTML = '✅ Copied!';
        setTimeout(() => btn.innerHTML = original, 1500);
    });
}

// Enter key support
document.getElementById('stockVideoSearch')?.addEventListener('keypress', e => { if (e.key === 'Enter') searchStockVideos(); });
document.getElementById('stockImageSearch')?.addEventListener('keypress', e => { if (e.key === 'Enter') searchStockImages(); });
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
