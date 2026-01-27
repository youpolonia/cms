class SecurityDashboard {
    constructor() {
        this.scans = [];
        this.activeScans = [];
        this.scanResults = {};
    }

    init() {
        this.loadAvailableScans();
        this.loadScanHistory();
        setInterval(() => this.loadScanHistory(), 5000);
    }

    loadAvailableScans() {
        return fetch('/admin/security-dashboard.php?action=list_scans')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.scans = data.scans;
                    this.renderAvailableScans();
                }
                return data;
            });
    }

    renderAvailableScans() {
        const container = document.getElementById('scanTypesContainer');
        if (!container) return;

        container.innerHTML = '';
        for (const [scanType, description] of Object.entries(this.scans)) {
            const button = document.createElement('button');
            button.className = 'btn btn-primary mr-2 mb-2';
            button.textContent = description;
            button.onclick = () => this.startScan(scanType);
            container.appendChild(button);
        }
    }

    startScan(scanType) {
        return fetch(`/admin/security-dashboard.php?action=init_scan&scan_type=${scanType}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.loadScanHistory();
                }
                return data;
            });
    }

    loadScanHistory() {
        return fetch('/admin/security-dashboard.php?action=list_scans')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.renderScanHistory(data.scans);
                }
                return data;
            });
    }

    renderScanHistory(scans) {
        const container = document.getElementById('scanHistoryContainer');
        if (!container) return;

        container.innerHTML = '';
        for (const [scanType, description] of Object.entries(scans)) {
            const card = document.createElement('div');
            card.className = 'scan-card';
            card.innerHTML = `
                <h4>${description}</h4>
                <p>Type: ${scanType}</p>
                <button class="btn btn-sm btn-info" 
                    onclick="securityDashboard.viewScanResults('${scanType}')">
                    View Results
                </button>
            `;
            container.appendChild(card);
        }
    }

    viewScanResults(scanId) {
        return fetch(`/admin/security-dashboard.php?action=get_results&scan_id=${scanId}`)
            .then(response => response.json())
            .then(data => {
                this.scanResults[scanId] = data;
                this.renderScanResults(data);
                return data;
            });
    }

    renderScanResults(data) {
        const container = document.getElementById('scanResultsContainer');
        if (!container) return;

        container.innerHTML = '';
        if (data.status === 'success') {
            const resultsDiv = document.createElement('div');
            resultsDiv.innerHTML = `
                <h4>Scan ID: ${data.scan_id}</h4>
                <p>Status: ${data.status}</p>
                <h5>Findings:</h5>
                <ul>
                    ${data.findings.map(f => `<li>${f}</li>`).join('')}
                </ul>
                <h5>Recommendations:</h5>
                <ul>
                    ${data.recommendations.map(r => `<li>${r}</li>`).join('')}
                </ul>
            `;
            container.appendChild(resultsDiv);
        } else {
            container.textContent = `Error: ${data.message}`;
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.securityDashboard = new SecurityDashboard();
    securityDashboard.init();
});