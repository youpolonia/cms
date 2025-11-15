<?php
declare(strict_types=1);

// Security check - only allow direct access if not already in admin
if (php_sapi_name() !== 'cli' && !defined('SETUP_MODE')) {
    die('This script can only be run directly or with SETUP_MODE defined');
}

require_once __DIR__ . '/../config.php';

// Check if users already exist
$db = \core\Database::connection();

function checkExistingData(PDO $db): bool {
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    return $stmt->fetchColumn() > 0;
}

if (checkExistingData($db)) {
    die("Error: Users already exist in database. Setup aborted.\n");
}

try {
    $db->beginTransaction();

    // 1. Create default roles
    $roles = [
        ['name' => 'admin', 'description' => 'Full system administrator'],
        ['name' => 'editor', 'description' => 'Content editor'],
        ['name' => 'viewer', 'description' => 'Read-only access']
    ];

    foreach ($roles as $role) {
        $stmt = $db->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->execute([$role['name'], $role['description']]);
    }

    // 2. Create admin user
    $passwordHash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $db->prepare(
        "INSERT INTO users (username, email, password, created_at) 
        VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute(['admin', 'admin@example.com', $passwordHash]);
    $adminUserId = $db->lastInsertId();

    // 3. Assign admin role to admin user
    $adminRoleId = $db->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();
    $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $stmt->execute([$adminUserId, $adminRoleId]);

    // 4. Assign all permissions to admin role
    $permissions = $db->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($permissions as $permissionId) {
        $stmt = $db->prepare(
            "INSERT INTO role_permissions (role_id, permission_id) 
            VALUES (?, ?)"
        );
        $stmt->execute([$adminRoleId, $permissionId]);
    }

    $db->commit();
    
    echo "Setup completed successfully!\n";
    echo "Admin credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    echo "WARNING: You should immediately:\n";
    echo "1. Log in and change the admin password\n";
    echo "2. Delete this setup file for security\n\n";
    echo "No interactive deletion is performed in this environment.\n";
    echo "Please delete this file manually via FTP or file manager: " . __FILE__ . "\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
