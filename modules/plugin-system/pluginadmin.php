<?php
/**
 * Plugin Admin Interface
 */
class PluginAdmin {
    private PluginManager $pluginManager;

    public function __construct(PluginManager $pluginManager) {
        $this->pluginManager = $pluginManager;
    }

    /**
     * Render plugin management page
     */
    public function renderPluginPage(): string {
        $plugins = $this->pluginManager->getPlugins();
        $html = '
<div class="plugin-manager">';
        $html .= '
<h2>Plugin Management</h2>';
        $html .= '
<table class="plugin-table"><thead><tr><th>Plugin</th><th>Version</th><th>Status</th><th>Actions</th></tr></thead><tbody>';

        foreach ($plugins as $pluginDir => $pluginInfo) {
            $html .= sprintf(
                '
<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars(
$pluginInfo['name']),
                htmlspecialchars($pluginInfo['version']),
                $pluginInfo['active'] ? 'Active' : 'Inactive',
                $this->getActionButtons($pluginDir, $pluginInfo['active'])
            );
        }

        $html .= '
</tbody></table></div>';
        return $html;
    }

    /**
     * Get action buttons for a plugin
     */
    private function getActionButtons(string $pluginDir, bool $isActive): string {
        $buttons = '';
        if ($isActive) {
            $buttons .= sprintf('
<button class="deactivate" data-plugin="%s">Deactivate</button>', 
                htmlspecialchars(
$pluginDir));
        } else {
            $buttons .= sprintf('
<button class="activate" data-plugin="%s">Activate</button>', 
                htmlspecialchars(
$pluginDir));
        }
        $buttons .= sprintf('
<button class="delete" data-plugin="%s">Delete</button>', 
            htmlspecialchars(
$pluginDir));
        return $buttons;
    }

    /**
     * Handle plugin actions via AJAX
     */
    public function handleAjaxRequest(array $request): array {
        if (empty($request['action']) || empty($request['plugin'])) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        try {
            switch ($request['action']) {
                case 'activate':
                    // Implementation would call PluginManager
                    return ['success' => true];
                case 'deactivate':
                    // Implementation would call PluginManager
                    return ['success' => true];
                case 'delete':
                    // Implementation would call PluginManager
                    return ['success' => true];
                default:
                    return ['success' => false, 'message' => 'Invalid action'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
