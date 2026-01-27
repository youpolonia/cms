/**
 * Batch Management Interface
 * 
 * Provides UI for managing batch processing jobs
 */
require_once __DIR__ . '/../../includes/auth/admin-check.php';
require_once __DIR__ . '/admin_header.php';

?><div class="container-fluid">
    <h1 class="mb-4">Batch Processing Management</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Active Batches</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="batchTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="batchTableBody">
                        <!-- Filled via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Create New Batch</h6>
        </div>
        <div class="card-body">
            <form id="batchCreateForm">
                <div class="form-group">
                    <label for="batchName">Batch Name</label>
                    <input type="text" class="form-control" id="batchName"
 required>
?>                </div>
                <div class="form-group">
                    <label for="batchType">Batch Type</label>
                    <select class="form-control" id="batchType"
 required>
                        <option value="">Select Type</option>
                        <option value="content_import">Content Import</option>
                        <option value="image_processing">Image Processing</option>
                        <option value="data_export">Data Export</option>
                    </select>
?>                </div>
                <div class="form-group">
                    <label for="batchFile">Input File</label>
                    <input type="file" class="form-control-file" id="batchFile"
 required>
?>                </div>
                <button type="submit" class="btn btn-primary">Create Batch</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; 
?><script>
$(document).ready(function() {
    // Load batch data
    loadBatchData();
    
    // Form submission
    $('#batchCreateForm').submit(function(e) {
        e.preventDefault();
        createNewBatch();
    });
});

function loadBatchData() {
    $.ajax({
        url: '/api/v1/batch/list',
        method: 'GET',
        success: function(response) {
            $('#batchTableBody').empty();
            response.data.forEach(function(batch) {
                $('#batchTableBody').append(`
                    <tr>
                        <td>${batch.id}</td>
                        <td>${batch.name}</td>
                        <td><span class="badge ${getStatusBadgeClass(batch.status)}">${batch.status}</span></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: ${batch.progress}%" 
                                    aria-valuenow="${batch.progress}" aria-valuemin="0" aria-valuemax="100">
                                    ${batch.progress}%
                                </div>
                            </div>
                        </td>
                        <td>${batch.created_at}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewBatchDetails(${batch.id})">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            ${batch.status === 'queued' ? 
                                `<button class="btn btn-sm btn-danger" onclick="cancelBatch(${batch.id})">
                                    <i class="fas fa-times"></i>
                                </button>` : ''}
                        </td>
                    </tr>
                `);
            });
        }
    });
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'completed': return 'badge-success';
        case 'processing': return 'badge-primary';
        case 'queued': return 'badge-secondary';
        case 'failed': return 'badge-danger';
        default: return 'badge-warning';
    }
}

function createNewBatch() {
    const formData = new FormData();
    formData.append('name', $('#batchName').val());
    formData.append('type', $('#batchType').val());
    formData.append('file', $('#batchFile')[0].files[0]);
    
    $.ajax({
        url: '/api/v1/batch/create',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            alert('Batch created successfully!');
            loadBatchData();
            $('#batchCreateForm')[0].reset();
        },
        error: function(xhr) {
            alert('Error: ' + xhr.responseJSON.message);
        }
    });
}

function viewBatchDetails(batchId) {
    window.location.href = `/admin/batch/details?id=${batchId}`;
}

function cancelBatch(batchId) {
    if(confirm('Are you sure you want to cancel this batch?')) {
        $.ajax({
            url: `/api/v1/batch/cancel/${batchId}`,
            method: 'POST',
            success: function() {
                loadBatchData();
            }
        });
    }
}
</script>
