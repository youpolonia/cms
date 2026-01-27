// Tenant Analytics Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const tenantSelector = document.getElementById('tenant-selector');
    const dateRangeSelector = document.getElementById('date-range');
    const summaryDataContainer = document.getElementById('summary-data');
    const comparisonDataContainer = document.getElementById('comparison-data');

    // Fetch and populate tenant list
    fetch('/api/v1/tenants')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                data.data.forEach(tenant => {
                    const option = document.createElement('option');
                    option.value = tenant.id;
                    option.textContent = tenant.name;
                    tenantSelector.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching tenants:', error);
        });

    // Handle tenant selection changes
    tenantSelector.addEventListener('change', function() {
        const tenantId = this.value;
        const dateRange = dateRangeSelector.value;
        
        if (tenantId) {
            fetchTenantSummary(tenantId, dateRange);
        }
    });

    // Handle date range changes
    dateRangeSelector.addEventListener('change', function() {
        const tenantId = tenantSelector.value;
        const dateRange = this.value;
        
        if (tenantId) {
            fetchTenantSummary(tenantId, dateRange);
        }
    });

    // Fetch and display tenant summary data
    function fetchTenantSummary(tenantId, dateRange) {
        fetch(`/api/v1/analytics/tenant/${tenantId}?range=${dateRange}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    displaySummaryData(data.data);
                } else {
                    summaryDataContainer.innerHTML = `<div class="error">${data.message}</div>`;
                }
            })
            .catch(error => {
                summaryDataContainer.innerHTML = '<div class="error">Failed to load summary data</div>';
                console.error('Error:', error);
            });
    }

    // Display summary data
    function displaySummaryData(data) {
        let html = `<div class="summary-card">
            <h3>${data.tenant_name}</h3>
            <div class="metrics">`;
        
        for (const [metric, value] of Object.entries(data.metrics)) {
            html += `<div class="metric">
                <span class="metric-name">${metric}:</span>
                <span class="metric-value">${value}</span>
            </div>`;
        }

        html += `</div></div>`;
        summaryDataContainer.innerHTML = html;
    }
});