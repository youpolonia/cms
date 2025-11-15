class RegionMapVisualizer {
    constructor(mapElementId) {
        this.mapElement = document.getElementById(mapElementId);
        this.map = null;
        this.regionData = {};
        this.initMap();
    }

    initMap() {
        // Initialize Leaflet map with default view
        this.map = L.map(this.mapElement).setView([20, 0], 2);
        
        // Add tile layer (using OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        // Initialize empty layer group for regions
        this.regionLayer = L.layerGroup().addTo(this.map);
    }

    async loadRegionData(tenantId = 'all', startDate, endDate) {
        try {
            const response = await fetch(`/api/v1/analytics/regions?tenant=${tenantId}&start=${startDate}&end=${endDate}`);
            this.regionData = await response.json();
            this.updateMap();
        } catch (error) {
            console.error('Failed to load region data:', error);
        }
    }

    updateMap() {
        // Clear existing regions
        this.regionLayer.clearLayers();

        // Calculate max visits for color scaling
        const maxVisits = Math.max(...Object.values(this.regionData).map(r => r.visits));

        // Add regions to map
        for (const [regionCode, data] of Object.entries(this.regionData)) {
            const { lat, lng, visits } = data;
            const radius = Math.sqrt(visits) * 2;
            const colorIntensity = Math.min(255, Math.floor(255 * (visits / maxVisits));
            const color = `rgb(255, ${255 - colorIntensity}, 0)`;

            L.circleMarker([lat, lng], {
                radius,
                fillColor: color,
                color: '#000',
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8
            }).bindPopup(`<b>${regionCode}</b><br>Visits: ${visits}`)
              .addTo(this.regionLayer);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const regionMap = new RegionMapVisualizer('region-map');
    
    // Set default date range (last 30 days)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(endDate.getDate() - 30);
    
    document.getElementById('start-date').valueAsDate = startDate;
    document.getElementById('end-date').valueAsDate = endDate;
    
    // Load initial data
    regionMap.loadRegionData(
        document.getElementById('tenant-select').value,
        startDate.toISOString().split('T')[0],
        endDate.toISOString().split('T')[0]
    );
    
    // Add event listeners
    document.getElementById('apply-dates').addEventListener('click', () => {
        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;
        const tenant = document.getElementById('tenant-select').value;
        regionMap.loadRegionData(tenant, start, end);
    });
    
    document.getElementById('tenant-select').addEventListener('change', () => {
        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;
        const tenant = document.getElementById('tenant-select').value;
        regionMap.loadRegionData(tenant, start, end);
    });
});