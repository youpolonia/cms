/**
 * JTB Media Gallery JavaScript
 *
 * Provides Media Gallery functionality for image selection.
 * Extends JTB object with media gallery methods.
 *
 * Based on Theme Builder 3.0 media-gallery.js
 * Adapted for Jessie Theme Builder with 'jtb-' prefix and 'JTB' namespace
 *
 * @package JessieThemeBuilder
 * @version 1.0
 */

(function() {
    'use strict';

    // Ensure JTB namespace exists
    window.JTB = window.JTB || {};

    // Media Gallery state
    JTB.mediaGalleryCallback = null;
    JTB.selectedMediaUrl = null;
    JTB.selectedMediaUrls = []; // For multi-select
    JTB.mediaGalleryMultiSelect = false; // Multi-select mode flag

    /**
     * Open Media Gallery modal
     * @param {Function} callback - Called with selected URL(s) when image is selected
     * @param {Object} options - Options: { multiSelect: boolean }
     */
    JTB.openMediaGallery = function(callback, options = {}) {
        this.mediaGalleryCallback = callback;
        this.selectedMediaUrl = null;
        this.selectedMediaUrls = [];
        this.mediaGalleryMultiSelect = options.multiSelect || false;

        const modal = document.getElementById('jtb-media-modal');
        const selectBtn = document.getElementById('jtb-media-select-btn');

        if (!modal) {
            console.error('JTB Media Gallery: Modal element not found');
            return;
        }

        // Reset selection state
        if (selectBtn) {
            selectBtn.disabled = true;
            selectBtn.textContent = this.mediaGalleryMultiSelect ? 'Select Images' : 'Select Image';
        }
        document.querySelectorAll('.jtb-media-item').forEach(item => item.classList.remove('selected'));
        document.querySelectorAll('.jtb-stock-item').forEach(item => item.classList.remove('selected'));

        // Toggle multi-select class on modal
        modal.classList.toggle('multi-select', this.mediaGalleryMultiSelect);

        // Show modal
        modal.classList.add('active');

        // Reset to Library tab for multi-select (more useful), Upload for single
        document.querySelectorAll('.jtb-media-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.jtb-media-tab-content').forEach(c => c.classList.remove('active'));

        const defaultTab = this.mediaGalleryMultiSelect ? 'library' : 'upload';
        const activeTab = document.querySelector(`.jtb-media-tab[data-tab="${defaultTab}"]`);
        const activeContent = document.getElementById(`jtb-media-tab-${defaultTab}`);
        if (activeTab) activeTab.classList.add('active');
        if (activeContent) activeContent.classList.add('active');
    };

    /**
     * Close Media Gallery modal
     */
    JTB.closeMediaGallery = function() {
        const modal = document.getElementById('jtb-media-modal');
        if (modal) modal.classList.remove('active');
    };

    /**
     * Select image from gallery and close modal
     */
    JTB.selectMediaFromGallery = async function() {
        // Check if we need to save AI generated image first
        if (this.aiGeneratedTemp && this.aiGeneratedTemp.url === this.selectedMediaUrl) {
            // Save AI image first, then continue with selection
            await this.saveAiImageToGallery();
        }

        if (this.mediaGalleryMultiSelect) {
            // Multi-select mode - return array of URLs
            if (this.selectedMediaUrls.length === 0) return;

            if (this.mediaGalleryCallback) {
                this.mediaGalleryCallback(this.selectedMediaUrls);
            }
        } else {
            // Single select mode - return single URL
            if (!this.selectedMediaUrl) return;

            if (this.mediaGalleryCallback) {
                this.mediaGalleryCallback(this.selectedMediaUrl);
            }
        }

        this.closeMediaGallery();
    };

    /**
     * Update selection count display
     */
    JTB.updateSelectionCount = function() {
        const selectBtn = document.getElementById('jtb-media-select-btn');
        if (!selectBtn) return;

        if (this.mediaGalleryMultiSelect) {
            const count = this.selectedMediaUrls.length;
            selectBtn.disabled = count === 0;
            selectBtn.textContent = count > 0 ? `Select ${count} Image${count > 1 ? 's' : ''}` : 'Select Images';
        } else {
            selectBtn.disabled = !this.selectedMediaUrl;
            selectBtn.textContent = 'Select Image';
        }
    };

    /**
     * Handle media item click - supports both single and multi-select
     * @param {HTMLElement} item - Clicked item
     * @param {Event} e - Click event
     */
    JTB.handleMediaItemClick = function(item, e) {
        const url = item.dataset.url;

        if (this.mediaGalleryMultiSelect) {
            // Multi-select mode - toggle selection
            if (item.classList.contains('selected')) {
                // Deselect
                item.classList.remove('selected');
                const idx = this.selectedMediaUrls.indexOf(url);
                if (idx > -1) {
                    this.selectedMediaUrls.splice(idx, 1);
                }
            } else {
                // Select
                item.classList.add('selected');
                if (!this.selectedMediaUrls.includes(url)) {
                    this.selectedMediaUrls.push(url);
                }
            }
        } else {
            // Single select mode - deselect all others
            document.querySelectorAll('.jtb-media-item').forEach(i => i.classList.remove('selected'));
            document.querySelectorAll('.jtb-stock-item').forEach(i => i.classList.remove('selected'));

            item.classList.add('selected');
            this.selectedMediaUrl = url;
        }

        this.updateSelectionCount();
    };

    /**
     * Initialize Media Gallery event listeners
     * Called automatically on DOMContentLoaded
     */
    JTB.initMediaGalleryEvents = function() {
        // Tab switching
        document.querySelectorAll('.jtb-media-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.dataset.tab;

                // Update tab buttons
                document.querySelectorAll('.jtb-media-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Update tab content
                document.querySelectorAll('.jtb-media-tab-content').forEach(c => c.classList.remove('active'));
                const content = document.getElementById('jtb-media-tab-' + tabName);
                if (content) content.classList.add('active');
            });
        });

        // Library item selection
        document.querySelectorAll('.jtb-media-item').forEach(item => {
            item.addEventListener('click', function(e) {
                JTB.handleMediaItemClick(this, e);
            });
        });

        // Upload area drag & drop
        const uploadArea = document.getElementById('jtb-upload-area');
        const uploadInput = document.getElementById('jtb-media-upload');

        if (uploadArea) {
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    JTB.uploadMediaFile(e.dataTransfer.files[0]);
                }
            });

            // Click to open file browser
            uploadArea.addEventListener('click', (e) => {
                if (e.target.tagName !== 'LABEL' && e.target.tagName !== 'INPUT') {
                    uploadInput?.click();
                }
            });
        }

        if (uploadInput) {
            uploadInput.addEventListener('change', () => {
                if (uploadInput.files.length) {
                    JTB.uploadMediaFile(uploadInput.files[0]);
                }
            });
        }

        // Stock photo search on Enter
        const stockInput = document.getElementById('jtb-stock-search-input');
        if (stockInput) {
            stockInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    JTB.searchStockPhotos();
                }
            });
        }

        // Close on overlay click
        const modal = document.getElementById('jtb-media-modal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    JTB.closeMediaGallery();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal?.classList.contains('active')) {
                JTB.closeMediaGallery();
            }
        });
    };

    /**
     * Upload a media file
     * @param {File} file - File to upload
     */
    JTB.uploadMediaFile = function(file) {
        const config = window.JTB_MEDIA_CONFIG || {};
        const csrfToken = config.csrfToken || JTB.config?.csrfToken || '';
        const uploadEndpoint = config.uploadEndpoint || '/api/jtb/upload';

        const formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', csrfToken);

        // Show progress
        const progressEl = document.getElementById('jtb-upload-progress');
        const progressBar = document.getElementById('jtb-upload-bar');
        if (progressEl) progressEl.style.display = 'block';
        if (progressBar) progressBar.style.width = '30%';

        fetch(uploadEndpoint, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(r => {
            if (progressBar) progressBar.style.width = '70%';
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (progressBar) progressBar.style.width = '100%';

            setTimeout(() => {
                if (progressEl) progressEl.style.display = 'none';
                if (progressBar) progressBar.style.width = '0%';
            }, 500);

            if (data.success && data.url) {
                // Add to library grid
                const grid = document.getElementById('jtb-media-grid');
                if (grid) {
                    const newItem = document.createElement('div');
                    newItem.className = 'jtb-media-item';
                    newItem.dataset.url = data.url;
                    newItem.innerHTML = `
                        <img src="${data.url}" alt="">
                        <div class="jtb-media-filename">${data.filename || 'uploaded'}</div>
                        <div class="jtb-media-checkbox"></div>
                    `;

                    // Add click handler using the unified handler
                    newItem.addEventListener('click', function(e) {
                        JTB.handleMediaItemClick(this, e);
                    });

                    // Remove empty message if exists
                    const emptyMsg = grid.querySelector('.jtb-media-empty');
                    if (emptyMsg) emptyMsg.remove();

                    grid.insertBefore(newItem, grid.firstChild);
                }

                // Select the uploaded image
                if (JTB.mediaGalleryMultiSelect) {
                    const newItem = document.querySelector('.jtb-media-item[data-url="' + data.url + '"]');
                    if (newItem) {
                        newItem.classList.add('selected');
                        JTB.selectedMediaUrls.push(data.url);
                    }
                } else {
                    document.querySelectorAll('.jtb-media-item').forEach(i => i.classList.remove('selected'));
                    const newItem = document.querySelector('.jtb-media-item[data-url="' + data.url + '"]');
                    if (newItem) newItem.classList.add('selected');
                    JTB.selectedMediaUrl = data.url;
                }
                JTB.updateSelectionCount();

                // Switch to Library tab
                document.querySelectorAll('.jtb-media-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.jtb-media-tab-content').forEach(c => c.classList.remove('active'));
                document.querySelector('.jtb-media-tab[data-tab="library"]')?.classList.add('active');
                document.getElementById('jtb-media-tab-library')?.classList.add('active');

                JTB.showNotification?.('Image uploaded successfully!', 'success');
            } else {
                JTB.showNotification?.(data.error || 'Upload failed', 'error');
            }
        })
        .catch(err => {
            if (progressEl) progressEl.style.display = 'none';
            JTB.showNotification?.('Upload error: ' + err.message, 'error');
        });
    };

    /**
     * Search stock photos from Pexels via server-side proxy
     * Uses /admin/api/pexels-search.php to bypass CSP restrictions
     */
    JTB.searchStockPhotos = function() {
        const query = document.getElementById('jtb-stock-search-input')?.value.trim();
        if (!query) return;

        const resultsEl = document.getElementById('jtb-stock-results');
        if (!resultsEl) return;

        resultsEl.innerHTML = `
            <div class="jtb-stock-loading">
                <div class="jtb-spinner"></div>
                <p>Searching...</p>
            </div>
        `;

        // Use server-side proxy to bypass CSP
        fetch('/admin/api/pexels-search.php?query=' + encodeURIComponent(query), {
            credentials: 'same-origin'
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            // Check for proxy error
            if (data.error) {
                throw new Error(data.error);
            }

            if (data.photos && data.photos.length > 0) {
                let html = '<div class="jtb-stock-grid">';
                data.photos.forEach(photo => {
                    const url = photo.src.large;
                    const thumb = photo.src.medium;
                    const alt = photo.alt || '';
                    const photographer = photo.photographer || 'Unknown';

                    html += `
                        <div class="jtb-stock-item" data-url="${url}">
                            <img src="${thumb}" alt="${alt}" loading="lazy">
                            <div class="jtb-stock-credit">by ${photographer}</div>
                        </div>
                    `;
                });
                html += '</div>';
                resultsEl.innerHTML = html;

                // Add click handlers
                resultsEl.querySelectorAll('.jtb-stock-item').forEach(item => {
                    item.addEventListener('click', function() {
                        JTB.selectStockPhoto(this);
                    });
                });
            } else {
                resultsEl.innerHTML = `
                    <div class="jtb-stock-placeholder">
                        <p>No photos found. Try a different search.</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            resultsEl.innerHTML = `
                <div class="jtb-stock-placeholder">
                    <p style="color: #ef4444;">Error: ${err.message}</p>
                </div>
            `;
        });
    };

    /**
     * Select a stock photo
     * @param {HTMLElement} el - Stock photo element
     */
    JTB.selectStockPhoto = function(el) {
        const url = el.dataset.url;

        if (this.mediaGalleryMultiSelect) {
            // Multi-select mode - toggle selection
            if (el.classList.contains('selected')) {
                el.classList.remove('selected');
                const idx = this.selectedMediaUrls.indexOf(url);
                if (idx > -1) {
                    this.selectedMediaUrls.splice(idx, 1);
                }
            } else {
                el.classList.add('selected');
                if (!this.selectedMediaUrls.includes(url)) {
                    this.selectedMediaUrls.push(url);
                }
            }
        } else {
            // Single select mode
            document.querySelectorAll('.jtb-media-item').forEach(i => i.classList.remove('selected'));
            document.querySelectorAll('.jtb-stock-item').forEach(i => i.classList.remove('selected'));
            el.classList.add('selected');
            this.selectedMediaUrl = url;
        }

        this.updateSelectionCount();
    };

    /**
     * Generate AI image using DALL-E
     * Uses /admin/api/ai-image-generate.php endpoint
     */
    JTB.generateAiImage = function() {
        const prompt = document.getElementById('jtb-ai-image-prompt')?.value.trim();
        if (!prompt) {
            JTB.showNotification?.('Please describe the image you want to generate', 'warning');
            return;
        }

        const config = window.JTB_MEDIA_CONFIG || {};
        const csrfToken = config.csrfToken
            || JTB.config?.csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.content
            || '';

        const style = document.getElementById('jtb-ai-image-style')?.value || 'photorealistic';
        const size = document.getElementById('jtb-ai-image-size')?.value || '1024x1024';
        const previewEl = document.getElementById('jtb-ai-preview');
        const generateBtn = document.querySelector('.jtb-ai-generate-btn');

        if (!previewEl) {
            console.error('JTB AI Generate: Preview element not found');
            return;
        }

        // Disable button during generation
        if (generateBtn) {
            generateBtn.disabled = true;
            generateBtn.textContent = 'Generating...';
        }

        // Show loading state with animated progress bar
        previewEl.style.display = 'block';
        previewEl.innerHTML = `
            <div class="jtb-ai-generating">
                <div class="jtb-ai-progress-container">
                    <div class="jtb-ai-progress-bar">
                        <div class="jtb-ai-progress-fill"></div>
                    </div>
                    <div class="jtb-ai-progress-text">
                        <span class="jtb-ai-progress-status">Connecting to AI...</span>
                        <span class="jtb-ai-progress-percent">0%</span>
                    </div>
                </div>
                <p class="jtb-ai-progress-hint">Generating "${prompt.substring(0, 30)}${prompt.length > 30 ? '...' : ''}"</p>
            </div>
        `;

        // Animate progress bar
        const progressFill = previewEl.querySelector('.jtb-ai-progress-fill');
        const progressStatus = previewEl.querySelector('.jtb-ai-progress-status');
        const progressPercent = previewEl.querySelector('.jtb-ai-progress-percent');

        let progress = 0;
        const progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 8;
                if (progress > 90) progress = 90;

                if (progressFill) progressFill.style.width = progress + '%';
                if (progressPercent) progressPercent.textContent = Math.round(progress) + '%';

                if (progressStatus) {
                    if (progress < 20) {
                        progressStatus.textContent = 'Connecting to AI...';
                    } else if (progress < 50) {
                        progressStatus.textContent = 'Processing prompt...';
                    } else if (progress < 80) {
                        progressStatus.textContent = 'Generating image...';
                    } else {
                        progressStatus.textContent = 'Finalizing...';
                    }
                }
            }
        }, 500);

        fetch('/admin/api/ai-image-generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                prompt: prompt,
                style: style,
                size: size,
                csrf_token: csrfToken
            })
        })
        .then(r => {
            if (!r.ok) {
                return r.json().then(data => {
                    throw new Error(data.error || 'HTTP ' + r.status);
                });
            }
            return r.json();
        })
        .then(data => {
            clearInterval(progressInterval);

            // Re-enable button
            if (generateBtn) {
                generateBtn.disabled = false;
                generateBtn.textContent = 'Generate';
            }

            // Get image URL - backend returns either 'url' or 'path'
            const imageUrl = data.url || data.path;

            if ((data.success || data.ok) && imageUrl) {
                // Complete progress bar
                if (progressFill) progressFill.style.width = '100%';
                if (progressPercent) progressPercent.textContent = '100%';
                if (progressStatus) progressStatus.textContent = 'Complete!';

                // Store temp data for later save
                JTB.aiGeneratedTemp = {
                    url: imageUrl,
                    tempFile: data.tempFile || '',
                    prompt: data.prompt || prompt
                };

                // Auto-select the generated image so "Select Image" button works
                JTB.selectedMediaUrl = imageUrl;
                JTB.updateSelectionCount();

                // Short delay then show image
                setTimeout(() => {
                    // Create elements manually to ensure event listeners work
                    const resultDiv = document.createElement('div');
                    resultDiv.className = 'jtb-ai-result';

                    const img = document.createElement('img');
                    img.src = imageUrl;
                    img.alt = 'Generated image';

                    const actionsDiv = document.createElement('div');
                    actionsDiv.className = 'jtb-ai-result-actions';

                    const useBtn = document.createElement('button');
                    useBtn.type = 'button';
                    useBtn.className = 'jtb-btn jtb-btn-primary';
                    useBtn.id = 'jtb-ai-use-btn';
                    useBtn.textContent = 'Use This Image';
                    useBtn.addEventListener('click', function() {
                        JTB.saveAndUseAiImage();
                    });

                    const regenBtn = document.createElement('button');
                    regenBtn.type = 'button';
                    regenBtn.className = 'jtb-btn';
                    regenBtn.id = 'jtb-ai-regenerate-btn';
                    regenBtn.textContent = 'Regenerate';
                    regenBtn.addEventListener('click', function() {
                        JTB.generateAiImage();
                    });

                    actionsDiv.appendChild(useBtn);
                    actionsDiv.appendChild(regenBtn);
                    resultDiv.appendChild(img);
                    resultDiv.appendChild(actionsDiv);

                    previewEl.innerHTML = '';
                    previewEl.appendChild(resultDiv);

                    // DO NOT add to library grid here - only after user clicks "Use This Image"

                    JTB.showNotification?.('Image generated! Click "Use This Image" to save and select it.', 'success');
                }, 300);
            } else {
                // Show error
                previewEl.innerHTML = `
                    <div class="jtb-ai-error">
                        <div class="jtb-ai-error-icon">⚠️</div>
                        <p class="jtb-ai-error-text">${data.error || 'Generation failed. Please try again.'}</p>
                        <button type="button" class="jtb-btn" onclick="JTB.generateAiImage()">Try Again</button>
                    </div>
                `;
            }
        })
        .catch(err => {
            clearInterval(progressInterval);

            // Re-enable button
            if (generateBtn) {
                generateBtn.disabled = false;
                generateBtn.textContent = 'Generate';
            }

            previewEl.innerHTML = `
                <div class="jtb-ai-error">
                    <div class="jtb-ai-error-icon">⚠️</div>
                    <p class="jtb-ai-error-text">${err.message}</p>
                    <button type="button" class="jtb-btn" onclick="JTB.generateAiImage()">Try Again</button>
                </div>
            `;
        });
    };

    /**
     * Add image to Library grid dynamically
     * @param {string} url - Image URL
     * @param {string} filename - Display filename
     */
    JTB.addImageToLibraryGrid = function(url, filename) {
        const grid = document.getElementById('jtb-media-grid');
        if (!grid) return;

        // Remove empty message if exists
        const emptyMsg = grid.querySelector('.jtb-media-empty');
        if (emptyMsg) emptyMsg.remove();

        // Create new item
        const newItem = document.createElement('div');
        newItem.className = 'jtb-media-item';
        newItem.dataset.url = url;
        newItem.innerHTML = `
            <img src="${url}" alt="">
            <div class="jtb-media-filename">${filename || url.split('/').pop()}</div>
            <div class="jtb-media-checkbox"></div>
        `;

        // Add click handler using unified handler
        newItem.addEventListener('click', function(e) {
            JTB.handleMediaItemClick(this, e);
        });

        // Insert at beginning of grid
        grid.insertBefore(newItem, grid.firstChild);
    };

    /**
     * Save AI image to gallery without closing modal
     * Called by selectMediaFromGallery when AI image is selected
     */
    JTB.saveAiImageToGallery = async function() {
        if (!JTB.aiGeneratedTemp || !JTB.aiGeneratedTemp.url) {
            return; // Nothing to save
        }

        const config = window.JTB_MEDIA_CONFIG || {};
        const csrfToken = config.csrfToken || JTB.config?.csrfToken || '';

        try {
            const response = await fetch('/admin/api/ai-image-save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    url: JTB.aiGeneratedTemp.url,
                    tempFile: JTB.aiGeneratedTemp.tempFile,
                    prompt: JTB.aiGeneratedTemp.prompt,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();

            if (data.success && data.url) {
                // Update selectedMediaUrl to the saved URL
                JTB.selectedMediaUrl = data.url;
                // Add to library grid
                JTB.addImageToLibraryGrid(data.url, 'AI Generated');
                // Clear temp data
                JTB.aiGeneratedTemp = null;
            }
        } catch (error) {
            console.error('Failed to save AI image:', error);
        }
    };

    /**
     * Save AI generated image to gallery and then use it
     * Called when user clicks "Use This Image" after AI generation
     */
    JTB.saveAndUseAiImage = async function() {
        if (!JTB.aiGeneratedTemp || !JTB.aiGeneratedTemp.url) {
            JTB.showNotification?.('No generated image to save', 'error');
            return;
        }

        const config = window.JTB_MEDIA_CONFIG || {};
        const csrfToken = config.csrfToken || JTB.config?.csrfToken || '';

        // Show saving state
        const useBtn = document.getElementById('jtb-ai-use-btn');
        if (useBtn) {
            useBtn.disabled = true;
            useBtn.textContent = 'Saving...';
        }

        try {
            // Save image to gallery via API
            const response = await fetch('/admin/api/ai-image-save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    url: JTB.aiGeneratedTemp.url,
                    tempFile: JTB.aiGeneratedTemp.tempFile,
                    prompt: JTB.aiGeneratedTemp.prompt,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();

            if (data.success && data.url) {
                // Add to library grid
                JTB.addImageToLibraryGrid(data.url, 'AI Generated');

                // Now use the saved image
                JTB.useAiGeneratedImage(data.url);

                JTB.showNotification?.('Image saved and selected!', 'success');
            } else {
                throw new Error(data.error || 'Failed to save image');
            }
        } catch (error) {
            console.error('Failed to save AI image:', error);
            JTB.showNotification?.('Failed to save image: ' + error.message, 'error');

            // Re-enable button
            if (useBtn) {
                useBtn.disabled = false;
                useBtn.textContent = 'Use This Image';
            }
        }
    };

    /**
     * Use AI generated image (after it's been saved)
     * @param {string} url - Image URL
     */
    JTB.useAiGeneratedImage = function(url) {
        if (JTB.mediaGalleryMultiSelect) {
            if (!JTB.selectedMediaUrls.includes(url)) {
                JTB.selectedMediaUrls.push(url);
            }
        } else {
            JTB.selectedMediaUrl = url;
        }
        JTB.updateSelectionCount();
        JTB.selectMediaFromGallery();
    };

    // Initialize on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            JTB.initMediaGalleryEvents();
        });
    } else {
        // DOM already loaded
        JTB.initMediaGalleryEvents();
    }

})();
