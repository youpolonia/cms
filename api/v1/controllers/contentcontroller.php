<?php

namespace Api\v1\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ContentController
{
    private $versionedScheduleService;
    private $schedulingPermissionService;

    public function __construct(
        VersionedScheduleService $versionedScheduleService,
        SchedulingPermissionService $schedulingPermissionService
    ) {
        $this->versionedScheduleService = $versionedScheduleService;
        $this->schedulingPermissionService = $schedulingPermissionService;
    }

    public function checkVersionConflicts(Request $request, Response $response): Response
    {
        $request = $request->withAttribute('permission', 'content_schedule');
        $contentId = (int)$request->getParam('id');
        $data = $request->getParsedBody();
        $userId = (int)$request->getAttribute('user_id');
        $tenantId = (int)$request->getAttribute('tenant_id');

        try {
            if (empty($data['version_id']) || empty($data['publish_at'])) {
                throw new \InvalidArgumentException('Missing required fields');
            }
            
            $versions = DBSupport::getContentVersions($tenantId, $contentId);
            $conflictData = ConflictResolutionService::checkVersionConflicts(
                $versions['current'],
                $versions['pending']
            );
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $conflictData,
                'error' => null
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }

    /**
     * Resolve version scheduling conflicts
     */
    public function resolveVersionConflict(Request $request, Response $response): Response
    {
        $request = $request->withAttribute('permission', 'content_schedule');
        $contentId = (int)$request->getParam('id');
        $data = $request->getParsedBody();
        $userId = (int)$request->getAttribute('user_id');

        try {
            // Validate required fields
            if (empty($data['version_id']) || empty($data['publish_at']) || empty($data['resolution_strategy'])) {
                throw new \InvalidArgumentException('Missing required fields');
            }

            // Check scheduling permissions
            if (!$this->schedulingPermissionService->canScheduleVersion($userId, $contentId, (int)$data['version_id'])) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'data' => null,
                    'error' => 'You do not have permission to schedule this version'
                ]));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(403);
            }

            $publishAt = new \DateTime($data['publish_at']);
            $resolution = $this->versionedScheduleService->resolveConflict(
                $contentId,
                (int)$data['version_id'],
                $publishAt,
                $data['resolution_strategy'],
                $userId,
                $data['notes'] ?? null
            );

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $resolution,
                'error' => null
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}
