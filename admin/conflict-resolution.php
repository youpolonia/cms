<?php
// Conflict Resolution Panel
require_once __DIR__ . '/../includes/auth/admin-auth.php';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conflict Resolution</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <style>
        .conflict-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .conflict-history {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .history-item {
            padding: 10px;
            border-bottom: 1px solid #f5f5f5;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .conflict-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .conflict-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-resolved {
            background: #d4edda;
            color: #155724;
        }
        .admin-notes {
            margin-top: 20px;
        }
        .admin-notes textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 80px;
        }
        .conflict-item {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .diff-view {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .version {
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .version.current {
            border-color: #28a745;
        }
        .version.pending {
            border-color: #ffc107;
        }
        .diff-line {
            padding: 2px 5px;
            margin: 1px 0;
        }
        .diff-added {
            background: #d4edda;
        }
        .diff-removed {
            background: #f8d7da;
            text-decoration: line-through;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-approve {
            background: #28a745;
            color: white;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        .btn-merge {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/admin-header.php'; ?>
    <main class="conflict-container">
        <h1>Content Conflicts</h1>
        <div id="conflictsList">
            <!-- Will be populated by JS -->
        </div>
    </main>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <script>
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function confirmAction(action, conflictId) {
            const actionText = action === 'approve' ? 'approve' : 'reject';
            if (confirm(`Are you sure you want to ${actionText} this change?`)) {
                resolveConflict(conflictId, action);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Fetch conflicts from API
            fetch('/api/v1/versions/conflicts')
                .then(response => response.json())
                .then(data => {
                    renderConflicts(data.conflicts);
                });

            function renderConflicts(conflicts) {
                const container = document.getElementById('conflictsList');

                if (conflicts.length === 0) {
                    container.innerHTML = '<p>No content conflicts found.</p>';
                    return;
                }

                conflicts.forEach(conflict => {
                    const item = document.createElement('div');
                    item.className = 'conflict-item';

                    // Create diff view
                    let diffHtml = '';
                    conflict.diff.forEach(diff => {
                        if (diff.added) {
                            diffHtml += `<div class="diff-line diff-added">${diff.value}</div>`;
                        } else if (diff.removed) {
                            diffHtml += `<div class="diff-line diff-removed">${diff.value}</div>`;
                        } else {
                            diffHtml += `
<div class="diff-line">${diff.value}</div>`;
                        }
                    });

                    item.innerHTML = `
                        <div class="conflict-meta">
                            <div>
                                <h3>${conflict.content_title} (ID: ${conflict.content_id})</h3>
                                <p>Conflict between version ${conflict.current_version} and ${conflict.pending_version}</p>
                                <p>Submitted by: ${conflict.submitted_by || 'System'}</p>
                            </div>
                            <div class="conflict-status status-${conflict.status}">
                                ${conflict.status.toUpperCase()}
                            </div>
                        </div>

                        <div class="admin-notes">
                            <h4>Resolution Notes</h4>
                            <textarea id="notes-${conflict.id}" placeholder="Add notes about this resolution...">${conflict.admin_notes || ''}</textarea>
                        </div>
                        
                        <div class="diff-view">
                            <div class="version current">
                                <h4>Current Version (${conflict.current_version})</h4>
                                <div>${conflict.current_content}</div>
                            </div>
                            <div class="version pending">
                                <h4>Pending Version (${conflict.pending_version})</h4>
                                <div>${conflict.pending_content}</div>
                            </div>
                        </div>

                        <div class="diff-container">
                            <h4>Changes</h4>
                            <div>${diffHtml}</div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-approve" onclick="confirmAction('approve', ${conflict.id})">
                                Approve Changes
                            </button>
                            <button class="btn btn-reject" onclick="confirmAction('reject', ${conflict.id})">
                                Reject Changes
                            </button>
                            <button class="btn btn-merge" onclick="showMergeTool(${conflict.id})">
                                Manual Merge
                            </button>
                        </div>

                        ${conflict.history && conflict.history.length > 0 ? `
                        <div class="conflict-history">
                            <h4>Resolution History</h4>
                            ${conflict.history.map(item => `
                                <div class="history-item">
                                    <strong>${new Date(item.timestamp).toLocaleString()}</strong> -
                                    ${item.action} by ${item.admin_name}
                                    ${item.notes ? `<p>${item.notes}</p>` : ''}
                                </div>
                            `).join('')}
                        </div>
                        ` : ''}
                    `;
                    
                    container.appendChild(item);
                });
            }

            window.resolveConflict = function(conflictId, action) {
                const notes = document.getElementById(`notes-${conflictId}`).value;
                showLoading();

                fetch(`/api/v1/versions/conflicts/${conflictId}/resolve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        action: action,
                        notes: notes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error resolving conflict: ' + data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    alert('Network error: ' + error.message);
                });
            };

            window.showMergeTool = function(conflictId) {
                // Would open a more advanced merge interface
                alert('Merge tool would open for conflict ' + conflictId);
            };
        });
    </script>
</body>
</html>
