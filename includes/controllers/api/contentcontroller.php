<?php
/**
 * API Content Controller
 * 
 * Handles content-related API endpoints
 */

namespace Includes\Controllers\Api;

use Includes\Routing\Request;
use Includes\Routing\Response;

class ContentController
{
    /**
     * Create new content
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function createContent(Request $request, Response $response): void
    {
        $data = $request->getBody();
        
        if (empty($data['title'])) {
            $response->json(['error' => 'Title is required'], 400);
            return;
        }

        // In a real implementation, we would save to database here
        $contentId = uniqid();
        
        $response->json([
            'id' => $contentId,
            'title' => $data['title'],
            'status' => 'created'
        ], 201);
    }

    /**
     * Get content by ID
     * 
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function getContent(Request $request, Response $response): void
    {
        $contentId = $request->getParam('id');
        
        if (empty($contentId)) {
            $response->json(['error' => 'Content ID is required'], 400);
            return;
        }

        // Mock content data - in real implementation would fetch from database
        $response->json([
            'id' => $contentId,
            'title' => 'Sample Content',
            'body' => 'This is sample content body',
            'created_at' => date('Y-m-d H:i:s')
        ], 200);
    }
}
