class VideoBlock {
    constructor(element) {
        this.element = element;
        this.blockId = element.dataset.blockId;
        this.urlInput = element.querySelector('.video-url-input');
        this.autoplayCheckbox = element.querySelector('.autoplay-checkbox');
        this.loopCheckbox = element.querySelector('.loop-checkbox');
        this.controlsCheckbox = element.querySelector('.controls-checkbox');
        this.previewContainer = element.querySelector('.current-video-preview');
        
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.urlInput.addEventListener('input', () => this.handleUrlChange());
        this.autoplayCheckbox.addEventListener('change', () => this.updatePreview());
        this.loopCheckbox.addEventListener('change', () => this.updatePreview());
        this.controlsCheckbox.addEventListener('change', () => this.updatePreview());
    }

    handleUrlChange() {
        const url = this.urlInput.value.trim();
        if (!url) {
            this.previewContainer.innerHTML = '';
            return;
        }

        // Validate URL format
        if (!this.isValidVideoUrl(url)) {
            this.previewContainer.innerHTML = '<div class="video-error">Invalid video URL</div>';
            return;
        }

        this.updatePreview();
    }

    isValidVideoUrl(url) {
        // YouTube patterns
        const youtubePatterns = [
            /youtube\.com\/watch\?v=([^&]+)/,
            /youtu\.be\/([^?]+)/,
            /youtube\.com\/embed\/([^\/]+)/,
            /youtube\.com\/v\/([^\/]+)/
        ];

        // Vimeo patterns
        const vimeoPatterns = [
            /vimeo\.com\/([0-9]+)/,
            /vimeo\.com\/channels\/[^\/]+\/([0-9]+)/,
            /vimeo\.com\/groups\/[^\/]+\/videos\/([0-9]+)/,
            /vimeo\.com\/album\/[^\/]+\/video\/([0-9]+)/
        ];

        return youtubePatterns.some(p => p.test(url)) || 
               vimeoPatterns.some(p => p.test(url));
    }

    updatePreview() {
        const url = this.urlInput.value.trim();
        if (!url) return;

        const data = {
            videoUrl: url,
            autoplay: this.autoplayCheckbox.checked,
            loop: this.loopCheckbox.checked,
            controls: this.controlsCheckbox.checked
        };

        // Send update to server for validation and preview generation
        fetch(`/api/blocks/${this.blockId}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.previewHtml) {
                this.previewContainer.innerHTML = data.previewHtml;
            } else {
                this.previewContainer.innerHTML = '<div class="video-error">' + 
                    (data.error || 'Error loading preview') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.previewContainer.innerHTML = '<div class="video-error">Preview update failed</div>';
        });
    }
}

// Initialize all video block editors on the page
document.querySelectorAll('.video-block-edit').forEach(element => {
    new VideoBlock(element.closest('.block-editor'));
});