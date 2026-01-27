document.addEventListener('DOMContentLoaded', () => {
    const versionList = document.getElementById('version-list');
    const contentSelect = document.getElementById('content-select');
    const refreshBtn = document.getElementById('refresh-btn');
    const compareBtn = document.getElementById('compare-btn');
    const rollbackBtn = document.getElementById('rollback-btn');
    const selectAll = document.getElementById('select-all');
    const comparisonModal = document.getElementById('comparison-modal');
    const rollbackModal = document.getElementById('rollback-modal');
    
    let selectedVersions = new Set();
    let currentContentId = null;

    // Initialize modals
    initModal(comparisonModal);
    initModal(rollbackModal);

    // Load content items
    loadContentItems();

    // Event listeners
    contentSelect.addEventListener('change', (e) => {
        currentContentId = e.target.value;
        if (currentContentId) {
            loadVersions(currentContentId);
        } else {
            clearVersionList();
        }
    });

    refreshBtn.addEventListener('click', () => {
        if (currentContentId) {
            loadVersions(currentContentId);
        }
    });

    compareBtn.addEventListener('click', showComparison);
    rollbackBtn.addEventListener('click', showRollbackModal);
    selectAll.addEventListener('change', toggleSelectAll);

    function loadContentItems() {
        fetch('/api/content/list')
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.title;
                    contentSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading content:', error));
    }

    function loadVersions(contentId) {
        fetch(`/admin/versions.php?action=history&content_id=${contentId}`)
            .then(response => response.json())
            .then(data => {
                renderVersionList(data.data);
                selectedVersions.clear();
                updateButtonStates();
            })
            .catch(error => console.error('Error loading versions:', error));
    }

    function renderVersionList(versions) {
        const tbody = versionList.querySelector('tbody');
        tbody.innerHTML = '';

        versions.forEach(version => {
            const row = document.createElement('tr');
            row.dataset.versionId = version.version_id;

            row.innerHTML = `
                <td><input type="checkbox" class="version-checkbox"></td>
                <td>${version.version_number}</td>
                <td>${version.created_by}</td>
                <td>${new Date(version.created_at).toLocaleString()}</td>
                <td>${version.rollback_notes || ''}</td>
                <td>
                    <button class="btn view-btn">View</button>
                </td>
            `;

            const checkbox = row.querySelector('.version-checkbox');
            checkbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    selectedVersions.add(version.version_id);
                } else {
                    selectedVersions.delete(version.version_id);
                }
                updateButtonStates();
            });

            const viewBtn = row.querySelector('.view-btn');
            viewBtn.addEventListener('click', () => {
                window.open(`/admin/version_view.php?id=${version.version_id}`, '_blank');
            });

            tbody.appendChild(row);
        });
    }

    function updateButtonStates() {
        const count = selectedVersions.size;
        compareBtn.disabled = count !== 2;
        rollbackBtn.disabled = count !== 1;
    }

    function toggleSelectAll(e) {
        const checkboxes = versionList.querySelectorAll('.version-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
            if (e.target.checked) {
                selectedVersions.add(checkbox.closest('tr').dataset.versionId);
            } else {
                selectedVersions.clear();
            }
        });
        updateButtonStates();
    }

    function showComparison() {
        const [v1, v2] = Array.from(selectedVersions);
        fetch(`/admin/versions.php?action=compare&version1=${v1}&version2=${v2}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('version1-number').textContent = data.data.version1;
                document.getElementById('version2-number').textContent = data.data.version2;
                
                const diffContainer = document.getElementById('diff-results');
                diffContainer.innerHTML = '';
                diffContainer.className = 'diff-container side-by-side';
                
                // Initialize DiffViewer
                const diffViewer = new DiffViewer({
                    container: diffContainer,
                    oldContent: data.data.content1,
                    newContent: data.data.content2,
                    renderMode: 'side-by-side'
                });
                diffViewer.render();

                // Add view mode toggle
                const toggle = document.createElement('div');
                toggle.className = 'diff-controls';
                toggle.innerHTML = `
                    <button class="diff-toggle active" data-mode="side-by-side">Side-by-side</button>
                    <button class="diff-toggle" data-mode="inline">Inline</button>
                `;
                diffContainer.prepend(toggle);

                // Handle mode switching
                toggle.querySelectorAll('.diff-toggle').forEach(btn => {
                    btn.addEventListener('click', () => {
                        toggle.querySelector('.active').classList.remove('active');
                        btn.classList.add('active');
                        diffViewer.setRenderMode(btn.dataset.mode);
                    });
                });

                comparisonModal.style.display = 'block';
            });
    }

    function showRollbackModal() {
        const versionId = Array.from(selectedVersions)[0];
        const versionNumber = document.querySelector(`tr[data-version-id="${versionId}"] td:nth-child(2)`).textContent;
        document.getElementById('rollback-version').textContent = versionNumber;
        document.getElementById('rollback-notes').value = '';
        rollbackModal.style.display = 'block';
    }

    document.getElementById('confirm-rollback').addEventListener('click', () => {
        const versionId = Array.from(selectedVersions)[0];
        const notes = document.getElementById('rollback-notes').value;

        fetch('/admin/versions.php?action=rollback', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                version_id: versionId,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rollback successful');
                loadVersions(currentContentId);
                rollbackModal.style.display = 'none';
            } else {
                alert('Rollback failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Rollback failed');
        });
    });

    function initModal(modal) {
        const closeBtn = modal.querySelector('.close');
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    function clearVersionList() {
        versionList.querySelector('tbody').innerHTML = '';
        selectedVersions.clear();
        updateButtonStates();
    }
});