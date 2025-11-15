<?php
/**
 * Analytics Dashboard View
 * 
 * @package CMS
 * @subpackage Admin\Analytics
 */

defined('CMS_ROOT') or die('No direct script access allowed');

// Template sections require proper context - for standalone use, comment out
// <?php $this->startSection('content'); ?>
<div class="analytics-container">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="analytics-grid">
        <!-- Performance Metrics -->
        <div class="metric-card">
            <h2>System Performance</h2>
            <div class="metric-values">
                <?php foreach ($performanceMetrics as $metric => $value): ?>
                    <div class="metric-row">
                        <span class="metric-name"><?= htmlspecialchars($metric) ?>:</span>
                        <span class="metric-value"><?= htmlspecialchars($value) ?></span>
                    </div>
                <?php endforeach;  ?>
            </div>
        </div>

        <!-- Worker Stats -->
        <div class="metric-card">
            <h2>Recent Worker Activity</h2>
            <table class="worker-stats">
                <thead>
                    <tr>
                        <th>Worker</th>
                        <th>Activity</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workerStats as $stat): ?>
                        <tr>
                            <td><?= htmlspecialchars($stat['worker_name']) ?></td>
                            <td><?= htmlspecialchars($stat['activity']) ?></td>
                            <td><?= htmlspecialchars($stat['timestamp']) ?></td>
                        </tr>
                    <?php endforeach;  ?>
                </tbody>
            </table>
        </div>

        <!-- Client Activity Logs -->
        <div class="metric-card">
            <h2>Recent Client Activities</h2>
            <table class="client-stats">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Activity Type</th>
                        <th>Details</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientActivities as $activity): ?>
                        <tr>
                            <td><?= htmlspecialchars($activity['client_name']) ?></td>
                            <td><?= htmlspecialchars($activity['activity_type']) ?></td>
                            <td><?= htmlspecialchars(substr($activity['activity_details'], 0, 50)) ?></td>
                            <td><?= htmlspecialchars($activity['created_at']) ?></td>
                        </tr>
                    <?php endforeach;  ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Daily Report -->
    <div class="report-card">
        <h2>Daily Report</h2>
        <pre><?= htmlspecialchars(print_r($dailyReport, true)) ?></pre>
    </div>
</div>

// <?php $this->endSection();
