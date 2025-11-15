<?php
/**
 * Handles media processing API endpoints
 */

namespace API\v1\Controllers;

require_once __DIR__ . '/../../../includes/ai/aimanager.php'; // Added for AI\AIManager

use AI\MediaGallery\MediaProcessor;
use Database\DatabaseConnection;
use API\Response;
use API\RateLimiter;

class MediaController {
    private $mediaProcessor;
    private $rateLimiter;

    public function __construct(DatabaseConnection $db) {
        $this->mediaProcessor = new MediaProcessor($db, new \AI\AIManager());
        $this->rateLimiter = new RateLimiter($db, 'media_processing', 10); // 10 requests/minute
    }

    /**
     * POST /media/process
     * Queues media for AI processing
     */
    public function processMedia(array $request): void {
        if (!$this->rateLimiter->checkLimit($request['user_id'])) {
            Response::json(['error' => 'Rate limit exceeded'], 429);
            return;
        }

        $mediaId = $request['params']['id'] ?? 0;
        $filePath = $request['params']['file_path'] ?? '';

        if (empty($mediaId) || empty($filePath)) {
            Response::json(['error' => 'Missing required parameters'], 400);
            return;
        }

        $result = $this->mediaProcessor->processMedia($mediaId, $filePath);

        if ($result['success']) {
            Response::json([
                'status' => 'completed',
                'media_id' => $mediaId,
                'metadata' => $result['metadata']
            ]);
        } else {
            Response::json([
                'status' => 'failed',
                'error' => $result['error']
            ], 500);
        }
    }

    /**
     * GET /media/status/{id}
     * Checks processing status
     */
    public function getStatus(array $request): void {
        $mediaId = $request['params']['id'] ?? 0;
        
        if (empty($mediaId)) {
            Response::json(['error' => 'Missing media ID'], 400);
            return;
        }

        // In a real implementation, we'd check a job queue or database status
        // For now, we'll just return a mock response
        Response::json([
            'media_id' => $mediaId,
            'status' => 'completed',
            'processed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * GET /media/tags/{id}
     * Gets AI-generated tags
     */
    public function getTags(array $request): void {
        $mediaId = $request['params']['id'] ?? 0;
        
        if (empty($mediaId)) {
            Response::json(['error' => 'Missing media ID'], 400);
            return;
        }

        // In a real implementation, we'd query the database
        // For now, we'll return mock data
        Response::json([
            'media_id' => $mediaId,
            'tags' => ['nature', 'outdoors', 'landscape'],
            'colors' => ['#4a6b82', '#a3b8cc', '#e6e6e6'],
            'objects' => ['tree', 'sky', 'mountain']
        ]);
    }
}
