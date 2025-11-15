<?php
/**
 * API v1 Plugin Controller
 */

declare(strict_types=1);

namespace Api\V1\Controllers;

use Includes\Routing\Request;
use Includes\Routing\Response;

class PluginController
{
    /**
     * List installed plugins
     */
    public function index(Request $request, Response $response): void
    {
        $response->json([
            'success' => true,
            'data' => [
                ['id' => 'core-plugin', 'name' => 'Core Plugin', 'version' => '1.0.0'],
                ['id' => 'analytics', 'name' => 'Analytics', 'version' => '1.2.0']
            ],
            'error' => null
        ]);
    }

    /**
     * Install new plugin
     */
    public function store(Request $request, Response $response): void
    {
        $pluginData = $request->getParsedBody();
        // TODO: Validate and install plugin
        
        $response->json([
            'success' => true,
            'data' => $pluginData,
            'error' => null
        ], 201);
    }

    /**
     * Uninstall plugin
     */
    public function destroy(Request $request, Response $response): void
    {
        $pluginId = $request->getParam('id');
        // TODO: Uninstall plugin
        
        $response->json([
            'success' => true,
            'data' => null,
            'error' => null
        ], 204);
    }
}
