<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
$debugger = HookDebugger::getInstance();
?><div class="hook-debug-console">
    <div class="debug-header">
        <h2>Hook & API Debug Console</h2>
        <div class="controls">
            <select id="hook-type-filter">
                <option value="">All Hook Types</option>
                <?php foreach(array_keys($debugger->getFilteredHooks()) as $hook): ?>                    <option value="<?= htmlspecialchars($hook) ?>"><?= htmlspecialchars($hook) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="search-term" placeholder="Search...">
            <button id="refresh-btn">Refresh</button>
        </div>
    </div>

    <div class="debug-tabs">
        <button class="tab-btn active" data-tab="hooks">Hooks</button>
        <button class="tab-btn" data-tab="api-calls">API Calls</button>
    </div>

    <div class="debug-content">
        <div id="hooks-tab" class="tab-content active">
            <table>
                <thead>
                    <tr>
                        <th>Hook Name</th>
                        <th>Priority</th>
                        <th>Callback</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="hooks-list">
                    <!-- Filled by JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="api-calls-tab" class="tab-content">
            <table>
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Data</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody id="api-calls-list">
                    <!-- Filled by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="debug-console.js"></script>
<link rel="stylesheet" href="style.css">
