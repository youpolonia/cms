<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class SecurityDashboardController
{
    public function __construct()
    {
        require_once CMS_CORE . '/securityauditor.php';
    }

    public function index(Request $request): void
    {
        $results = \SecurityAuditor::runFullAudit();

        // Calculate stats
        $passed = 0;
        $failed = 0;
        $total = 0;

        foreach ($results as $category => $checks) {
            foreach ($checks as $check => $result) {
                $total++;
                if ($result) {
                    $passed++;
                } else {
                    $failed++;
                }
            }
        }

        // Get last scan time
        $scanFile = \CMS_ROOT . '/storage/security_last_scan.txt';
        $lastScan = file_exists($scanFile) ? file_get_contents($scanFile) : null;

        render('admin/security/index', [
            'results' => $results,
            'stats' => [
                'total' => $total,
                'passed' => $passed,
                'failed' => $failed,
                'score' => $total > 0 ? round(($passed / $total) * 100) : 0
            ],
            'lastScan' => $lastScan,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function scan(Request $request): void
    {
        // Run the audit
        $results = \SecurityAuditor::runFullAudit();

        // Store scan timestamp
        $storageDir = \CMS_ROOT . '/storage';
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
        file_put_contents($storageDir . '/security_last_scan.txt', date('Y-m-d H:i:s'));

        // Calculate results for flash message
        $passed = 0;
        $failed = 0;
        foreach ($results as $category => $checks) {
            foreach ($checks as $check => $result) {
                if ($result) {
                    $passed++;
                } else {
                    $failed++;
                }
            }
        }

        if ($failed === 0) {
            Session::setFlash('success', 'Security scan completed: All ' . $passed . ' checks passed!');
        } else {
            Session::setFlash('error', 'Security scan completed: ' . $failed . ' issue(s) found out of ' . ($passed + $failed) . ' checks.');
        }

        header('Location: /admin/security');
        exit;
    }
}
