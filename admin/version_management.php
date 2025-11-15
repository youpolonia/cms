<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Version Management</title>
    <link rel="stylesheet" href="/admin/css/version-management.css">
    <script src="/admin/js/version-management.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>Version Management</h1>
        
        <div class="content-selector">
            <label for="content-select">Select Content:</label>
            <select id="content-select">
                <option value="">-- Select Content --</option>
            </select>
        </div>

        <div class="version-controls">
            <button id="refresh-btn" class="btn">Refresh</button>
            <button id="compare-btn" class="btn" disabled>Compare Versions</button>
            <button id="rollback-btn" class="btn danger" disabled>Rollback</button>
        </div>

        <div class="version-list-container">
            <table id="version-list">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Version</th>
                        <th>Created By</th>
                        <th>Date</th>
                        <th>Rollback Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="comparison-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Version Comparison</h2>
                <div class="comparison-container">
                    <div class="version-display">
                        <h3>Version <span id="version1-number"></span></h3>
                        <pre id="version1-content"></pre>
                    </div>
                    <div class="version-display">
                        <h3>Version <span id="version2-number"></span></h3>
                        <pre id="version2-content"></pre>
                    </div>
                </div>
                <div class="diff-container">
                    <h3>Differences</h3>
                    <div id="diff-results"></div>
                </div>
            </div>
        </div>

        <div id="rollback-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Confirm Rollback</h2>
                <p>You are about to rollback to version <span id="rollback-version"></span></p>
                <label for="rollback-notes">Notes:</label>
                <textarea id="rollback-notes" rows="4"></textarea>
                <div class="modal-actions">
                    <button id="confirm-rollback" class="btn danger">Confirm Rollback</button>
                    <button id="cancel-rollback" class="btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
