<?php
/**
 * Theme-aware Content Preview Renderer
 */
class PreviewRenderer {
    private $themeHandler;
    private $blockRenderer;

    public function __construct() {
        $this->themeHandler = new ThemeHandler();
        $this->blockRenderer = new BlockRenderer();
    }

    /**
     * Render content with current theme and blocks
     */
    public function renderPreview(array $content, int $userId, string $access_level = 'public'): string {
        $roleManager = RoleManager::getInstance();
        
        // Check access level permissions
        switch($access_level) {
            case 'private':
                if ($userId === 0) {
                    throw new Exception('Access denied - login required');
                }
                break;
            case 'admin':
                if (!$roleManager->hasPermission($userId, 'admin_access')) {
                    throw new Exception('Access denied - admin privileges required');
                }
                break;
            default: // public
                if (!$roleManager->hasPermission($userId, 'view_content')) {
                    throw new Exception('Access denied - insufficient permissions');
                }
        }

        $theme = $this->themeHandler->getActiveTheme();
        $blocks = $this->processBlocks($content['blocks'] ?? []);

        ob_start();
        // Pass context variables to template
        $context = [
            'userId' => $userId,
            'userRole' => $roleManager->getUserRole($userId),
            'content' => $content,
            'blocks' => $blocks,
            'access_level' => $access_level
        ];
        
        $templatePath = $theme->getPreviewTemplate();
        if (!file_exists($templatePath)) {
            throw new Exception("Preview template not found at: $templatePath");
        }
        
        extract($context, EXTR_SKIP);
        require_once $templatePath;
        $output = ob_get_clean();

        return $output;
    }

    private function processBlocks(array $blocks): array {
        $processed = [];
        foreach ($blocks as $block) {
            $processed[] = $this->blockRenderer->render($block);
        }
        return $processed;
    }

    /**
     * Generate preview-specific CSS classes
     */
    private function getPreviewClasses(): string {
        return 'preview-mode preview-draft';
    }
}
