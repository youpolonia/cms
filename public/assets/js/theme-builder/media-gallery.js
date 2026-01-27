/**
 * Theme Builder 3.0 - Media Gallery JavaScript
 * 
 * Provides Media Gallery functionality for image selection.
 * Extends TB object with media gallery methods.
 * 
 * @package ThemeBuilder
 * @version 3.0
 */

// Extend TB object with Media Gallery methods
Object.assign(TB, {
    mediaGalleryCallback: null,
    selectedMediaUrl: null,

    openMediaGallery(callback) {
        this.mediaGalleryCallback = callback;
        this.selectedMediaUrl = null;
        document.getElementById('tb-media-select-btn').disabled = true;
        document.querySelectorAll('.tb-media-item').forEach(item => item.classList.remove('selected'));
        document.querySelectorAll('.tb-stock-item').forEach(item => item.classList.remove('selected'));
        document.getElementById('tb-media-modal').classList.add('active');
        document.querySelectorAll('.tb-media-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tb-media-tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector('.tb-media-tab[data-tab="upload"]').classList.add('active');
        document.getElementById('tb-media-tab-upload').classList.add('active');
    },

    closeMediaGallery() {
        document.getElementById('tb-media-modal').classList.remove('active');
    },

    selectMediaFromGallery() {
        if (!this.selectedMediaUrl) return;
        if (this.mediaGalleryCallback) {
            this.mediaGalleryCallback(this.selectedMediaUrl);
        }
        this.closeMediaGallery();
    },

    initMediaGalleryEvents() {
        document.querySelectorAll('.tb-media-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tb-media-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tb-media-tab-content').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('tb-media-tab-' + this.dataset.tab).classList.add('active');
            });
        });

        document.querySelectorAll('.tb-media-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.tb-media-item').forEach(i => i.classList.remove('selected'));
                document.querySelectorAll('.tb-stock-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                TB.selectedMediaUrl = this.dataset.url;
                document.getElementById('tb-media-select-btn').disabled = false;
            });
        });

        const uploadArea = document.getElementById('tb-upload-area');
        const uploadInput = document.getElementById('tb-media-upload');
        if (uploadArea) {
            uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
            uploadArea.addEventListener('dragleave', () => { uploadArea.classList.remove('dragover'); });
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                if (e.dataTransfer.files.length) TB.uploadMediaFile(e.dataTransfer.files[0]);
            });
        }
        if (uploadInput) {
            uploadInput.addEventListener('change', () => { if (uploadInput.files.length) TB.uploadMediaFile(uploadInput.files[0]); });
        }

        const stockInput = document.getElementById('tb-stock-search-input');
        if (stockInput) {
            stockInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); TB.searchStockPhotos(); }
            });
        }
    },

    uploadMediaFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        document.getElementById('tb-upload-progress').style.display = 'block';
        document.getElementById('tb-upload-bar').style.width = '30%';

        fetch('/admin/api/article-image-upload.php', { method: 'POST', credentials: 'same-origin', body: formData })
        .then(r => {
            document.getElementById('tb-upload-bar').style.width = '70%';
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            document.getElementById('tb-upload-bar').style.width = '100%';
            setTimeout(() => {
                document.getElementById('tb-upload-progress').style.display = 'none';
                document.getElementById('tb-upload-bar').style.width = '0%';
            }, 500);

            if (data.success && data.url) {
                const grid = document.getElementById('tb-media-grid');
                const newItem = document.createElement('div');
                newItem.className = 'tb-media-item selected';
                newItem.dataset.url = data.url;
                newItem.innerHTML = '<img src="' + data.url + '" alt=""><div class="tb-media-filename">' + (data.filename || 'uploaded') + '</div>';
                newItem.addEventListener('click', function() {
                    document.querySelectorAll('.tb-media-item').forEach(i => i.classList.remove('selected'));
                    this.classList.add('selected');
                    TB.selectedMediaUrl = this.dataset.url;
                    document.getElementById('tb-media-select-btn').disabled = false;
                });
                grid.insertBefore(newItem, grid.firstChild);
                
                document.querySelectorAll('.tb-media-item').forEach(i => i.classList.remove('selected'));
                newItem.classList.add('selected');
                TB.selectedMediaUrl = data.url;
                document.getElementById('tb-media-select-btn').disabled = false;
                
                document.querySelectorAll('.tb-media-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tb-media-tab-content').forEach(c => c.classList.remove('active'));
                document.querySelector('.tb-media-tab[data-tab="library"]').classList.add('active');
                document.getElementById('tb-media-tab-library').classList.add('active');
                
                if (this.showToast) this.showToast('Image uploaded successfully!', 'success');
            } else {
                if (this.showToast) this.showToast(data.error || 'Upload failed', 'error');
            }
        })
        .catch(err => {
            document.getElementById('tb-upload-progress').style.display = 'none';
            if (this.showToast) this.showToast('Upload error: ' + err.message, 'error');
        });
    },

    searchStockPhotos() {
        const query = document.getElementById('tb-stock-search-input').value.trim();
        if (!query) return;

        const resultsEl = document.getElementById('tb-stock-results');
        resultsEl.innerHTML = '<div class="tb-stock-loading"><div class="spinner"></div><p>Searching...</p></div>';

        fetch('/admin/api/pexels-search.php?query=' + encodeURIComponent(query))
        .then(r => r.json())
        .then(data => {
            if (data.photos && data.photos.length > 0) {
                let html = '<div class="tb-stock-grid">';
                data.photos.forEach(photo => {
                    html += '<div class="tb-stock-item" data-url="' + photo.src.large + '" onclick="TB.selectStockPhoto(this)">';
                    html += '<img src="' + photo.src.medium + '" alt="' + (photo.alt || '') + '" loading="lazy">';
                    html += '<div class="tb-stock-credit">📷 ' + photo.photographer + '</div>';
                    html += '</div>';
                });
                html += '</div>';
                resultsEl.innerHTML = html;
            } else {
                resultsEl.innerHTML = '<div class="tb-stock-loading"><p>No photos found. Try a different search.</p></div>';
            }
        })
        .catch(err => {
            resultsEl.innerHTML = '<div class="tb-stock-loading"><p>Error searching photos: ' + err.message + '</p></div>';
        });
    },

    selectStockPhoto(el) {
        document.querySelectorAll('.tb-media-item').forEach(i => i.classList.remove('selected'));
        document.querySelectorAll('.tb-stock-item').forEach(i => i.classList.remove('selected'));
        el.classList.add('selected');
        this.selectedMediaUrl = el.dataset.url;
        document.getElementById('tb-media-select-btn').disabled = false;
    },

    generateAiImage() {
        const prompt = document.getElementById('tb-ai-image-prompt').value.trim();
        if (!prompt) {
            if (this.showToast) this.showToast('Please describe the image you want to generate', 'warning');
            return;
        }

        const style = document.getElementById('tb-ai-image-style').value;
        const size = document.getElementById('tb-ai-image-size').value;
        const previewEl = document.getElementById('tb-ai-gen-preview');
        
        previewEl.innerHTML = '<div class="tb-ai-gen-status"><div class="spinner"></div><p>Generating image...</p><p style="font-size:0.75rem;color:var(--tb-text-muted)">This may take 10-30 seconds</p></div>';

        fetch('/admin/api/ai-image-generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ prompt, style, size, csrf_token: document.querySelector('meta[name="csrf-token"]')?.content || '' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.url) {
                previewEl.innerHTML = '<div class="tb-ai-gen-result"><img src="' + data.url + '" alt="Generated image"><button class="tb-btn tb-btn-primary" onclick="TB.useAiGeneratedImage(\'' + data.url + '\')">Use This Image</button></div>';
            } else {
                previewEl.innerHTML = '<div class="tb-ai-gen-status"><p style="color:var(--tb-error)">⚠️ ' + (data.error || 'Generation failed') + '</p></div>';
            }
        })
        .catch(err => {
            previewEl.innerHTML = '<div class="tb-ai-gen-status"><p style="color:var(--tb-error)">⚠️ Error: ' + err.message + '</p></div>';
        });
    },

    useAiGeneratedImage(url) {
        this.selectedMediaUrl = url;
        document.getElementById('tb-media-select-btn').disabled = false;
        this.selectMediaFromGallery();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (typeof TB !== 'undefined' && TB.initMediaGalleryEvents) {
        TB.initMediaGalleryEvents();
    }
});
