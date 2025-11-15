<?php
require_once __DIR__ . '/../../includes/security.php';
verifyAdminAccess('users.view');

// Get filter parameters
$searchTerm = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';
$page = $_GET['page'] ?? 1;
$perPage = 20;

// Initialize empty results
$users = [];
$totalUsers = 0;
$totalPages = 1;

// Only query if this is not an AJAX request
if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    try {
        $db = getDatabaseConnection();
        
        // Base query
        $query = "SELECT u.id, u.username, u.email, u.status, r.name as role_name 
                  FROM users u
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if ($searchTerm) {
            $query .= " AND (u.username LIKE ? OR u.email LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
        }
        
        if ($role) {
            $query .= " AND r.name = ?";
            $params[] = $role;
        }
        
        if ($status) {
            $query .= " AND u.status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countQuery = "SELECT COUNT(*) FROM ($query) AS total";
        $stmt = $db->prepare($countQuery);
        $stmt->execute($params);
        $totalUsers = $stmt->fetchColumn();
        $totalPages = ceil($totalUsers / $perPage);
        
        // Apply pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        
        // Get results
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("User search error: " . $e->getMessage());
    }
}
?><div class="container">
    <h2>User Search</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form id="userSearchForm" method="get">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="searchTerm">Search Term</label>
                            <input type="text" class="form-control" id="searchTerm" name="q" 
                                   value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Name or email">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="roleFilter">Role</label>
                            <select class="form-control" id="roleFilter" name="role">
                                <option value="">All Roles</option>
                                <?php foreach (getAllRoles() as $roleOption): ?>                                    <option value="<?= $roleOption['name'] ?>" <?= $roleOption['name'] === $role ? 'selected' : '' ?>>
                                        <?= $roleOption['name']  ?>
                                    </option>
                                <?php endforeach;  ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="statusFilter">Status</label>
                            <select class="form-control" id="statusFilter" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div id="userResults">
        <?php require_once __DIR__ . '/_user_results.php'; 
?>    </div>
</div>

<script>
$(document).ready(function() {
    // Handle form submission via AJAX
    $('#userSearchForm').on('submit', function(e) {
        e.preventDefault();
        loadUserResults();
    });
    
    // Handle filter changes
    $('#roleFilter, #statusFilter').on('change', function() {
        loadUserResults();
    });
    
    function loadUserResults() {
        const formData = $('#userSearchForm').serialize();
        
        $.ajax({
            url: window.location.pathname,
            data: formData,
            beforeSend: function() {
                $('#userResults').html('
<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            },
            success: function(data) {
                $('#userResults').html(data);
            },
            error: function() {
                $('#userResults').html('<div class="alert alert-danger">Error loading results</div>');
            }
        });
    }
});
</script>
