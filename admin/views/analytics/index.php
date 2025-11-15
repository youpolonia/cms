<?php
// Admin Analytics Dashboard View
require_once __DIR__ . '/../../includes/admin/Header.php';
?>
<div class="analytics-container">
    <h1>Tenant Analytics Dashboard</h1>
    
    <div class="dashboard-controls">
        <select id="tenant-selector" class="form-control">
            <option value="">Select Tenant</option>
            <!-- Will be populated via JS -->
        </select>
        
        <select id="date-range" class="form-control">
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
            <option value="90d">Last 90 Days</option>
        </select>
    </div>

    <div class="analytics-sections">
        <div class="summary-section">
            <h2>Tenant Summary</h2>
            <div id="summary-data" class="data-container"></div>
        </div>
        
        <div class="comparison-section">
            <h2>Multi-Tenant Comparison</h2>
            <div id="comparison-data" class="data-container"></div>
        </div>
    </div>
</div>

<script src="/admin/js/analytics.js"></script>

<?php
require_once __DIR__ . '/../../includes/admin/Footer.php';
