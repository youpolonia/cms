class VersionHistory {
  constructor(contentId, containerSelector) {
    this.contentId = contentId;
    this.container = document.querySelector(containerSelector);
    this.versions = [];
    this.init();
  }

  async init() {
    await this.loadVersions();
    this.render();
    this.bindEvents();
  }

  async loadVersions() {
    try {
      const response = await fetch(`/api/content/versions?content_id=${this.contentId}`);
      const data = await response.json();
      this.versions = data.versions || [];
    } catch (error) {
      console.error('Failed to load versions:', error);
      this.versions = [];
    }
  }

  render() {
    if (this.versions.length === 0) {
      this.container.innerHTML = '<div class="no-versions">No version history available</div>';
      return;
    }

    let html = `
      <div class="version-history-header">
        <h3>Version History</h3>
        <div class="version-actions">
          <button class="btn-refresh">Refresh</button>
        </div>
      </div>
      <div class="version-list">
        <table>
          <thead>
            <tr>
              <th>Version</th>
              <th>Date</th>
              <th>State</th>
              <th>User</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
    `;

    this.versions.forEach(version => {
      html += `
        <tr data-version-id="${version.id}">
          <td>#${version.id}</td>
          <td>${new Date(version.created_at).toLocaleString()}</td>
          <td>${version.to_state}</td>
          <td>${version.user_name || 'System'}</td>
          <td>
            <button class="btn-view" data-version-id="${version.id}">View</button>
            <button class="btn-restore" data-version-id="${version.id}">Restore</button>
          </td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    this.container.innerHTML = html;
  }

  bindEvents() {
    // Refresh button
    this.container.querySelector('.btn-refresh')?.addEventListener('click', () => {
      this.loadVersions().then(() => this.render());
    });

    // View buttons
    this.container.querySelectorAll('.btn-view').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const versionId = e.target.dataset.versionId;
        this.viewVersion(versionId);
      });
    });

    // Restore buttons
    this.container.querySelectorAll('.btn-restore').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const versionId = e.target.dataset.versionId;
        this.confirmRestore(versionId);
      });
    });
  }

  viewVersion(versionId) {
    // Open version in comparison view
    const version = this.versions.find(v => v.id == versionId);
    if (version) {
      window.dispatchEvent(new CustomEvent('version-view', { 
        detail: { 
          versionId: versionId,
          contentId: this.contentId 
        }
      }));
    }
  }

  async confirmRestore(versionId) {
    if (!confirm('Are you sure you want to restore this version? This will overwrite the current content.')) {
      return;
    }

    try {
      const response = await fetch('/api/content/restore-version', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          content_id: this.contentId,
          version_id: versionId
        })
      });

      const result = await response.json();
      if (result.success) {
        alert('Version restored successfully');
        window.dispatchEvent(new CustomEvent('content-updated', { 
          detail: { contentId: this.contentId } 
        }));
      } else {
        throw new Error(result.error || 'Restore failed');
      }
    } catch (error) {
      console.error('Restore failed:', error);
      alert(`Restore failed: ${error.message}`);
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const contentId = document.currentScript.getAttribute('data-content-id');
  if (contentId) {
    new VersionHistory(contentId, '.version-history-container');
  }
});