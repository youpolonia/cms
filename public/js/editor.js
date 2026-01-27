document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE
    tinymce.init({
        selector: '#editor',
        plugins: 'link image media table code',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image media | code',
        height: 500,
        images_upload_url: '/api/media/upload',
        automatic_uploads: true
    });

    // Version history modal
    const versionModal = document.getElementById('version-modal');
    const versionBtn = document.getElementById('version-history');
    const closeBtn = document.querySelector('.close');
    const diffViewer = document.getElementById('diff-viewer');
    
    versionBtn.addEventListener('click', () => versionModal.style.display = 'block');
    closeBtn.addEventListener('click', () => {
        versionModal.style.display = 'none';
        diffViewer.style.display = 'none';
    });

    // Handle version diff viewing
    document.querySelectorAll('.view-diff').forEach(btn => {
        btn.addEventListener('click', async function() {
            const versionId = this.dataset.version;
            const contentId = document.querySelector('input[name="content_id"]').value;
            
            try {
                const response = await fetch(`/api/content/version-diff?content_id=${contentId}&version_id=${versionId}`);
                const diff = await response.text();
                
                diffViewer.innerHTML = `<pre>${diff}</pre>`;
                diffViewer.style.display = 'block';
            } catch (error) {
                console.error('Error loading diff:', error);
            }
        });
    });

    // Handle version restoration
    document.querySelectorAll('.restore').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Restore this version?')) return;
            
            const versionId = this.dataset.version;
            const contentId = document.querySelector('input[name="content_id"]').value;
            
            try {
                const response = await fetch('/api/content/restore-version', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        content_id: contentId,
                        version_id: versionId
                    })
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Failed to restore version');
                }
            } catch (error) {
                console.error('Error restoring version:', error);
            }
        });
    });

    // Media upload button
    document.getElementById('media-upload').addEventListener('click', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*,video/*';
        
        input.onchange = async function() {
            const file = this.files[0];
            const formData = new FormData();
            formData.append('file', file);
            
            try {
                const response = await fetch('/api/media/upload', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.url) {
                    tinymce.activeEditor.insertContent(`<img src="${result.url}" alt="${file.name}">`);
                }
            } catch (error) {
                console.error('Upload failed:', error);
            }
        };
        
        input.click();
    });
});