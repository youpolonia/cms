<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/../../modules/builderengine/blockrenderer.php';
require_once __DIR__ . '/../../plugins/pluginregistry.php';
require_once __DIR__ . '/../../security/OutputSanitizer.php';

header('Content-Type: text/html');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['blockId'])) {
        throw new InvalidArgumentException('Missing blockId parameter');
    }

    $pluginRegistry = new CMS\Plugins\PluginRegistry(new CMS\Plugins\HookManager());
    $sanitizer = new CMS\Security\OutputSanitizer();
    $renderer = new CMS\DeveloperTools\BlockSandbox\BlockRenderer($pluginRegistry, $sanitizer);
    
    echo $renderer->renderBlock($input['blockId']);
} catch (Exception $e) {
    http_response_code(400);
    echo '
<div class="error">Error: ' . htmlspecialchars(
$e->getMessage()) . '</div>';
}
