class ARMarkerAdmin {
    constructor() {
        this.initElements();
        this.bindEvents();
        this.loadMarkers();
    }

    initElements() {
        this.table = document.getElementById('markers-table');
        this.tbody = this.table.querySelector('tbody');
        this.refreshBtn = document.getElementById('refresh-markers');
        this.generateBtn = document.getElementById('generate-markers');
        this.bulkActionsBtn = document.getElementById('bulk-actions');
    }

    bindEvents() {
        this.refreshBtn.addEventListener('click', () => this.loadMarkers());
        this.generateBtn.addEventListener('click', () => this.showGenerateDialog());
        this.table.querySelector('.select-all').addEventListener('change', (e) => {
            this.tbody.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = e.target.checked;
            });
        });
    }

    async loadMarkers() {
        try {
            const response = await fetch('/api/ar/markers/list');
            if (!response.ok) throw new Error('Failed to fetch markers');
            const { markers } = await response.json();
            this.renderMarkers(markers);
        } catch (error) {
            console.error('Marker loading error:', error);
            alert('Failed to load markers. See console for details.');
        }
    }

    renderMarkers(markers) {
        this.tbody.innerHTML = markers.map(marker => `
            <tr>
                <td><input type="checkbox" data-id="${marker.id}"></td>
                <td>${marker.id}</td>
                <td>${marker.code}</td>
                <td>${new Date(marker.created_at).toLocaleString()}</td>
                <td>${marker.expires_at ? new Date(marker.expires_at).toLocaleString() : 'Never'}</td>
                <td>${marker.status}</td>
                <td>
                    <button class="btn btn-sm" data-action="view" data-id="${marker.id}">View</button>
                    <button class="btn btn-sm btn-danger" data-action="delete" data-id="${marker.id}">Delete</button>
                </td>
            </tr>
        `).join('');

        // Add event listeners to action buttons
        this.table.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleAction(
                e.target.dataset.action,
                e.target.dataset.id
            ));
        });
    }

    showGenerateDialog() {
        // TODO: Implement dialog for marker generation
        alert('Marker generation dialog will be implemented here');
    }

    handleAction(action, id) {
        switch(action) {
            case 'view':
                // TODO: Implement view details
                break;
            case 'delete':
                if (confirm('Delete this marker?')) {
                    this.deleteMarker(id);
                }
                break;
        }
    }

    async deleteMarker(id) {
        try {
            const response = await fetch(`/api/ar/markers/${id}`, {
                method: 'DELETE'
            });
            if (!response.ok) throw new Error('Delete failed');
            this.loadMarkers();
        } catch (error) {
            console.error('Delete error:', error);
            alert('Failed to delete marker');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ARMarkerAdmin();
});