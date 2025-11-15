<?php
/**
 * Preview Renderer - Handles rendering of content previews with permission checks
 */
require_once CMS_ROOT . '/includes/security/permissionmanager.php';
require_once CMS_ROOT . '/includes/template/templatesystem.php';
require_once CMS_ROOT . '/includes/cache/cacheinterface.php';
require_once CMS_ROOT . '/includes/cache/filecache.php';

class PreviewRenderer {
    protected PermissionManager $permissionManager;
    protected TemplateSystem $templateSystem;
    protected CacheInterface $cache;

    public function __construct() {
        $this->permissionManager = new PermissionManager();
        $this->templateSystem = new TemplateSystem();
        $this->cache = new FileCache(
            CMS_ROOT . '/cache/previews',
            $_SESSION['tenant_id'] ?? 'global'
        );
    }

    /**
     * Render content preview with permission check
     */
    public function renderPreview(array $content): string {
        $accessLevel = $content['access_level'] ?? 'public';
        $userId = $_SESSION['user_id'] ?? 0;

        if (!$this->permissionManager->canAccessContent($userId, $accessLevel)) {
            http_response_code(403);
            return $this->templateSystem->render('errors/403');
        }

        $cacheKey = 'preview_' . md5(json_encode($content));
        
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $rendered = $this->templateSystem->render('preview/content', [
            'content' => $content
        ]);
        
        $this->cache->set($cacheKey, $rendered, 3600); // Cache for 1 hour
        
        return $rendered;
    }
}
