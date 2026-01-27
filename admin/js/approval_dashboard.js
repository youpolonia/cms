class ApprovalDashboard {
    static init() {
        this.bindEvents();
        this.refreshStats();
    }

    static bindEvents() {
        document.addEventListener('DOMContentLoaded', () => {
            setInterval(() => this.refreshStats(), 30000); // Refresh every 30 seconds
        });
    }

    static async refreshStats() {
        try {
            const response = await fetch('/api/v1/approval/stats');
            if (!response.ok) throw new Error('Failed to fetch stats');
            
            const stats = await response.json();
            this.updateStatsUI(stats);
        } catch (error) {
            console.error('Error refreshing stats:', error);
        }
    }

    static updateStatsUI(stats) {
        document.querySelector('.stat-card.pending .stat-value').textContent = stats.pending;
        document.querySelector('.stat-card.approved .stat-value').textContent = stats.approved;
        document.querySelector('.stat-card.rejected .stat-value').textContent = stats.rejected;
    }
}

ApprovalDashboard.init();