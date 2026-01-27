/**
 * Approval Workflow JavaScript
 * Handles UI interactions and API calls for content approval workflow
 */
class ApprovalWorkflow {
    constructor() {
        this.apiBaseUrl = '/api/approval';
        this.versionApiUrl = '/api/versions';
        this.currentContentId = null;
        this.currentState = null;
        
        this.initEventListeners();
    }

    initEventListeners() {
        // State transition buttons
        document.querySelectorAll('.approval-action').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleStateTransition(e));
        });

        // Version history toggle
        document.getElementById('show-version-history').addEventListener('click', () => {
            this.loadVersionHistory();
        });

        // Rollback buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('rollback-btn')) {
                this.rollbackToVersion(e.target.dataset.versionId);
            }
        });
    }

    async handleStateTransition(event) {
        const action = event.target.dataset.action;
        const contentId = this.currentContentId;
        
        try {
            const response = await fetch(`${this.apiBaseUrl}/process`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    content_id: contentId,
                    action: action
                })
            });

            const result = await response.json();
            this.updateUI(result.new_state);
            this.showSuccessMessage(`State changed to ${result.new_state}`);
        } catch (error) {
            console.error('Transition failed:', error);
            this.showErrorMessage('State transition failed');
        }
    }

    async loadVersionHistory() {
        try {
            const response = await fetch(`${this.versionApiUrl}/history?content_id=${this.currentContentId}`);
            const versions = await response.json();
            this.renderVersionHistory(versions);
        } catch (error) {
            console.error('Failed to load version history:', error);
            this.showErrorMessage('Failed to load version history');
        }
    }

    async rollbackToVersion(versionId) {
        if (!confirm('Are you sure you want to rollback to this version?')) return;

        try {
            const response = await fetch(`${this.versionApiUrl}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    version_id: versionId
                })
            });

            const result = await response.json();
            if (result.success) {
                this.showSuccessMessage('Version restored successfully');
                this.loadVersionHistory();
            }
        } catch (error) {
            console.error('Rollback failed:', error);
            this.showErrorMessage('Version rollback failed');
        }
    }

    renderVersionHistory(versions) {
        const container = document.getElementById('version-history-container');
        container.innerHTML = versions.map(version => `
            <div class="version-item">
                <h4>Version ${version.version_id}</h4>
                <p>${version.timestamp} by User ${version.user_id}</p>
                <p>${version.changes}</p>
                <button class="rollback-btn" data-version-id="${version.version_id}">
                    Restore this version
                </button>
            </div>
        `).join('');
    }

    updateUI(newState) {
        this.currentState = newState;
        document.querySelectorAll('.state-badge').forEach(el => {
            el.textContent = newState;
            el.className = `state-badge state-${newState}`;
        });
    }

    showSuccessMessage(msg) {
        // Implementation would show success toast/alert
        console.log('Success:', msg);
    }

    showErrorMessage(msg) {
        // Implementation would show error toast/alert
        console.error('Error:', msg);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ApprovalWorkflow();
});