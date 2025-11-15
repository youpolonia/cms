<?php
declare(strict_types=1);

class TenantVersionMiddleware {
    /**
     * Verifies tenant ownership of content
     * @throws AccessDeniedException When ownership cannot be verified
     */
    public static function verifyOwnership(int $contentId): void {
        $tenantId = TenantAuth::currentTenantId();
        
        $stmt = db()->prepare(
            "SELECT 1 FROM tenant_content 
             WHERE tenant_id = ? AND content_id = ?
             LIMIT 1"
        );
        $stmt->execute([$tenantId, $contentId]);
        
        if (!$stmt->fetchColumn()) {
            error_log("Tenant $tenantId attempted to access unauthorized content $contentId");
            throw new AccessDeniedException('Content not owned by tenant');
        }
    }

    /**
     * Middleware handler for route integration
     */
    public static function handle(int $contentId): void {
        self::verifyOwnership($contentId);
    }
}
