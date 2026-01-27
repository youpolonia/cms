/**
 * Deployment System Admin JavaScript
 * Handles all frontend functionality for the deployment interface
 */

class DeploymentAdmin {
    constructor() {
        this.initElements();
        this.bindEvents();
        this.loadDeploymentLog();
    }

    initElements() {
        this.elements = {
            versionList: document.querySelector('.version-list'),
            createVersionBtn: document.getElementById('create-version-btn'),
            ftpConfigForm: document.getElementById('ftp-config-form'),
            logEntries: document.querySelector('.log-entries')
        };
    }

    bindEvents() {
        // Version activation
        this.elements.versionList.addEventListener('click', (e) => {
            if (e.target.classList.contains('activate-btn') && !e.target.textContent.includes('Active')) {
                this.activateVersion(e.target.dataset.version);
            }
        });

        // Create new version
        this.elements.createVersionBtn.addEventListener('click', () => {
            this.createNewVersion();
        });

        // Save FTP config
        this.elements.ftpConfigForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveFTPConfig(new FormData(e.target));
        });
    }

    async activateVersion(version) {
        try {
            const response = await fetch('/admin/api/deployment/activate-version', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ version })
            });

            if (response.ok) {
                location.reload();
            } else {
                this.showError('Failed to activate version');
            }
        } catch (error) {
            this.showError('Network error while activating version');
        }
    }

    async createNewVersion() {
        if (!confirm('Create a new deployment version? This will capture the current state.')) {
            return;
        }

        try {
            this.elements.createVersionBtn.disabled = true;
            this.elements.createVersionBtn.textContent = 'Creating...';

            const response = await fetch('/admin/api/deployment/create-version', {
                method: 'POST'
            });

            if (response.ok) {
                location.reload();
            } else {
                this.showError('Failed to create version');
                this.elements.createVersionBtn.disabled = false;
                this.elements.createVersionBtn.textContent = 'Create New Version';
            }
        } catch (error) {
            this.showError('Network error while creating version');
            this.elements.createVersionBtn.disabled = false;
            this.elements.createVersionBtn.textContent = 'Create New Version';
        }
    }

    async saveFTPConfig(formData) {
        try {
            const response = await fetch('/admin/api/deployment/save-ftp-config', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                this.showSuccess('FTP configuration saved successfully');
            } else {
                this.showError('Failed to save FTP configuration');
            }
        } catch (error) {
            this.showError('Network error while saving FTP configuration');
        }
    }

    async loadDeploymentLog() {
        try {
            const response = await fetch('/admin/api/deployment/get-logs');
            const logs = await response.json();

            let html = '';
            logs.forEach(log => {
                html += `
                    <div class="log-entry ${log.type}">
                        <span class="log-date">${new Date(log.timestamp).toLocaleString()}</span>
                        <span class="log-message">${log.message}</span>
                    </div>
                `;
            });

            this.elements.logEntries.innerHTML = html || '<div class="no-logs">No deployment logs found</div>';
        } catch (error) {
            this.elements.logEntries.innerHTML = '<div class="error">Failed to load deployment logs</div>';
        }
    }

    showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert success';
        alert.textContent = message;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert error';
        alert.textContent = message;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DeploymentAdmin();
});