<?php
/**
 * Users API Endpoint
 * User management (admin only for most operations)
 */

function handle_users(string $method, ?string $id, ?string $action): void
{
    switch ($method) {
        case 'GET':
            if ($id) {
                get_user($id);
            } else {
                list_users();
            }
            break;

        case 'POST':
            create_user();
            break;

        case 'PUT':
            if (!$id) api_error('User ID required', 400);
            update_user($id);
            break;

        case 'DELETE':
            if (!$id) api_error('User ID required', 400);
            delete_user($id);
            break;

        default:
            api_error('Method not allowed', 405);
    }
}

function list_users(): void
{
    try {
        $pdo = \core\Database::connection();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT id, username, email, role, status, created_at, last_login
            FROM users
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Remove sensitive data
        foreach ($users as &$user) {
            unset($user['password']);
        }

        api_response([
            'items' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);

    } catch (Exception $e) {
        api_error('Failed to list users: ' . $e->getMessage(), 500);
    }
}

function get_user(string $id): void
{
    try {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("
            SELECT id, username, email, role, status, created_at, last_login
            FROM users WHERE id = ?
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            api_error('User not found', 404);
        }

        api_response($user);

    } catch (Exception $e) {
        api_error('Failed to get user: ' . $e->getMessage(), 500);
    }
}

function create_user(): void
{
    try {
        $data = get_request_body();

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            api_error('Username, email and password are required', 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            api_error('Invalid email format', 400);
        }

        $pdo = \core\Database::connection();

        // Check unique
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$data['username'], $data['email']]);
        if ($checkStmt->fetch()) {
            api_error('Username or email already exists', 409);
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'] ?? 'user',
            $data['status'] ?? 'active',
        ]);

        api_response([
            'id' => $pdo->lastInsertId(),
            'message' => 'User created successfully',
        ], 201);

    } catch (Exception $e) {
        api_error('Failed to create user: ' . $e->getMessage(), 500);
    }
}

function update_user(string $id): void
{
    try {
        $data = get_request_body();
        $pdo = \core\Database::connection();

        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            api_error('User not found', 404);
        }

        $fields = [];
        $params = [];

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                api_error('Invalid email format', 400);
            }
            $fields[] = 'email = ?';
            $params[] = $data['email'];
        }

        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $params[] = $data['role'];
        }

        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $params[] = $data['status'];
        }

        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            api_error('No fields to update', 400);
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        api_response(['message' => 'User updated successfully']);

    } catch (Exception $e) {
        api_error('Failed to update user: ' . $e->getMessage(), 500);
    }
}

function delete_user(string $id): void
{
    try {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            api_error('User not found', 404);
        }

        api_response(['message' => 'User deleted successfully']);

    } catch (Exception $e) {
        api_error('Failed to delete user: ' . $e->getMessage(), 500);
    }
}
