document.addEventListener('DOMContentLoaded', function() {
    // Initialize analytics charts if on analytics page
    if (document.getElementById('trendsChart')) {
        initializeAnalyticsCharts();
    }

    // Initialize timeline container
    const container = document.getElementById('versionTimeline');
    
    // Get timeline data from cache or window object
    const cacheService = new AnalyticsCacheService();
    const cacheKey = `timeline_${window.contentId}`;
    let timelineData = cacheService.get(cacheKey);
    
    if (!timelineData) {
        timelineData = window.analyticsData ? window.analyticsData.version_timeline : null;
        if (timelineData) {
            cacheService.set(cacheKey, timelineData);
        }
    }
    
    if (!timelineData) {
        container.innerHTML = '<p class="text-center text-gray-500 py-8">No version history data available</p>';
        return;
    }

    // Create tooltip element
    const tooltip = document.createElement('div');
    tooltip.className = 'version-tooltip';
    document.body.appendChild(tooltip);

    // Create items array for the timeline with enhanced data
    const items = new vis.DataSet(
        timelineData.versions.map(version => ({
            id: version.id,
            content: `
                <div class="d-flex align-items-center">
                    <div class="user-avatar" style="background-color: ${stringToColor(version.author.name)}">
                        ${getInitials(version.author.name)}
                    </div>
                    <span>v${version.version_number}</span>
                </div>
            `,
            start: new Date(version.created_at),
            title: version.author.name,
            className: `version-item ${version.status}`,
            group: 1,
            versionData: version // Store full version data
        }))
    );

    // Create groups
    const groups = new vis.DataSet([
        { id: 1, content: 'Versions', className: 'version-group' }
    ]);

    // Configuration options
    const options = {
        zoomable: true,
        moveable: true,
        selectable: true,
        showCurrentTime: false,
        orientation: 'top',
        margin: {
            item: 10,
            axis: 5
        },
        format: {
            minorLabels: {
                minute: 'h:mma',
                hour: 'ha',
                weekday: 'ddd',
                day: 'D',
                month: 'MMM'
            },
            majorLabels: {
                month: 'MMM YYYY',
                year: 'YYYY'
            }
        },
        tooltip: {
            followMouse: true,
            overflowMethod: 'cap'
        }
    };

    // Create the Timeline
    const timeline = new vis.Timeline(container, items, groups, options);

    // Add comparison points if available
    if (timelineData.comparisons && timelineData.comparisons.length > 0) {
        const comparisonItems = new vis.DataSet(
            timelineData.comparisons.map(comp => ({
                id: `comp-${comp.id}`,
                content: '🔍',
                start: new Date(comp.compared_at),
                title: `Compared v${comp.base_version_id} with v${comp.target_version_id}`,
                className: 'comparison-item',
                group: 2,
                type: 'point'
            }))
        );

        groups.add({ id: 2, content: 'Comparisons', className: 'comparison-group' });
        timeline.setItems(vis.DataSet.merge([items, comparisonItems]));
    }

    // Handle version selection
    timeline.on('select', function(properties) {
        if (properties.items && properties.items.length > 0) {
            const itemId = properties.items[0];
            const item = items.get(itemId) || comparisonItems.get(itemId);
            
            if (item) {
                // Emit event or handle version selection
                console.log('Selected version:', item);
                // You could dispatch a custom event here for other components to handle:
                // window.dispatchEvent(new CustomEvent('versionSelected', { detail: item }));
            }
        }
    });

    // Handle mouseover for custom tooltips
    timeline.on('itemover', function(properties) {
        const item = items.get(properties.item);
        if (item) {
            const version = item.versionData;
            tooltip.innerHTML = `
                <div class="tooltip-content">
                    <h6>Version ${version.version_number}</h6>
                    <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(version.status)}">${version.status}</span></p>
                    <p><strong>Author:</strong> ${version.author.name}</p>
                    <p><strong>Date:</strong> ${new Date(version.created_at).toLocaleString()}</p>
                    <p><strong>Changes:</strong> ${version.changes_count} modifications</p>
                    ${version.tags && version.tags.length ? `<p><strong>Tags:</strong> ${version.tags.join(', ')}</p>` : ''}
                </div>
            `;
            tooltip.style.display = 'block';
            positionTooltip(properties.event);
        }
    });

    timeline.on('itemout', function() {
        tooltip.style.display = 'none';
    });

    timeline.on('mousemove', function(properties) {
        if (tooltip.style.display === 'block') {
            positionTooltip(properties.event);
        }
    });

    // Fit the timeline to show all items
    timeline.fit();

    // Make timeline available globally for debugging
    window.versionTimeline = timeline;

    // Helper functions
    function positionTooltip(event) {
        const x = event.clientX + 10;
        const y = event.clientY + 10;
        tooltip.style.left = `${x}px`;
        tooltip.style.top = `${y}px`;
    }

    function stringToColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        const color = Math.floor(Math.abs((Math.sin(hash) * 10000) % 1 * 16777216)).toString(16);
        return '#' + Array(6 - color.length + 1).join('0') + color;
    }

    function getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase();
    }

    function getStatusClass(status) {
        switch(status) {
            case 'approved': return 'success';
            case 'rejected': return 'danger';
            case 'pending': return 'warning';
            default: return 'secondary';
        }
    }
});

// Load Vis.js CSS dynamically if not already loaded
if (!document.querySelector('link[href*="vis-timeline"]')) {
    const link = document.createElement('link');
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/vis-timeline/7.4.2/vis-timeline-graph2d.min.css';
    link.rel = 'stylesheet';
    document.head.appendChild(link);
}

// Load Font Awesome for icons
if (!document.querySelector('link[href*="font-awesome"]')) {
    const faLink = document.createElement('link');
    faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
    faLink.rel = 'stylesheet';
    document.head.appendChild(faLink);
}

// Initialize analytics charts
function initializeAnalyticsCharts() {
    const analyticsData = window.analyticsData;
    
    // Trends Chart
    new Chart(document.getElementById('trendsChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: analyticsData.trends.labels,
            datasets: [{
                label: 'Comparisons',
                data: analyticsData.trends.data,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // Cache Chart
    new Chart(document.getElementById('cacheChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Cache Hits', 'Cache Misses'],
            datasets: [{
                data: [
                    analyticsData.cache_stats.cache_hits,
                    analyticsData.cache_stats.total - analyticsData.cache_stats.cache_hits
                ],
                backgroundColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)'
                ]
            }]
        }
    });

    // User Activity Chart
    new Chart(document.getElementById('userActivityChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: Object.keys(analyticsData.user_activity),
            datasets: [{
                label: 'Comparisons',
                data: Object.values(analyticsData.user_activity),
                backgroundColor: 'rgba(153, 102, 255, 0.6)'
            }]
        }
    });

    // Load version pairs data
    fetch(`/content/${window.contentId}/analytics/version-pairs`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('versionPairsTable');
            tableBody.innerHTML = data.map(pair => `
                <tr>
                    <td class="px-4 py-2">${pair.version1} ↔ ${pair.version2}</td>
                    <td class="px-4 py-2">${pair.count}</td>
                </tr>
            `).join('');
        });
}
