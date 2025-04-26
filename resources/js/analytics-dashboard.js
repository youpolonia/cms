import { createApp } from 'vue';
import Chart from 'chart.js/auto';
import axios from 'axios';
import { useMCPTool } from './composables/useMCP';
import AnalyticsCacheService from './services/AnalyticsCacheService';

// Initialize MCP tool
const mcpTool = useMCPTool('cms-knowledge-server');
import CompletionRatesChart from './components/ApprovalAnalytics/CompletionRatesChart.vue';
import ApprovalTimesChart from './components/ApprovalAnalytics/ApprovalTimesChart.vue';
import RejectionReasonsChart from './components/ApprovalAnalytics/RejectionReasonsChart.vue';
import ExportControls from './components/ApprovalAnalytics/ExportControls.vue';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    const completionRatesCtx = document.getElementById('completionRatesChart').getContext('2d');
    const approvalTimesCtx = document.getElementById('approvalTimesChart').getContext('2d');

    let completionRatesChart = new Chart(completionRatesCtx, {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${context.raw}%`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Completion Rate (%)' }
                }
            }
        }
    });

    let approvalTimesChart = new Chart(approvalTimesCtx, {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Hours' }
                }
            }
        }
    });

    // Current filters
    let currentFilters = {
        timeframe: 'monthly',
        dateFrom: null,
        dateTo: null,
        workflowId: null
    };

    // DOM elements
    const timeframeFilter = document.getElementById('timeframeFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');
    const workflowFilter = document.getElementById('workflowFilter');
    const refreshRejectionReasons = document.getElementById('refreshRejectionReasons');
    const exportRejectionReasons = document.getElementById('exportRejectionReasons');

    // Initialize workflow type selection
    const workflowTypeSelect = document.getElementById('workflow_type');
    const workflowIdSelect = document.getElementById('workflow_id');
    
    workflowTypeSelect.addEventListener('change', function() {
        // Clear existing options
        workflowIdSelect.innerHTML = '<option value="">Loading...</option>';
        
        // Load workflows for selected type
        axios.get(`/api/approval-workflows?type=${this.value}`)
            .then(response => {
                workflowIdSelect.innerHTML = '<option value="">All Workflows</option>';
                response.data.forEach(workflow => {
                    const option = document.createElement('option');
                    option.value = workflow.id;
                    option.textContent = workflow.name;
                    workflowIdSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading workflows:', error);
                workflowIdSelect.innerHTML = '<option value="">Error loading workflows</option>';
            });
    });

    // Load workflow options
    loadWorkflowOptions();

    // Initial data load
    loadAllData();

    // Event listeners
    timeframeFilter.addEventListener('change', function() {
        currentFilters.timeframe = this.value;
        clearCacheForCurrentFilters();
        loadAllData();
    });

    dateFromFilter.addEventListener('change', function() {
        currentFilters.dateFrom = this.value;
        clearCacheForCurrentFilters();
        loadAllData();
    });

    dateToFilter.addEventListener('change', function() {
        currentFilters.dateTo = this.value;
        clearCacheForCurrentFilters();
        loadAllData();
    });

    workflowFilter.addEventListener('change', function() {
        currentFilters.workflowId = this.value || null;
        clearCacheForCurrentFilters();
        loadAllData();
    });

    // Clear cache for current filter state
    function clearCacheForCurrentFilters() {
        const cacheService = new AnalyticsCacheService();
        // Invalidate all cache entries matching current filter pattern
        const pattern = new RegExp(JSON.stringify(currentFilters).replace(/[{}"]/g, '.*'));
        cacheService.invalidateMatching(pattern);
    }

    refreshRejectionReasons.addEventListener('click', function() {
        loadRejectionReasons();
    });

    exportRejectionReasons.addEventListener('click', function() {
        exportData('rejectionReasons');
    });

    // Show toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-md text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Add manual cache clear button if it exists
    const clearCacheBtn = document.getElementById('clearCacheBtn');
    if (clearCacheBtn) {
        clearCacheBtn.addEventListener('click', function() {
            const cacheService = new AnalyticsCacheService();
            cacheService.invalidateAll();
            loadAllData();
            showToast('Cache cleared successfully!');
        });
    }

    // Clear cache for current filter state
    function clearCacheForCurrentFilters() {
        const cacheService = new AnalyticsCacheService();
        // Invalidate all cache entries matching current filter pattern
        const pattern = new RegExp(JSON.stringify(currentFilters).replace(/[{}"]/g, '.*'));
        cacheService.invalidateMatching(pattern);
        showToast('Cache invalidated for current filters');
    }

    // Load all dashboard data
    async function loadAllData() {
        loadCompletionRates();
        await loadApprovalTimes();
        loadRejectionReasons();
    }

    // Load completion rates data
    function loadCompletionRates() {
        const cacheService = new AnalyticsCacheService();
        const cacheKey = `completion_rates_${JSON.stringify(currentFilters)}`;
        const cachedData = cacheService.get(cacheKey);
        
        if (cachedData) {
            updateCompletionRatesChart(cachedData);
            document.getElementById('completionRatesTimestamp').textContent = 'Cached: ' + new Date().toLocaleString();
            return;
        }

        axios.get('/api/content-approval-analytics/completion-rates', {
            params: currentFilters
        })
        .then(response => {
            updateCompletionRatesChart(response.data);
            document.getElementById('completionRatesTimestamp').textContent = new Date().toLocaleString();
            // Try MCP cache first, fallback to local
            mcpTool.cache_file({ path: cacheKey })
                .then(() => {
                    cacheService.set(cacheKey, response.data, 300); // Local fallback
                })
                .catch(err => {
                    console.warn('MCP cache failed, using local only:', err);
                    cacheService.set(cacheKey, response.data, 300);
                });
        })
        .catch(error => {
            console.error('Error loading completion rates:', error);
        });
    }

    // Update completion rates chart
    function updateCompletionRatesChart(data) {
        const labels = Object.keys(data);
        const rates = labels.map(label => data[label].rate);

        completionRatesChart.data.labels = labels;
        completionRatesChart.data.datasets = [{
            label: 'Completion Rate',
            data: rates,
            backgroundColor: 'rgba(59, 130, 246, 0.7)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1
        }];
        completionRatesChart.update();
    }

    // Load approval times data
    async function loadApprovalTimes() {
        const cacheService = new AnalyticsCacheService();
        const cacheKey = `approval_times_${JSON.stringify(currentFilters)}`;
        // Check MCP cache first
        let cachedData = null;
        
        try {
            const mcpData = await mcpTool.get_cached_file({ path: cacheKey });
            if (mcpData) {
                cachedData = mcpData;
                document.getElementById('completionRatesTimestamp').textContent = 'MCP Cached: ' + new Date().toLocaleString();
            }
        } catch (err) {
            console.warn('MCP cache check failed:', err);
            cachedData = cacheService.get(cacheKey);
            if (cachedData) {
                document.getElementById('completionRatesTimestamp').textContent = 'Local Cached: ' + new Date().toLocaleString();
            }
        }
        
        if (cachedData) {
            updateApprovalTimesChart(cachedData);
            document.getElementById('approvalTimesTimestamp').textContent = 'Cached: ' + new Date().toLocaleString();
            return;
        }

        axios.get('/api/content-approval-analytics/approval-times', {
            params: currentFilters
        })
        .then(response => {
            updateApprovalTimesChart(response.data);
            document.getElementById('approvalTimesTimestamp').textContent = new Date().toLocaleString();
            cacheService.set(cacheKey, response.data, 300); // Cache for 5 minutes
        })
        .catch(error => {
            console.error('Error loading approval times:', error);
        });
    }

    // Update approval times chart
    function updateApprovalTimesChart(data) {
        const labels = Object.keys(data);
        const times = labels.map(label => data[label].average_time);

        approvalTimesChart.data.labels = labels;
        approvalTimesChart.data.datasets = [{
            label: 'Average Approval Time',
            data: times,
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 2,
            tension: 0.1,
            fill: true
        }];
        approvalTimesChart.update();
    }

    // Load rejection reasons data
    function loadRejectionReasons() {
        const cacheService = new AnalyticsCacheService();
        const cacheKey = `rejection_reasons_${JSON.stringify(currentFilters)}`;
        const cachedData = cacheService.get(cacheKey);
        
        if (cachedData) {
            updateRejectionReasonsTable(cachedData);
            document.getElementById('rejectionReasonsTimestamp').textContent = 'Cached: ' + new Date().toLocaleString();
            return;
        }

        axios.get('/api/content-approval-analytics/rejection-reasons', {
            params: currentFilters
        })
        .then(response => {
            updateRejectionReasonsTable(response.data);
            document.getElementById('rejectionReasonsTimestamp').textContent = new Date().toLocaleString();
            cacheService.set(cacheKey, response.data, 300); // Cache for 5 minutes
        })
        .catch(error => {
            console.error('Error loading rejection reasons:', error);
        });
    }

    // Update rejection reasons table
    function updateRejectionReasonsTable(data) {
        const tbody = document.getElementById('rejectionReasonsBody');
        tbody.innerHTML = '';

        // Calculate total rejections
        let total = 0;
        Object.values(data).forEach(timeframe => {
            Object.values(timeframe).forEach(count => {
                total += count;
            });
        });

        // Add rows for each reason
        Object.entries(data).forEach(([timeframe, reasons]) => {
            Object.entries(reasons).forEach(([reason, count]) => {
                const percentage = total > 0 ? ((count / total) * 100).toFixed(2) : 0;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">${reason}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${count}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${percentage}%</td>
                `;
                tbody.appendChild(row);
            });
        });
    }

    // Load workflow options
    function loadWorkflowOptions() {
        axios.get('/api/approval-workflows')
            .then(response => {
                response.data.forEach(workflow => {
                    const option = document.createElement('option');
                    option.value = workflow.id;
                    option.textContent = workflow.name;
                    workflowFilter.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading workflow options:', error);
            });
    }

    // Export handlers for new chart types
    function exportComparisonActivity(format) {
        exportChartData('comparison-activity', format);
    }

    function exportUsageBreakdown(format) {
        exportChartData('usage-breakdown', format);
    }

    function exportApprovalMetrics(format) {
        exportChartData('approval-metrics', format);
    }

    // Generic chart data exporter
    function exportChartData(type, format) {
        axios.post('/api/analytics/export', {
            type: type,
            format: format,
            filters: currentFilters
        }, {
            responseType: 'blob'
        })
        .then(response => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `analytics-${type}-export.${format}`);
            document.body.appendChild(link);
            link.click();
            link.remove();
            showToast(`${type} exported successfully as ${format.toUpperCase()}`);
        })
        .catch(error => {
            console.error('Error exporting data:', error);
            showToast(`Failed to export ${type}`, 'error');
        });
    }

    // Legacy export function (keep for backward compatibility)
    function exportData(type) {
        exportChartData(type, 'csv');
    }

    // Initialize Vue app with export handlers
    const app = createApp({
        components: {
            CompletionRatesChart,
            ApprovalTimesChart,
            RejectionReasonsChart,
            ExportControls
        },
        methods: {
            exportComparisonActivity,
            exportUsageBreakdown,
            exportApprovalMetrics
        }
    });
    
    // Mount the Vue app to the DOM
    app.mount('#app');
});
