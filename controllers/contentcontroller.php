<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../modules/content/services/templaterenderer.php';

/**
 * Content Management API Controller
 */
class ContentController {
    private ContentService $contentService;
    private ContentStateService $stateService;
    private TemplateRenderer $templateRenderer;

    public function __construct(
        ContentService $contentService,
        ContentStateService $stateService,
        TemplateRenderer $templateRenderer
    ) {
        $this->contentService = $contentService;
        $this->stateService = $stateService;
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * List all content (admin view)
     */
    public function listContent() {
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $content = $this->contentService->getAllContent($tenantId);
        require_once __DIR__ . '/../views/admin/content/index.php';
    }

    /**
     * Show content by slug
     */
    public function showContent(string $slug) {
        $content = $this->contentService->getContentBySlug($slug);
        
        if (!$content) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        echo $this->templateRenderer->render('content', ['content' => $content]);
    }

    public function createContent(array $request): array {
        if (empty($request['title']) || empty($request['content'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        $tenantId = $request['tenant_id'] ?? null;
        $userId = $request['user_id'] ?? 0;

        return [
            'status' => 'success',
            'data' => $this->contentService->createContent(
                $request['title'],
                $request['content'],
                $userId,
                $tenantId
            )
        ];
    }

    public function getContent(array $request): array {
        if (empty($request['content_id'])) {
            throw new InvalidArgumentException("Missing content_id");
        }

        $contentId = (int)$request['content_id'];
        $tenantId = $request['tenant_id'] ?? null;

        $content = $this->contentService->getContent($contentId, $tenantId);
        if (!$content) {
            throw new RuntimeException("Content not found");
        }

        return [
            'status' => 'success',
            'data' => $content
        ];
    }

    public function updateContent(array $request): array {
        if (empty($request['content_id'])) {
            throw new InvalidArgumentException("Missing content_id");
        }

        $contentId = (int)$request['content_id'];
        $tenantId = $request['tenant_id'] ?? null;
        $updates = $request['updates'] ?? [];

        return [
            'status' => 'success',
            'data' => $this->contentService->updateContent(
                $contentId,
                $updates,
                $tenantId
            )
        ];
    }

    public function changeContentState(array $request): array {
        if (empty($request['content_id']) || empty($request['target_state'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        $contentId = (int)$request['content_id'];
        $targetState = $request['target_state'];
        $tenantId = $request['tenant_id'] ?? null;
        $userId = $request['user_id'] ?? 0;
        $notes = $request['notes'] ?? '';

        return [
            'status' => 'success',
            'data' => $this->stateService->changeContentState(
                $contentId,
                $targetState,
                $userId,
                $tenantId,
                $notes
            )
        ];
    }

    public function getContentState(array $request): array {
        if (empty($request['content_id'])) {
            throw new InvalidArgumentException("Missing content_id");
        }

        $contentId = (int)$request['content_id'];
        $tenantId = $request['tenant_id'] ?? null;

        $state = $this->stateService->getContentState($contentId, $tenantId);
        if (!$state) {
            throw new RuntimeException("Content state not found");
        }

        return [
            'status' => 'success',
            'data' => $state
        ];
    }

    public function getContentStateHistory(array $request): array {
        if (empty($request['content_id'])) {
            throw new InvalidArgumentException("Missing content_id");
        }

        $contentId = (int)$request['content_id'];
        $tenantId = $request['tenant_id'] ?? null;
        $limit = $request['limit'] ?? 50;

        return [
            'status' => 'success',
            'data' => $this->stateService->getStateHistory(
                $contentId,
                $tenantId,
                $limit
            )
        ];
    }

    // Admin web interface methods
    public function index() {
        // Get all content for current tenant
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $content = $this->contentService->getAllContent($tenantId);
        require_once __DIR__ . '/../views/admin/content/index.php';
    }

    /**
     * Show public content page by slug
     */
    public function showPage(string $slug) {
        $content = $this->contentService->getContentBySlug($slug);
        
        if (!$content) {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Check if content is published
        if (!$this->contentService->isContentPublished($content['id'])) {
            http_response_code(403);
            require_once __DIR__ . '/../views/errors/403.php';
            return;
        }

        // Render with theme template
        $this->renderWithTheme('page', $content);
    }

    /**
     * Show paginated blog listing
     */
    public function showBlog() {
        $page = max(1, $_GET['page'] ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $posts = $this->contentService->getPublishedBlogPosts($perPage, $offset);
        $totalPosts = $this->contentService->countPublishedBlogPosts();
        
        $this->renderWithTheme('blog/index', [
            'posts' => $posts,
            'pagination' => [
                'current' => $page,
                'total' => ceil($totalPosts / $perPage),
                'path' => '/blog'
            ]
        ]);
    }

    /**
     * Show single blog post
     */
    public function showBlogPost(string $slug) {
        $post = $this->contentService->getBlogPostBySlug($slug);
        
        if (!$post || !$this->contentService->isContentPublished($post['id'])) {
            http_response_code(404);
            $this->renderWithTheme('errors/404');
            return;
        }

        $this->renderWithTheme('blog/single', ['post' => $post]);
    }

    /**
     * Render content with theme template
     */
    private function renderWithTheme(string $template, array $data = []) {
        $theme = $_ENV['THEME'] ?? 'default';
        $themeDir = __DIR__ . "/../../public/themes/{$theme}";
        
        if (!is_dir($themeDir)) {
            // Fallback to default theme
            $themeDir = __DIR__ . "/../../public/themes/default";
        }

        $renderer = new TemplateRenderer($themeDir);
        $data['this'] = $renderer;
        $renderer->extend("{$template}.php");
    }

    public function create() {
        // Show create form
        require_once __DIR__ . '/../views/admin/content/create.php';
    }

    public function store() {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        // Process form submission
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? 0;
        $content = $this->contentService->createContent(
            $_POST['title'],
            $_POST['content'],
            $userId,
            $tenantId
        );

        header("Location: /admin/content");
        exit;
    }

    public function edit(int $id) {
        // Get content by ID
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $content = $this->contentService->getContent($id, $tenantId);
        require_once __DIR__ . '/../views/admin/content/edit.php';
    }

    public function update(int $id) {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        // Process update
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $this->contentService->updateContent($id, [
            'title' => $_POST['title'],
            'content' => $_POST['content']
        ], $tenantId);

        // Clear cached previews
        $cacheKey = "preview_{$tenantId}_{$id}";
        if (isset($this->cache) && is_object($this->cache) && method_exists($this->cache, 'delete')) {
            $this->cache->delete($cacheKey);
        }

        header("Location: /admin/content");
        exit;
    }

    public function delete(int $id) {
        require_once __DIR__ . '/../core/csrf.php';
        csrf_validate_or_403();

        // Process deletion
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $this->contentService->deleteContent($id, $tenantId);

        // Clear cached previews
        $cacheKey = "preview_{$tenantId}_{$id}";
        if (isset($this->cache) && is_object($this->cache) && method_exists($this->cache, 'delete')) {
            $this->cache->delete($cacheKey);
        }

        header("Location: /admin/content");
        exit;
    }
}
