/**
 * Media Picker Component
 * Provides a modal interface for selecting media files
 */

(function(window) {
    'use strict';

    var MediaPicker = {
        modal: null,
        callback: null,
        currentType: 'all',
        searchQuery: '',

        /**
         * Initialize the media picker
         */
        init: function() {
            this.createModal();
            this.bindEvents();
        },

        /**
         * Create the modal HTML
         */
        createModal: function() {
            if (document.getElementById('media-picker-modal')) {
                return;
            }

            var modalHtml = '' +
                '<div id="media-picker-modal" class="media-picker-modal" style="display:none;">' +
                '  <div class="media-picker-overlay"></div>' +
                '  <div class="media-picker-dialog">' +
                '    <div class="media-picker-header">' +
                '      <h3>Select Media</h3>' +
                '      <button type="button" class="media-picker-close">&times;</button>' +
                '    </div>' +
                '    <div class="media-picker-toolbar">' +
                '      <div class="media-picker-filters">' +
                '        <select id="media-picker-type">' +
                '          <option value="all">All Files</option>' +
                '          <option value="image">Images</option>' +
                '          <option value="video">Videos</option>' +
                '          <option value="audio">Audio</option>' +
                '          <option value="document">Documents</option>' +
                '        </select>' +
                '        <input type="text" id="media-picker-search" placeholder="Search...">' +
                '      </div>' +
                '      <a href="/admin/media/upload.php" target="_blank" class="media-picker-upload-btn">Upload New</a>' +
                '    </div>' +
                '    <div class="media-picker-body">' +
                '      <div id="media-picker-grid" class="media-picker-grid"></div>' +
                '      <div id="media-picker-loading" class="media-picker-loading" style="display:none;">Loading...</div>' +
                '      <div id="media-picker-empty" class="media-picker-empty" style="display:none;">No media files found.</div>' +
                '    </div>' +
                '    <div class="media-picker-footer">' +
                '      <button type="button" class="media-picker-cancel-btn">Cancel</button>' +
                '    </div>' +
                '  </div>' +
                '</div>';

            var container = document.createElement('div');
            container.innerHTML = modalHtml;
            document.body.appendChild(container.firstChild);

            this.modal = document.getElementById('media-picker-modal');
            this.addStyles();
        },

        /**
         * Add CSS styles
         */
        addStyles: function() {
            if (document.getElementById('media-picker-styles')) {
                return;
            }

            var css = '' +
                '.media-picker-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 10000; }' +
                '.media-picker-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }' +
                '.media-picker-dialog { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; max-width: 900px; max-height: 80vh; background: #fff; border-radius: 8px; display: flex; flex-direction: column; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }' +
                '.media-picker-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid #ddd; }' +
                '.media-picker-header h3 { margin: 0; font-size: 18px; }' +
                '.media-picker-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; }' +
                '.media-picker-close:hover { color: #333; }' +
                '.media-picker-toolbar { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: #f8f9fa; border-bottom: 1px solid #ddd; }' +
                '.media-picker-filters { display: flex; gap: 10px; }' +
                '.media-picker-filters select, .media-picker-filters input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; }' +
                '.media-picker-upload-btn { padding: 8px 16px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; }' +
                '.media-picker-upload-btn:hover { background: #0056b3; }' +
                '.media-picker-body { flex: 1; overflow-y: auto; padding: 20px; min-height: 300px; }' +
                '.media-picker-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; }' +
                '.media-picker-item { border: 2px solid #ddd; border-radius: 4px; padding: 5px; cursor: pointer; transition: border-color 0.2s; }' +
                '.media-picker-item:hover { border-color: #007bff; }' +
                '.media-picker-item.selected { border-color: #007bff; background: #e7f3ff; }' +
                '.media-picker-thumb { width: 100%; height: 80px; object-fit: cover; border-radius: 2px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; }' +
                '.media-picker-thumb img { max-width: 100%; max-height: 80px; object-fit: cover; }' +
                '.media-picker-name { font-size: 11px; color: #666; margin-top: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }' +
                '.media-picker-loading, .media-picker-empty { text-align: center; color: #666; padding: 40px; }' +
                '.media-picker-footer { display: flex; justify-content: flex-end; padding: 15px 20px; border-top: 1px solid #ddd; }' +
                '.media-picker-cancel-btn { padding: 8px 16px; background: #6c757d; color: #fff; border: none; border-radius: 4px; cursor: pointer; }' +
                '.media-picker-cancel-btn:hover { background: #545b62; }' +
                '.media-picker-file-icon { font-size: 32px; color: #999; }';

            var style = document.createElement('style');
            style.id = 'media-picker-styles';
            style.textContent = css;
            document.head.appendChild(style);
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;

            // Close button
            this.modal.querySelector('.media-picker-close').addEventListener('click', function() {
                self.close();
            });

            // Cancel button
            this.modal.querySelector('.media-picker-cancel-btn').addEventListener('click', function() {
                self.close();
            });

            // Overlay click
            this.modal.querySelector('.media-picker-overlay').addEventListener('click', function() {
                self.close();
            });

            // Type filter
            document.getElementById('media-picker-type').addEventListener('change', function() {
                self.currentType = this.value;
                self.loadMedia();
            });

            // Search
            var searchInput = document.getElementById('media-picker-search');
            var searchTimeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    self.searchQuery = searchInput.value;
                    self.loadMedia();
                }, 300);
            });

            // Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && self.modal.style.display !== 'none') {
                    self.close();
                }
            });
        },

        /**
         * Open the media picker
         * @param {Function} callback - Called with selected media item
         * @param {Object} options - Optional settings
         */
        open: function(callback, options) {
            options = options || {};
            this.callback = callback;
            this.currentType = options.type || 'all';
            this.searchQuery = '';

            document.getElementById('media-picker-type').value = this.currentType;
            document.getElementById('media-picker-search').value = '';

            this.modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            this.loadMedia();
        },

        /**
         * Close the media picker
         */
        close: function() {
            this.modal.style.display = 'none';
            document.body.style.overflow = '';
            this.callback = null;
        },

        /**
         * Load media items via AJAX
         */
        loadMedia: function() {
            var self = this;
            var grid = document.getElementById('media-picker-grid');
            var loading = document.getElementById('media-picker-loading');
            var empty = document.getElementById('media-picker-empty');

            grid.innerHTML = '';
            loading.style.display = 'block';
            empty.style.display = 'none';

            var url = '/admin/media/picker.php?action=list&type=' + encodeURIComponent(this.currentType);
            if (this.searchQuery) {
                url += '&search=' + encodeURIComponent(this.searchQuery);
            }

            fetch(url, {
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                loading.style.display = 'none';

                if (!data.success || !data.items || data.items.length === 0) {
                    empty.style.display = 'block';
                    return;
                }

                data.items.forEach(function(item) {
                    var itemEl = self.createMediaItem(item);
                    grid.appendChild(itemEl);
                });
            })
            .catch(function(err) {
                console.error('Media picker error:', err);
                loading.style.display = 'none';
                empty.textContent = 'Error loading media files.';
                empty.style.display = 'block';
            });
        },

        /**
         * Create a media item element
         */
        createMediaItem: function(item) {
            var self = this;
            var el = document.createElement('div');
            el.className = 'media-picker-item';
            el.dataset.id = item.id;

            var thumbHtml = '';
            if (item.is_image && item.thumb) {
                thumbHtml = '<div class="media-picker-thumb"><img src="' + this.escapeHtml(item.thumb) + '" alt=""></div>';
            } else {
                var icon = this.getFileIcon(item.mime);
                thumbHtml = '<div class="media-picker-thumb"><span class="media-picker-file-icon">' + icon + '</span></div>';
            }

            el.innerHTML = thumbHtml + '<div class="media-picker-name" title="' + this.escapeHtml(item.basename) + '">' + this.escapeHtml(item.basename) + '</div>';

            el.addEventListener('click', function() {
                // Remove selection from other items
                var selected = document.querySelectorAll('.media-picker-item.selected');
                selected.forEach(function(s) { s.classList.remove('selected'); });

                el.classList.add('selected');

                // Call callback and close
                if (self.callback) {
                    self.callback({
                        id: item.id,
                        path: item.path,
                        basename: item.basename,
                        alt: item.alt,
                        mime: item.mime
                    });
                }
                self.close();
            });

            return el;
        },

        /**
         * Get icon for file type
         */
        getFileIcon: function(mime) {
            if (mime.indexOf('video/') === 0) return '🎬';
            if (mime.indexOf('audio/') === 0) return '🎵';
            if (mime.indexOf('application/pdf') === 0) return '📕';
            if (mime.indexOf('application/') === 0) return '📄';
            return '📁';
        },

        /**
         * Escape HTML special characters
         */
        escapeHtml: function(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            MediaPicker.init();
        });
    } else {
        MediaPicker.init();
    }

    // Expose globally
    window.MediaPicker = MediaPicker;

})(window);
