<?php
// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    // Re-run the query for AJAX requests
    try {
        $db = getDatabaseConnection();
        
        $query = "SELECT u.id, u.username, u.email, u.status, r.name as role_name 
                  FROM users u
                  LEFT JOIN roles r ON u.role_id = r.id
                  WHERE 1=1";
        
        $params = [];
        
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
        
        // Apply pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "SELECT COUNT(*) FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE 1=1";
        if ($searchTerm) $countQuery .= " AND (u.username LIKE ? OR u.email LIKE ?)";
        if ($role) $countQuery .= " AND r.name = ?";
        if ($status) $countQuery .= " AND u.status = ?";
        
        $stmt = $db->prepare($countQuery);
        $stmt->execute($params);
        $totalUsers = $stmt->fetchColumn();
        $totalPages = ceil($totalUsers / $perPage);
        
    } catch (Exception $e) {
        error_log("User search error: " . $e->getMessage());
    }
}

?><div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info">No users found matching your criteria</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role_name']) ?></td>
                                <td>
                                    <span class="badge badge-<?php
                                        echo $user['status'] === 'active' ? 'success' :
                                        ($user['status'] === 'pending' ? 'warning' : 'danger');
                                    ?>">
                                        <?= ucfirst($user['status'])  ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/admin/users/edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach;  ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="<?= $page - 1 ?>">Previous</a>
                            </li>
                        <?php endif;  ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++):  ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor;  ?>
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="#" data-page="<?= $page + 1 ?>">Next</a>
                            </li>
                        <?php endif;  ?>
                    </ul>
                </nav>
            <?php endif;  ?>        <?php endif;  ?>
    </div>
</div>
