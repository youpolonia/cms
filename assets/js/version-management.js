document.addEventListener('DOMContentLoaded', function() {
    const contentId = new URLSearchParams(window.location.search).get('id');
    if (!contentId) return;

    // Load versions for this content item
    fetch(`/api/content/${contentId}/versions`)
        .then(response => response.json())
        .then(versions => {
            const tbody = document.querySelector('.version-table tbody');
            tbody.innerHTML = versions.map(version => `
                <tr>
                    <td><input type="checkbox" class="version-checkbox" data-id="${version.id}"></td>
                    <td>v${version.version_number}</td>
                    <td>${new Date(version.created_at).toLocaleString()}</td>
                    <td>${version.author_name}</td>
                    <td>${version.change_type}</td>
                    <td class="version-actions">
                        <button class="version-btn compare" data-id="${version.id}">Compare</button>
                        <button class="version-btn restore" data-id="${version.id}">Restore</button>
                        <button class="version-btn delete" data-id="${version.id}">Delete</button>
                    </td>
                </tr>
            `).join('');

            // Setup event listeners
            setupVersionActions();
        })
        .catch(error => console.error('Error loading versions:', error));

    // Select all versions checkbox
    document.getElementById('select-all-versions').addEventListener('change', function(e) {
        document.querySelectorAll('.version-checkbox').forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
    });
});

function setupVersionActions() {
    // Compare versions
    document.querySelectorAll('.version-btn.compare').forEach(btn => {
        btn.addEventListener('click', function() {
            const selectedVersions = Array.from(document.querySelectorAll('.version-checkbox:checked'))
                .map(cb => cb.getAttribute('data-id'));
            
            if (selectedVersions.length !== 2) {
                alert('Please select exactly 2 versions to compare');
                return;
            }

            fetch(`/api/content/versions/compare?from=${selectedVersions[0]}&to=${selectedVersions[1]}`)
                .then(response => response.json())
                .then(diff => {
                    // Open comparison modal with diff data
                    openDiffModal(diff);
                })
                .catch(error => {
                    console.error('Comparison failed:', error);
                    alert('Failed to compare versions');
                });
        });
    });

    function openDiffModal(diffData) {
        const modal = document.getElementById('diff-modal');
        const diffContent = document.getElementById('diff-content');
        
        // Render diff content using diff library
        diffContent.innerHTML = renderDiff(diffData);
        
        // Show modal
        modal.style.display = 'block';
    }

    // Restore versions
    document.querySelectorAll('.version-btn.restore').forEach(btn => {
        btn.addEventListener('click', function() {
            const versionId = this.getAttribute('data-id');
            if (confirm('Restore this version?')) {
                fetch(`/api/content/versions/${versionId}/restore`, { method: 'POST' })
                    .then(response => {
                        if (response.ok) location.reload();
                    });
            }
        });
    });

    // Delete versions
    document.querySelectorAll('.version-btn.delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const versionId = this.getAttribute('data-id');
            if (confirm('Permanently delete this version?')) {
                fetch(`/api/content/versions/${versionId}`, { method: 'DELETE' })
                    .then(response => {
                        if (response.ok) location.reload();
                    });
            }
        });
    });
}