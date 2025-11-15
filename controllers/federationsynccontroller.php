<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Content Federation Sync Controller
 */
class FederationSyncController {
    private FederationService $federationService;
    private ContentVersionService $versionService;

    public function __construct(
        FederationService $federationService,
        ContentVersionService $versionService
    ) {
        $this->federationService = $federationService;
        $this->versionService = $versionService;
    }

    public function shareContent(array $request): array {
        if (empty($request['content_id']) || empty($request['target_tenants'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        return [
            'status' => 'success',
            'data' => $this->federationService->shareContent(
                $request['content_id'],
                $request['target_tenants'],
                $request['options'] ?? []
            )
        ];
    }

    public function listFederatedContent(array $request): array {
        $contentId = $request['content_id'] ?? null;
        $tenantId = $request['tenant_id'] ?? null;

        return [
            'status' => 'success',
            'data' => $this->federationService->listFederatedContent($contentId, $tenantId)
        ];
    }

    public function syncContent(array $request): array {
        if (empty($request['content_id']) || empty($request['tenant_ids'])) {
            throw new InvalidArgumentException("Missing required fields");
        }

        return [
            'status' => 'success',
            'data' => $this->federationService->syncContent(
                $request['content_id'],
                $request['tenant_ids']
            )
        ];
    }
}
