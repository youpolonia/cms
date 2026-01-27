<?php
/**
 * Pages API Endpoint
 * CRUD operations for pages
 */

function handle_pages(string $method, ?string $id, ?string $action): void
{
    switch ($method) {
        case 'GET':
            if ($id) {
                get_page($id);
            } else {
                list_pages();
            }
            break;

        case 'POST':
            create_page();
            break;

        case 'PUT':
            if (!$id) {
                api_error('Page ID required', 400);
            }
            update_page($id);
            break;

        case 'DELETE':
            if (!$id) {
                api_error('Page ID required', 400);
            }
            delete_page($id);
            break;

        default:
            api_error('Method not allowed', 405);
    }
}

function list_pages(): void
{
    try {
        $pdo = \core\Database::connection();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $status = $_GET['status'] ?? null;

        $where = '';
        $params = [];

        if ($status) {
            $where = 'WHERE status = ?';
            $params[] = $status;
        }

        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM pages {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Get pages
        $sql = "SELECT id, title, slug, status, meta_title, meta_description, created_at, updated_at
                FROM pages {$where}
                ORDER BY updated_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        api_response([
            'items' => $pages,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);

    } catch (Exception $e) {
        api_error('Failed to list pages: ' . $e->getMessage(), 500);
    }
}

function get_page(string $id): void
{
    try {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ? OR slug = ?");
        $stmt->execute([$id, $id]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$page) {
            api_error('Page not found', 404);
        }

        api_response($page);

    } catch (Exception $e) {
        api_error('Failed to get page: ' . $e->getMessage(), 500);
    }
}

function create_page(): void
{
    try {
        $data = get_request_body();

        if (empty($data['title'])) {
            api_error('Title is required', 400);
        }

        $pdo = \core\Database::connection();

        $slug = $data['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']));

        // Check slug uniqueness
        $checkStmt = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if ($checkStmt->fetch()) {
            $slug .= '-' . time();
        }

        $stmt = $pdo->prepare("
            INSERT INTO pages (title, slug, content, status, meta_title, meta_description, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $data['title'],
            $slug,
            $data['content'] ?? '',
            $data['status'] ?? 'draft',
            $data['meta_title'] ?? $data['title'],
            $data['meta_description'] ?? '',
        ]);

        $id = $pdo->lastInsertId();

        api_response([
            'id' => $id,
            'slug' => $slug,
            'message' => 'Page created successfully',
        ], 201);

    } catch (Exception $e) {
        api_error('Failed to create page: ' . $e->getMessage(), 500);
    }
}

function update_page(string $id): void
{
    try {
        $data = get_request_body();
        $pdo = \core\Database::connection();

        // Check exists
        $checkStmt = $pdo->prepare("SELECT id FROM pages WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            api_error('Page not found', 404);
        }

        $fields = [];
        $params = [];

        $allowedFields = ['title', 'slug', 'content', 'status', 'meta_title', 'meta_description'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            api_error('No fields to update', 400);
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $sql = "UPDATE pages SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        api_response(['message' => 'Page updated successfully']);

    } catch (Exception $e) {
        api_error('Failed to update page: ' . $e->getMessage(), 500);
    }
}

function delete_page(string $id): void
{
    try {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            api_error('Page not found', 404);
        }

        api_response(['message' => 'Page deleted successfully']);

    } catch (Exception $e) {
        api_error('Failed to delete page: ' . $e->getMessage(), 500);
    }
}
