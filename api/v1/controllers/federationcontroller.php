<?php
/**
 * Federation API Controller
 */

declare(strict_types=1);

namespace Api\V1\Controllers;

use Includes\Federation\NodeRegistry;
use Includes\Federation\ContentSync;
use Includes\Http\Response;

class FederationController
{
    private NodeRegistry $nodeRegistry;
    private ContentSync $contentSync;

    public function __construct()
    {
        $this->nodeRegistry = new NodeRegistry();
        $this->contentSync = new ContentSync();
    }

    /**
     * Register a new federation node
     */
    public function register(array $request): Response
    {
        $node = $this->nodeRegistry->registerNode($request);
        return new Response(201, $node);
    }

    /**
     * List all active federation nodes
     */
    public function nodes(): Response
    {
        $nodes = $this->nodeRegistry->getActiveNodes();
        return new Response(200, $nodes);
    }

    /**
     * Push content to federation network
     */
    public function push(array $request): Response
    {
        if (!$this->nodeRegistry->verifyNodeSignature(
            $request['node_id'],
            $request['signature'],
            $request['content_data']
        )) {
            return new Response(401, ['error' => 'Invalid signature']);
        }

        $result = $this->contentSync->pushContent($request['content_data']);
        return new Response(202, $result);
    }

    /**
     * Pull content from federation network
     */
    public function pull(array $request): Response
    {
        $content = $this->contentSync->pullContent($request);
        return new Response(200, $content);
    }

    /**
     * Resolve content conflicts
     */
    public function conflicts(array $request): Response
    {
        $resolution = ConflictResolutionService::resolveVersionConflict($request);
        return new Response(200, $resolution);
    }
}
