<?php
/**
 * Posts/Blog API Endpoint
 * CRUD operations for blog posts
 */

function handle_posts(string $method, ?string $id, ?string $action): void
{
    switch ($method) {
        case 'GET':
            if ($id) {
                get_post($id);
            } else {
                list_posts();
            }
            break;

        case 'POST':
            create_post();
            break;

        case 'PUT':
            if (!$id) api_error('Post ID required', 400);
            update_post($id);
            break;

        case 'DELETE':
            if (!$id) api_error('Post ID required', 400);
            delete_post($id);
            break;

        default:
            api_error('Method not allowed', 405);
    }
}

function list_posts(): void
{
    try {
        $pdo = \core\Database::connection();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $category = $_GET['category'] ?? null;
        $status = $_GET['status'] ?? 'published';

        $where = ['status = ?'];
        $params = [$status];

        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Get posts
        $sql = "SELECT id, title, slug, excerpt, category, author_id, status,
                       featured_image, created_at, updated_at, published_at
                FROM posts {$whereClause}
                ORDER BY published_at DESC, created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        api_response([
            'items' => $posts,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);

    } catch (Exception $e) {
        api_error('Failed to list posts: ' . $e->getMessage(), 500);
    }
}

function get_post(string $id): void
{
    try {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? OR slug = ?");
        $stmt->execute([$id, $id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            api_error('Post not found', 404);
        }

        api_response($post);

    } catch (Exception $e) {
        api_error('Failed to get post: ' . $e->getMessage(), 500);
    }
}

function create_post(): void
{
    try {
        $data = get_request_body();

        if (empty($data['title'])) {
            api_error('Title is required', 400);
        }

        $pdo = \core\Database::connection();

        $slug = $data['slug'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']));

        $stmt = $pdo->prepare("
            INSERT INTO posts (title, slug, content, excerpt, category, author_id, status,
                              featured_image, meta_title, meta_description, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $data['title'],
            $slug,
            $data['content'] ?? '',
            $data['excerpt'] ?? '',
            $data['category'] ?? null,
            $data['author_id'] ?? 1,
            $data['status'] ?? 'draft',
            $data['featured_image'] ?? null,
            $data['meta_title'] ?? $data['title'],
            $data['meta_description'] ?? '',
        ]);

        api_response([
            'id' => $pdo->lastInsertId(),
            'slug' => $slug,
            'message' => 'Post created successfully',
        ], 201);

    } catch (Exception $e) {
        api_error('Failed to create post: ' . $e->getMessage(), 500);
    }
}

function update_post(string $id): void
{
    try {
        $data = get_request_body();
        $pdo = \core\Database::connection();

        $checkStmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            api_error('Post not found', 404);
        }

        $fields = [];
        $params = [];

        $allowedFields = ['title', 'slug', 'content', 'excerpt', 'category',
                          'status', 'featured_image', 'meta_title', 'meta_description'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            api_error('No fields to update', 400);
        }

        // Handle publish
        if (isset($data['status']) && $data['status'] === 'published') {
            $fields[] = 'published_at = COALESCE(published_at, NOW())';
        }

        $fields[] = 'updated_at = NOW()';
        $params[] = $id;

        $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        api_response(['message' => 'Post updated successfully']);

    } catch (Exception $e) {
        api_error('Failed to update post: ' . $e->getMessage(), 500);
    }
}

function delete_post(string $id): void
{
    try {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            api_error('Post not found', 404);
        }

        api_response(['message' => 'Post deleted successfully']);

    } catch (Exception $e) {
        api_error('Failed to delete post: ' . $e->getMessage(), 500);
    }
}
