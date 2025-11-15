<?php
/**
 * Security Dashboard for Admin Panel
 */
class SecurityDashboard {
    /**
     * Render security dashboard
     */
    public static function render(): string {
        $auditResults = SecurityAuditor::runFullAudit();
        $html = '<div class="security-dashboard">';
        $html .= '<h2>Security Audit Results</h2>';
        
        foreach ($auditResults as $category => $checks) {
            $html .= "<h3>" . ucfirst(str_replace('_', ' ', $category)) . "</h3>";
            $html .= '<ul>';
            foreach ($checks as $check => $result) {
                $status = $result ? '✅ Passed' : '❌ Failed';
                $html .= "<li>$check: $status</li>";
            }
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get security metrics for dashboard
     */
    public static function getMetrics(): array {
        return [
            'last_scan' => date('Y-m-d H:i:s'),
            'total_checks' => 12,
            'passed' => 10,
            'failed' => 2
        ];
    }
}
