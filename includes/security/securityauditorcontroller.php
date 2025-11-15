<?php
declare(strict_types=1);

namespace Security;

class SecurityAuditorController
{
    public static function handleRequest(array $request): array
    {
        $action = $request['action'] ?? '';
        
        try {
            switch ($action) {
                case 'start_scan':
                    return self::startScan($request);
                case 'get_results':
                    return self::getResults($request);
                case 'list_scans':
                    return self::listAvailableScans();
                default:
                    throw new \InvalidArgumentException("Invalid action: $action");
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    private static function startScan(array $request): array
    {
        if (empty($request['scan_type'])) {
            throw new \InvalidArgumentException('Missing scan_type parameter');
        }

        return SecurityAuditor::runScan($request['scan_type']);
    }

    private static function getResults(array $request): array
    {
        if (empty($request['scan_id'])) {
            throw new \InvalidArgumentException('Missing scan_id parameter');
        }

        return SecurityAuditor::getScanResults($request['scan_id']);
    }

    private static function listAvailableScans(): array
    {
        return [
            'status' => 'success',
            'scans' => SecurityAuditor::getAvailableScans()
        ];
    }
}
