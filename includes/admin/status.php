<?php
/**
 * System Status Template
 * 
 * Interface for monitoring system status
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

?><div class="system-status">
    <h1>System Status</h1>
    
    <div class="status-grid">
        <div class="status-card">
            <h3>CMS Version</h3>
            <p>1.0.0</p>
        </div>
        <div class="status-card">
            <h3>PHP Version</h3>
            <p><?= phpversion() ?></p>
        </div>
        <div class="status-card">
            <h3>Database</h3>
            <p>Connected</p>
        </div>
    </div>

    <div class="resource-usage">
        <h2>Resource Usage</h2>
        <ul>
            <li>Memory: 32MB / 128MB</li>
            <li>Disk Space: 45MB / 1GB</li>
            <li>CPU: 12%</li>
        </ul>
    </div>

    <div class="recent-errors">
        <h2>Recent Errors</h2>
        <ul>
            <li>2025-05-10 14:30: Database connection timeout (resolved)</li>
            <li>2025-05-09 10:15: Missing template file (resolved)</li>
        </ul>
    </div>
</div>
