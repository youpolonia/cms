<!-- admin/users/index_view.php -->
<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Users</li>
    </ol>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); 
?>        </div>
    <?php endif; ?>    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMessage); 
?>        </div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            User List
            <a href="create.php" class="btn btn-primary btn-sm float-end">Add New User</a>
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['last_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($user['status'])); ?></td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-sm btn-danger" onclick="
return confirm('Are you sure you want to delete this user?');">Delete</a>
                                    <!-- TODO: Add view profile link -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TODO: Add DataTables JS for sorting/pagination if not already in layout -->
