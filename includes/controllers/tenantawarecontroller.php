<?php

namespace Includes\Controllers;

use Includes\Database\TenantContext;
use Includes\Database\TenantAwareQueryBuilder;
use Includes\RoutingV2\Middleware\TenantDetectionMiddleware;
use InvalidArgumentException;

abstract class TenantAwareController {
    protected TenantAwareQueryBuilder $db;
    protected ?int $currentTenantId = null;

    public function __construct() {
        $this->currentTenantId = TenantContext::getCurrentTenantId();
        $this->db = new TenantAwareQueryBuilder();
    }

    protected function requireTenant(): void {
        if ($this->currentTenantId === null) {
            throw new InvalidArgumentException('Tenant context required');
        }
    }

    protected function validateTenantAccess(int $contentTenantId): void {
        if ($this->currentTenantId !== $contentTenantId) {
            throw new InvalidArgumentException('Cross-tenant access not allowed');
        }
    }

    protected function getCurrentSiteId(): int {
        return TenantDetectionMiddleware::getCurrentSiteId();
    }

    protected function getTenantQueryBuilder(): TenantAwareQueryBuilder {
        return $this->db;
    }
}
