class HookDebugConsole {
    constructor() {
        this.currentTab = 'hooks';
        this.refreshInterval = 2000; // 2 seconds
        this.initElements();
        this.setupEventListeners();
        this.loadInitialData();
        this.startAutoRefresh();
    }

    initElements() {
        this.tabButtons = document.querySelectorAll('.tab-btn');
        this.tabContents = document.querySelectorAll('.tab-content');
        this.hookTypeFilter = document.getElementById('hook-type-filter');
        this.searchTermInput = document.getElementById('search-term');
        this.refreshButton = document.getElementById('refresh-btn');
        this.hooksList = document.getElementById('hooks-list');
        this.apiCallsList = document.getElementById('api-calls-list');
    }

    setupEventListeners() {
        this.tabButtons.forEach(btn => {
            btn.addEventListener('click', () => this.switchTab(btn.dataset.tab));
        });

        this.hookTypeFilter.addEventListener('change', () => this.applyFilters());
        this.searchTermInput.addEventListener('input', () => this.applyFilters());
        this.refreshButton.addEventListener('click', () => this.refreshData());
    }

    switchTab(tabName) {
        this.currentTab = tabName;
        this.tabButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tabName));
        this.tabContents.forEach(content => content.classList.toggle('active', content.id === `${tabName}-tab`));
        this.refreshData();
    }

    async loadInitialData() {
        await this.fetchData();
    }

    startAutoRefresh() {
        this.autoRefreshTimer = setInterval(() => this.refreshData(), this.refreshInterval);
    }

    async refreshData() {
        clearInterval(this.autoRefreshTimer);
        await this.fetchData();
        this.startAutoRefresh();
    }

    async fetchData() {
        try {
            const response = await fetch('/developer-tools/hook-debug/get-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    filters: {
                        hook_type: this.hookTypeFilter.value,
                        search_term: this.searchTermInput.value
                    }
                })
            });

            const data = await response.json();

            if (this.currentTab === 'hooks') {
                this.updateHooksList(data.hooks);
            } else {
                this.updateApiCallsList(data.apiCalls);
            }
        } catch (error) {
            console.error('Error fetching debug data:', error);
        }
    }

    updateHooksList(hooks) {
        this.hooksList.innerHTML = '';
        
        Object.entries(hooks).forEach(([hookName, priorities]) => {
            Object.entries(priorities).forEach(([priority, callbacks]) => {
                callbacks.forEach(callback => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${hookName}</td>
                        <td>${priority}</td>
                        <td>${this.getCallbackName(callback.callback)}</td>
                        <td>${new Date(callback.timestamp * 1000).toLocaleString()}</td>
                    `;
                    this.hooksList.appendChild(row);
                });
            });
        });
    }

    updateApiCallsList(apiCalls) {
        this.apiCallsList.innerHTML = '';
        
        apiCalls.forEach(call => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${call.endpoint}</td>
                <td>${JSON.stringify(call.data)}</td>
                <td>${new Date(call.timestamp * 1000).toLocaleString()}</td>
            `;
            this.apiCallsList.appendChild(row);
        });
    }

    getCallbackName(callback) {
        if (typeof callback === 'string') return callback;
        if (callback.name) return callback.name;
        if (Array.isArray(callback)) {
            return callback.map(item => this.getCallbackName(item)).join('::');
        }
        return 'Anonymous function';
    }

    applyFilters() {
        this.refreshData();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new HookDebugConsole();
});