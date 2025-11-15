<?php
namespace CMS\Services;

use CMS\Services\OTService;
use CMS\Core\CRDT;

class RealTimeCollaborationHandler {
    private $otService;
    private $crdt;
    private $documentVersions = [];

    public function __construct() {
        $this->otService = new OTService();
        $this->crdt = new CRDT();
    }

    public function handleClientOperation(string $documentId, array $clientOp, array $serverOps): array {
        // Transform client operation against all server operations
        $transformedOp = $this->otService->getTransformedOperation($clientOp, $serverOps);
        
        // Apply to CRDT
        $this->crdt->applyOperation($documentId, $transformedOp);
        
        // Store document version
        $this->documentVersions[$documentId] = $this->crdt->getDocument($documentId);
        
        return $transformedOp;
    }

    public function getDocumentVersion(string $documentId): string {
        return $this->documentVersions[$documentId] ?? '';
    }

    public function getTransformedOperations(string $documentId, array $clientOps): array {
        $serverOps = $this->crdt->getOperations($documentId);
        return $this->otService->applyTransformations($clientOps, $serverOps);
    }
}
