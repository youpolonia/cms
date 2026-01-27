class ImageBlock {
    constructor(element) {
        this.element = element;
        this.imagePath = '';
        this.altText = '';
        this.generateThumbnail = false;
        this.thumbnailPath = null;
        
        this.initElements();
        this.bindEvents();
    }

    initElements() {
        this.uploadInput = this.element.querySelector('.image-upload');
        this.altTextInput = this.element.querySelector('.alt-text-input');
        this.thumbnailCheckbox = this.element.querySelector('.thumbnail-checkbox');
        this.previewContainer = this.element.querySelector('.current-image-preview');
    }

    bindEvents() {
        this.uploadInput.addEventListener('change', (e) => this.handleUpload(e));
        this.altTextInput.addEventListener('input', (e) => this.updateAltText(e));
        this.thumbnailCheckbox.addEventListener('change', (e) => this.toggleThumbnail(e));
    }

    handleUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);
        formData.append('generateThumbnail', this.generateThumbnail);

        fetch('/api/media/upload', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.imagePath = data.imagePath;
                this.thumbnailPath = data.thumbnailPath;
                this.updatePreview();
            }
        })
        .catch(error => console.error('Upload failed:', error));
    }

    updateAltText(event) {
        this.altText = event.target.value;
    }

    toggleThumbnail(event) {
        this.generateThumbnail = event.target.checked;
    }

    updatePreview() {
        if (!this.previewContainer) {
            this.previewContainer = document.createElement('div');
            this.previewContainer.className = 'current-image-preview';
            this.element.appendChild(this.previewContainer);
        }

        this.previewContainer.innerHTML = '';
        const img = document.createElement('img');
        img.src = this.imagePath;
        img.alt = 'Uploaded preview';
        this.previewContainer.appendChild(img);
    }

    getData() {
        return {
            imagePath: this.imagePath,
            altText: this.altText,
            generateThumbnail: this.generateThumbnail,
            thumbnailPath: this.thumbnailPath
        };
    }

    setData(data) {
        this.imagePath = data.imagePath || '';
        this.altText = data.altText || '';
        this.generateThumbnail = data.generateThumbnail || false;
        this.thumbnailPath = data.thumbnailPath || null;
        
        if (this.imagePath) {
            this.updatePreview();
        }
        
        if (this.altTextInput) {
            this.altTextInput.value = this.altText;
        }
        
        if (this.thumbnailCheckbox) {
            this.thumbnailCheckbox.checked = this.generateThumbnail;
        }
    }
}

// Initialize all image blocks on the page
document.querySelectorAll('.image-block-edit').forEach(el => {
    new ImageBlock(el);
});