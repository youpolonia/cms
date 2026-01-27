<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once CMS_ROOT . '/core/automation_rules.php';
/**
 * Blog Controller - Handles HTTP requests for blog functionality
 */
class BlogController {
    private BlogManager $blogManager;

    public function __construct() {
        $this->blogManager = new BlogManager();
    }

    /**
     * List all blog posts
     */
    public function index(): void {
        $posts = $this->blogManager->getAllPosts();
        require_once __DIR__ . '/../views/blog/listing.php';
    }

    /**
     * Show single blog post
     * @param int $id Post ID
     */
    public function show(int $id): void {
        $post = $this->blogManager->getPostById($id);
        if (!$post) {
            header("HTTP/1.0 404 Not Found");
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }
        require_once __DIR__ . '/../views/blog/post.php';
    }

    /**
     * Show blog post creation form (admin)
     */
    public function create(): void {
        $this->checkAdminAccess();
        require_once __DIR__ . '/../views/admin/blog/create.php';
    }

    /**
     * Store new blog post (admin)
     */
    public function store(): void {
        $this->checkAdminAccess();
        csrf_validate_or_403();

        try {
            $post = $this->blogManager->createPost($_POST);

            if (isset($post) && isset($post->status) && $post->status === 'published') {
                automation_rules_handle_event('blog.post_published', [
                    'post_id'   => $post->id ?? null,
                    'title'     => $post->title ?? '',
                    'slug'      => $post->slug ?? '',
                    'status'    => $post->status,
                    'author_id' => $post->author_id ?? null
                ]);
            }
            header("Location: /blog/{$post->id}");
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /blog/create");
        }
    }

    /**
     * Show blog post edit form (admin)
     * @param int $id Post ID
     */
    public function edit(int $id): void {
        $this->checkAdminAccess();
        $post = $this->blogManager->getPostById($id);
        require_once __DIR__ . '/../views/admin/blog/edit.php';
    }

    /**
     * Update blog post (admin)
     * @param int $id Post ID
     */
    public function update(int $id): void {
        $this->checkAdminAccess();
        csrf_validate_or_403();

        try {
            $post = $this->blogManager->updatePost($id, $_POST);

            if (isset($post) && isset($post->status) && $post->status === 'published') {
                automation_rules_handle_event('blog.post_published', [
                    'post_id'   => $post->id ?? null,
                    'title'     => $post->title ?? '',
                    'slug'      => $post->slug ?? '',
                    'status'    => $post->status,
                    'author_id' => $post->author_id ?? null
                ]);
            }

            header("Location: /blog/{$post->id}");
        } catch (InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /blog/{$id}/edit");
        }
    }

    /**
     * Delete blog post (admin)
     * @param int $id Post ID
     */
    public function destroy(int $id): void {
        $this->checkAdminAccess();
        $this->blogManager->deletePost($id);
        header("Location: /blog");
    }

    /**
     * Check if user has admin access
     * @throws Exception If not authorized
     */
    private function checkAdminAccess(): void {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("HTTP/1.0 403 Forbidden");
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
}
