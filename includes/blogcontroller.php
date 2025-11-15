<?php
require_once __DIR__ . '/../config.php';

class BlogController {
    public function index() {
        $db = \core\Database::connection();
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get published posts count
        $total = $db->query("
            SELECT COUNT(*) FROM blog_posts 
            WHERE status = 'published' AND published_at <= NOW()
        ")->fetchColumn();

        // Get paginated posts
        $stmt = $db->prepare("
            SELECT * FROM blog_posts 
            WHERE status = 'published' AND published_at <= NOW()
            ORDER BY published_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Render view
        $view = new View('blog/index');
        $view->render([
            'posts' => $posts,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }

    public function show($slug) {
        $db = \core\Database::connection();
        
        $stmt = $db->prepare("
            SELECT * FROM blog_posts 
            WHERE slug = ? AND status = 'published' AND published_at <= NOW()
        ");
        $stmt->execute([$slug]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            http_response_code(404);
            $view = new View('errors/404');
            $view->render();
            return;
        }

        $view = new View('blog/show');
        $view->render(['post' => $post]);
    }
}
