/**
 * Phase 15 Analytics Dashboard
 */
class AnalyticsDashboard {
  constructor() {
    this.charts = {};
    this.eventSource = null;
    this.userPermissions = {};
  }

  // Initialize dashboard with user permissions
  init(user) {
    this.userPermissions = user.permissions || {};
    this.renderBaseLayout();
    this.setupEventStream();
    this.loadInitialData();
  }

  // Render core dashboard layout
  renderBaseLayout() {
    const container = document.querySelector('.analytics-container') || 
      document.body.appendChild(document.createElement('div'));
    container.className = 'analytics-container';
    container.innerHTML = `
      <div class="dashboard-header">
        <h2>Analytics Dashboard</h2>
        <div class="access-controls"></div>
      </div>
      <div class="chart-container"></div>
      <div class="report-container">
        <button class="generate-report">Generate Report</button>
        <div class="report-download-link"></div>
      </div>
    `;
    
    // Setup report generation
    container.querySelector('.generate-report').addEventListener('click', () => {
      this.generateReport();
    });
  }

  // Setup SSE connection for real-time data
  setupEventStream() {
    if (this.eventSource) this.eventSource.close();
    
    this.eventSource = new EventSource('/analytics/events');
    this.eventSource.onmessage = (event) => {
      const data = JSON.parse(event.data);
      window.dispatchEvent(new CustomEvent('analytics_event', { detail: data }));
      this.updateCharts(data);
    };
  }

  // Load initial aggregated data
  async loadInitialData() {
    try {
      const response = await fetch('/analytics/aggregated', {
        headers: {
          'X-TENANT-ID': window.currentTenantId || 'default'
        }
      });
      const data = await response.json();
      this.renderCharts(data);
    } catch (error) {
      console.error('Failed to load initial data:', error);
    }
  }

  // Render charts with initial data
  renderCharts(data) {
    if (!data || data.error) {
      console.error('Invalid chart data:', data);
      return this.showError('Failed to load analytics data');
    }
    this.charts.main = this.createChart(data, '.chart-container');
  }

  // Update charts with new data
  updateCharts(newData) {
    if (this.charts.main) {
      this.charts.main.update(newData);
    }
  }

  // Generate and download report
  async generateReport() {
    if (!this.userPermissions.canGenerateReports) {
      alert('You do not have permission to generate reports');
      return;
    }

    const linkContainer = document.querySelector('.report-download-link');
    linkContainer.innerHTML = 'Generating report...';

    try {
      const response = await fetch('/analytics/report', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ format: 'pdf' })
      });
      const { url } = await response.json();
      
      linkContainer.innerHTML = `
        <a href="${url}" download>Download Report</a>
      `;
    } catch (error) {
      linkContainer.innerHTML = 'Report generation failed';
      console.error('Report generation error:', error);
    }
  }
}

// Export for testing and initialization
export function renderAnalyticsDashboard(user = { permissions: {} }) {
  const dashboard = new AnalyticsDashboard();
  dashboard.init(user);
  return dashboard;
}