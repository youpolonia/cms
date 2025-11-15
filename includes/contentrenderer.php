<?php
/**
 * Content Renderer - Handles rendering of all content field types with template inheritance and caching
 */
require_once __DIR__ . '/plugins/hookmanager.php';
require_once __DIR__ . '/FileCache.php';
require_once __DIR__ . '/core/View.php';
require_once __DIR__ . '/../admin/core/services/languageservice.php';

class ContentRenderer {
    private $hookManager;
    private $fileCache;
    private $templateSystem;
    private $fieldRegistry;
    private $languageService;
    private $isAdmin;
    private $permissionManager;

    public function __construct(bool $isAdmin = false) {
        require_once __DIR__ . '/core/permissionmanager.php';
        $this->hookManager = new HookManager();
        $this->fileCache = new FileCache();
        $this->templateSystem = new Core\View();
        $this->languageService = LanguageService::getInstance();
        $this->permissionManager = new PermissionManager();
        $this->loadFieldRegistry();

        // Register cache invalidation hooks
        $this->hookManager->addAction('content.update', [$this, 'handleContentUpdate']);
        $this->hookManager->addAction('template.modify', [$this, 'handleTemplateModify']);
        $this->hookManager->addAction('content.version.change', [$this, 'handleVersionChange']);
    }

    /**
     * Handle content update hook - clears cache for the content ID
     */
    private function handleContentUpdate(string $contentId): void {
        $this->clearContentCache($contentId);
        NotificationTriggers::triggerCacheClearNotification($contentId, 'content_update');
    }

    /**
     * Handle template modify hook - clears cache for the template
     */
    private function handleTemplateModify(string $template): void {
        $this->clearTemplateCache($template);
        NotificationTriggers::triggerCacheClearNotification('system', 'template_modify');
    }

    /**
     * Handle version change hook - clears cache for the content ID
     */
    private function handleVersionChange(string $contentId): void {
        $this->clearContentCache($contentId);
        NotificationTriggers::triggerCacheClearNotification($contentId, 'version_change');
    }

    /**
     * Load field type registry from configuration
     */
    private function loadFieldRegistry() {
        $registryFile = __DIR__ . '/../config_core/theme.php';
        if (!file_exists($registryFile)) {
            error_log("ContentRenderer: Missing field registry at $registryFile");
            $this->fieldRegistry = [];
            return;
        }
        
        $this->fieldRegistry = require_once $registryFile;
        if (!is_array($this->fieldRegistry)) {
            error_log("ContentRenderer: Invalid field registry format");
            $this->fieldRegistry = [];
        }
    }

    /**
     * Render content with template inheritance and caching
     */
    public function render(array $content, string $template = 'default'): string {
        // Content state validation
        if (!$this->isAdmin && ($content['state'] ?? 'draft') !== 'published') {
            throw new Exception('Content not available');
        }

        // Tenant validation
        if (!isset($content['tenant_id'])) {
            throw new Exception('Tenant ID missing in content');
        }
        
        $currentTenant = $_SERVER['HTTP_X_TENANT_KEY'] ?? '';
        if ($content['tenant_id'] !== $currentTenant) {
            throw new Exception("Tenant mismatch - requested: {$content['tenant_id']}, current: $currentTenant");
        }

        // Access level check
        $accessLevel = $content['access_level'] ?? 'public';
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$this->isAdmin && !$this->permissionManager->canAccessContent($userId, $accessLevel)) {
            http_response_code(403);
            return file_get_contents(__DIR__ . '/../../views/errors/403.php');
        }

        error_log("ContentRenderer: Starting render for tenant {$content['tenant_id']} with template '$template'");
        $debugData = [
            'template' => $template,
            'tenant_id' => $content['tenant_id'],
            'fields' => array_keys($content),
            'fieldRegistry' => array_keys($this->fieldRegistry),
            'timestamp' => microtime(true)
        ];
        error_log("ContentRenderer debug: " . json_encode($debugData));

        $cacheKey = $this->generateContentCacheKey($template, $content);
        
        // Try to get cached version first
        $cached = $this->getCachedContent($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Allow plugins to modify content before rendering
        $content = $this->hookManager->executeFilter('before_render', $content);

        $output = '';
        foreach ($content as $field => $value) {
            error_log("Rendering field: $field");
            $output .= $this->renderField($field, $value, $template, $content);
        }

        // Allow plugins to modify output after rendering
        $output = $this->hookManager->executeFilter('after_render', $output);

        // Cache the result
        $this->cacheContent($cacheKey, $output);
        
        
        return $output;
    }

    /**
     * Generate cache key for field rendering
     */
    private function generateFieldCacheKey(string $field, string $template, array $content): string {
        $templatePath = $this->templateSystem->getTemplatePath($template);
        $templateModTime = file_exists($templatePath) ? filemtime($templatePath) : 0;
        
        return 'field_' . ($content['id'] ?? '0') . '_' .
               ($content['version'] ?? '1') . '_' .
               $field . '_' . $template . '_' .
               $templateModTime;
    }

    /**
     * Generate cache key for content rendering
     */
    private function generateContentCacheKey(string $template, array $content): string {
        $templatePath = $this->templateSystem->getTemplatePath($template);
        $templateModTime = file_exists($templatePath) ? filemtime($templatePath) : 0;
        
        return 'content_' . ($content['id'] ?? '0') . '_' .
               ($content['version'] ?? '1') . '_' .
               $template . '_' . $templateModTime;
    }

    /**
     * Get cached content if available
     */
    private function getCachedContent(string $cacheKey): ?string {
        try {
            $cached = $this->fileCache->get($cacheKey);
            if ($cached !== null) {
                error_log("ContentRenderer: Using cached content with key $cacheKey");
                return $cached;
            }
        } catch (Exception $e) {
            error_log("ContentRenderer: Cache read error for content: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Cache rendered content
     */
    private function cacheContent(string $cacheKey, string $output): void {
        if ($output === '') {
            return;
        }
        
        try {
            $this->fileCache->set($cacheKey, $output, 3600);
            error_log("ContentRenderer: Cached content with key $cacheKey");
        } catch (Exception $e) {
            error_log("ContentRenderer: Cache write error for content: " . $e->getMessage());
        }
    }

    /**
     * Clear cache for specific content ID
     */
    public function clearContentCache(string $contentId): void {
        try {
            $this->fileCache->deleteByPrefix('content_' . $contentId . '_');
            error_log("ContentRenderer: Cleared cache for content ID $contentId");
        } catch (Exception $e) {
            error_log("ContentRenderer: Cache clear error for content ID $contentId: " . $e->getMessage());
        }
    }
}
