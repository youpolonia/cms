class LivePreview {
    constructor(editor) {
        this.editor = editor;
        this.previewFrame = null;
        this.cache = new Map();
        this.debounceTimer = null;
        
        this.initPreviewFrame();
        this.setupEventListeners();
    }

    initPreviewFrame() {
        const previewContainer = document.createElement('div');
        previewContainer.className = 'live-preview-container';
        previewContainer.innerHTML = `
            <div class="preview-toolbar">
                <button class="toggle-preview">Toggle Preview</button>
                <button class="refresh-preview">Refresh</button>
            </div>
            <iframe class="live-preview-frame" src="about:blank"></iframe>
        `;
        document.body.appendChild(previewContainer);
        this.previewFrame = previewContainer.querySelector('.live-preview-frame');
    }

    setupEventListeners() {
        // Listen for editor changes
        this.editor.blocks.forEach(block => {
            this.watchBlockChanges(block);
        });

        // Toggle preview visibility
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('toggle-preview')) {
                this.previewFrame.parentElement.classList.toggle('visible');
            }
            if (e.target.classList.contains('refresh-preview')) {
                this.updatePreview();
            }
        });
    }

    watchBlockChanges(block) {
        // TODO: Implement block change detection
    }

    updatePreview() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        this.debounceTimer = setTimeout(() => {
            const content = this.getPreviewContent();
            const cacheKey = this.generateCacheKey(content);
            
            if (this.cache.has(cacheKey)) {
                this.renderFromCache(cacheKey);
            } else {
                this.renderNewPreview(content, cacheKey);
            }
        }, 300);
    }

    getPreviewContent() {
        return this.editor.blocks.map(block => block.data.content).join('');
    }

    generateCacheKey(content) {
        return btoa(encodeURIComponent(content)).substring(0, 32);
    }

    renderFromCache(cacheKey) {
        const cached = this.cache.get(cacheKey);
        this.previewFrame.contentDocument.write(cached);
    }

    renderNewPreview(content, cacheKey) {
        const previewDoc = this.previewFrame.contentDocument;
        previewDoc.open();
        previewDoc.write(`
            <!DOCTYPE html>
            <html>
                <head>
                    <link rel="stylesheet" href="/admin/editor/preview.css">
                </head>
                <body>${content}</body>
            </html>
        `);
        previewDoc.close();
        this.cache.set(cacheKey, previewDoc.documentElement.outerHTML);
    }
}

export default LivePreview;