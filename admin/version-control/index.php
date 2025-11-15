<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/header.php';

$title = 'Version Control';
?><div class="container-fluid">
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Content Versions</div>
                <div class="card-body">
                    <table class="version-table">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Modified</th>
                                <th>Author</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="version-list">
                            <tr>
                                <td colspan="4">Loading versions...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Version Actions</div>
                <div class="card-body">
                    <div class="form-group">
                        <button id="create-version" class="btn btn-primary">Create New Version</button>
                    </div>
                    <div id="version-diff-preview" class="hidden">
                        <h5>Version Comparison</h5>
                        <div class="diff-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load versions
    $.get('/api/version/list', function(data) {
        if (data.success) {
            renderVersionList(data.versions);
        }
    });

    function renderVersionList(versions) {
        let html = '';
        versions.forEach(version => {
            html += `
                <tr>
                    <td>${version.number}</td>
                    <td>${version.modified}</td>
                    <td>${version.author}</td>
                    <td>
                        <button onclick="viewVersion(${version.id})">View</button>
                        <button onclick="restoreVersion(${version.id})">Restore</button>
                        <button onclick="compareVersion(${version.id})">Compare</button>
                    </td>
                </tr>
            `;
        });
        $('#version-list').html(html);
    }

    $('#create-version').click(function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        $.ajax({
            url: '/api/version/create',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(data) {
            if (data.success) {
                loadVersions();
            }
        });
    });
});

function viewVersion(id) {
    // Implementation for viewing a version
}

function restoreVersion(id) {
    if (!confirm('Are you sure you want to restore this version?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    $.ajax({
        url: '/api/version/restore',
        method: 'POST',
        data: { id: id },
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        success: function(data) {
            if (data.success) {
                alert('Version restored successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        },
        error: function(xhr) {
            alert('Request failed: ' + xhr.statusText);
        }
    });
}

function compareVersion(id) {
    const currentVersion = $('#version-list').data('current-version');
    if (!currentVersion) {
        alert('Please select a version to compare with');
        return;
    }

    $.get('/api/version/diff', {
        version1: currentVersion,
        version2: id
    }, function(data) {
        if (data.success) {
            $('#version-diff-preview').removeClass('hidden');
            $('.diff-container').html(
                `
<div class="diff-left">${data.diff.left}</div>
                 <div class="diff-right">${data.diff.right}</div>`
            );
            // Initialize diff highlighting
            if (typeof Diff !== 'undefined') {
                Diff.highlightChanges('.diff-container');
            }
        } else {
            alert('Comparison failed: ' + data.message);
        }
    });
}
?></script>

require_once __DIR__ . '/../../includes/footer.php';
