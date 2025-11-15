<?php
/**
 * API v1 Settings Controller
 */

declare(strict_types=1);

namespace Api\V1\Controllers;

use Includes\Routing\Request;
use Includes\Routing\Response;

class SettingsController
{
    /**
     * Get system settings
     */
    public function index(Request $request, Response $response): void
    {
        $response->json([
            'success' => true,
            'data' => [
                'site_name' => 'My CMS',
                'timezone' => 'UTC',
                'maintenance_mode' => false
            ],
            'error' => null
        ]);
    }

    /**
     * Update system settings
     */
    public function update(Request $request, Response $response): void
    {
        $settings = $request->getParsedBody();
        // TODO: Validate and save settings
        
        $response->json([
            'success' => true,
            'data' => $settings,
            'error' => null
        ]);
    }
}
