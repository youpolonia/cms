<?php
require_once __DIR__ . '/../DB/connect.php';

function content_get(PDO $pdo, int $content_id, string $user_access_level): array {
    $result = [
        'status' => false,
        'data' => null,
        'error' => null
    ];

    try {
        // Prepare query with access level check
        $stmt = $pdo->prepare("
            SELECT * FROM contents
            WHERE id = :content_id
            AND (status = 'published' OR :user_access_level IN ('admin', 'moderator'))
        ");
        
        $stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_access_level', $user_access_level, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $content = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($content) {
                $result['status'] = true;
                $result['data'] = $content;
            } else {
                $result['error'] = 'Content not found or access denied';
            }
        }
    } catch (PDOException $e) {
        error_log("ContentService Error: " . $e->getMessage());
        $result['error'] = 'Database error occurred';
    }

    return $result;
}

function content_create(PDO $pdo, array $content_data, string $user_access_level): array {
    $result = [
        'status' => false,
        'data' => null,
        'error' => null
    ];

    // Validate required fields
    $required = ['title', 'content', 'author_id'];
    foreach ($required as $field) {
        if (empty($content_data[$field])) {
            $result['error'] = "Missing required field: $field";
            return $result;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO contents
            (title, content, author_id, status, created_at, updated_at)
            VALUES (:title, :content, :author_id, :status, NOW(), NOW())
        ");

        // Set default status if not provided
        $status = $content_data['status'] ?? 'draft';

        $stmt->bindParam(':title', $content_data['title'], PDO::PARAM_STR);
        $stmt->bindParam(':content', $content_data['content'], PDO::PARAM_STR);
        $stmt->bindParam(':author_id', $content_data['author_id'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $content_id = $pdo->lastInsertId();
            $result['status'] = true;
            $result['data'] = ['id' => $content_id];
        }
    } catch (PDOException $e) {
        error_log("ContentService Error: " . $e->getMessage());
        $result['error'] = 'Failed to create content';
    }

    return $result;
}

function content_update(PDO $pdo, int $content_id, array $content_data, string $user_access_level): array {
    $result = [
        'status' => false,
        'data' => null,
        'error' => null
    ];

    // Check if content exists and user has permission
    $existing = content_get($pdo, $content_id, $user_access_level);
    if (!$existing['status']) {
        $result['error'] = $existing['error'] ?? 'Content not found or access denied';
        return $result;
    }

    try {
        // Build update query dynamically based on provided fields
        $updates = [];
        $params = [':id' => $content_id];

        $allowed_fields = ['title', 'content', 'status'];
        foreach ($allowed_fields as $field) {
            if (isset($content_data[$field])) {
                $updates[] = "$field = :$field";
                $params[":$field"] = $content_data[$field];
            }
        }

        if (empty($updates)) {
            $result['error'] = 'No valid fields to update';
            return $result;
        }

        $query = "UPDATE contents SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $pdo->prepare($query);

        foreach ($params as $key => $value) {
            $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $param_type);
        }

        if ($stmt->execute()) {
            $result['status'] = true;
            $result['data'] = ['id' => $content_id, 'updated' => $stmt->rowCount() > 0];
        }
    } catch (PDOException $e) {
        error_log("ContentService Error: " . $e->getMessage());
        $result['error'] = 'Failed to update content';
    }

    return $result;
}
