class SharedContentUI {
    static init() {
        this.loadDashboardStats();
        this.loadContentList();
        this.loadSyncStatus();
        this.loadConflicts();
        this.setupEventHandlers();
    }

    static loadDashboardStats() {
        fetch('/admin/shared_content.php?action=dashboard_stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('active-shares').textContent = data.active_shares || 0;
                document.getElementById('pending-sync').textContent = data.pending_sync || 0;
                document.getElementById('conflict-count').textContent = data.conflicts || 0;
            });
    }

    static loadContentList() {
        fetch('/admin/shared_content.php?action=list_content')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('content-select');
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.title;
                    select.appendChild(option);
                });
            });
    }

    static loadSyncStatus() {
        fetch('/admin/shared_content.php?action=sync_status')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('sync-status-body');
                tbody.innerHTML = '';
                
                data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${this.escapeHtml(item.content_title)}</td>
                        <td>${this.escapeHtml(item.site_name)}</td>
                        <td class="status-${item.status}">${item.status}</td>
                        <td>${item.last_sync || 'Never'}</td>
                        <td>
                            <button class="btn-small refresh-btn" data-id="${item.id}">Refresh</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            });
    }

    static loadConflicts() {
        fetch('/admin/shared_content.php?action=list_conflicts')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('conflict-list');
                container.innerHTML = '';

                if (data.length === 0) {
                    container.innerHTML = '<p>No conflicts detected</p>';
                    return;
                }

                data.forEach(conflict => {
                    const conflictEl = document.createElement('div');
                    conflictEl.className = 'conflict-item';
                    conflictEl.dataset.id = conflict.id;
                    conflictEl.innerHTML = `
                        <h4>${this.escapeHtml(conflict.content_title)}</h4>
                        <p>Site: ${this.escapeHtml(conflict.site_name)}</p>
                        <p>Detected: ${conflict.detected_at}</p>
                        <button class="btn-small resolve-btn">Resolve</button>
                    `;
                    container.appendChild(conflictEl);
                });
            });
    }

    static setupEventHandlers() {
        document.getElementById('share-btn').addEventListener('click', () => {
            const contentId = document.getElementById('content-select').value;
            const siteCheckboxes = document.querySelectorAll('input[name="target_sites"]:checked');
            const permissionCheckboxes = document.querySelectorAll('input[name="permissions"]:checked');
            
            const targetSites = Array.from(siteCheckboxes).map(cb => cb.value);
            const permissions = Array.from(permissionCheckboxes).map(cb => cb.value);

            if (!contentId || targetSites.length === 0) {
                alert('Please select content and at least one target site');
                return;
            }

            fetch('/admin/shared_content.php?action=share', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contentId, targetSites, permissions })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    alert('Content shared successfully');
                    this.loadSyncStatus();
                    this.loadDashboardStats();
                }
            });
        });

        document.addEventListener('click', e => {
            if (e.target.classList.contains('resolve-btn')) {
                const conflictId = e.target.closest('.conflict-item').dataset.id;
                document.getElementById('resolution-options').classList.remove('hidden');
                document.getElementById('resolution-options').dataset.conflictId = conflictId;
            }
        });

        document.getElementById('resolve-btn').addEventListener('click', () => {
            const conflictId = document.getElementById('resolution-options').dataset.conflictId;
            const resolution = document.querySelector('input[name="resolution"]:checked').value;

            fetch('/admin/shared_content.php?action=resolve', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ conflictId, resolution })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    alert('Conflict resolved successfully');
                    this.loadConflicts();
                    this.loadDashboardStats();
                    document.getElementById('resolution-options').classList.add('hidden');
                }
            });
        });
    }

    static escapeHtml(unsafe) {
        const text = document.createTextNode(String(unsafe));
        const div = document.createElement('div');
        div.appendChild(text);
        return div.innerHTML;
    }
}

document.addEventListener('DOMContentLoaded', () => SharedContentUI.init());