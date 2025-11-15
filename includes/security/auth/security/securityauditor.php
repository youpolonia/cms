<?php
/**
 * Security Auditor for CMS
 * Performs automated security checks and audits
 */
class SecurityAuditor {
    private $scanResults = [];
    private $config;

    public function __construct(array $config = []) {
        $this->config = array_merge([
            'check_file_permissions' => true,
            'scan_sql_injection' => true,
            'scan_xss' => true,
            'scan_csrf' => true
        ], $config);
    }

    /**
     * Run full security audit
     */
    public function audit(): array {
        if ($this->config['check_file_permissions']) {
            $this->checkFilePermissions();
        }
        if ($this->config['scan_sql_injection']) {
            $this->scanForSQLInjection();
        }
        if ($this->config['scan_xss']) {
            $this->scanForXSS();
        }
        if ($this->config['scan_csrf']) {
            $this->checkCSRFProtection();
        }
        
        return $this->scanResults;
    }

    private function checkFilePermissions(): void {
        // Check critical file permissions
        $files = [
            '../config/' => 0750,
            '../includes/' => 0750,
            '../database/' => 0750,
            '../memory-bank/' => 0750
        ];

        foreach ($files as $file => $expectedPerms) {
            if (file_exists($file)) {
                $actualPerms = fileperms($file) & 0777;
                if ($actualPerms !== $expectedPerms) {
                    $this->scanResults[] = [
                        'type' => 'file_permission',
                        'file' => $file,
                        'severity' => 'high',
                        'message' => "Incorrect permissions ($actualPerms), should be $expectedPerms"
                    ];
                }
            }
        }
    }

    private function scanForSQLInjection(): void {
        // Scan PHP files for potential SQL injection vulnerabilities
        $files = array_merge(
            glob('../includes/**/*.php'),
            glob('../api/**/*.php'),
            glob('../admin/**/*.php')
        );
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/\$_(GET|POST|REQUEST)\[.*?\].*?\$pdo->query|mysql_query/', $content)) {
                $this->scanResults[] = [
                    'type' => 'sql_injection',
                    'file' => $file,
                    'severity' => 'critical',
                    'message' => 'Potential SQL injection vulnerability detected'
                ];
            }
        }
    }

    private function scanForXSS(): void {
        // Scan for potential XSS vulnerabilities
        $files = array_merge(
            glob('../includes/**/*.php'),
            glob('../templates/**/*.php'),
            glob('../admin/**/*.php')
        );
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/echo\s+\$_(GET|POST|REQUEST)\[.*?\]|print\s+\$_(GET|POST|REQUEST)\[.*?\]/', $content)) {
                $this->scanResults[] = [
                    'type' => 'xss',
                    'file' => $file,
                    'severity' => 'high',
                    'message' => 'Potential XSS vulnerability detected'
                ];
            }
        }
    }

    private function checkCSRFProtection(): void {
        // Check if forms have CSRF protection
        $files = glob('../templates/**/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, '
<form') !== false && 
                strpos(
$content, 'csrf_token') === false) {
                $this->scanResults[] = [
                    'type' => 'csrf',
                    'file' => $file,
                    'severity' => 'medium',
                    'message' => 'Form missing CSRF protection'
                ];
            }
        }
    }

    /**
     * Generate comprehensive security report
     */
    public function generateReport(): string {
        $report = "Security Audit Report\n";
        $report .= "====================\n\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $report .= "System: " . php_uname() . "\n";
        $report .= "PHP Version: " . phpversion() . "\n\n";
        
        $severityCounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];
        
        // Detailed findings
        $report .= "Findings (" . count($this->scanResults) . " total):\n\n";
        foreach ($this->scanResults as $result) {
            $severityCounts[$result['severity']]++;
            
            $report .= "=== " . strtoupper($result['severity']) . ": " . $result['type'] . " ===\n";
            $report .= "Location: " . ($result['file'] ?? 'N/A') . "\n";
            $report .= "Description: " . $result['message'] . "\n";
            
            if (!empty($result['recommendation'])) {
                $report .= "Recommendation: " . $result['recommendation'] . "\n";
            }
            
            $report .= "\n";
        }
        
        // Summary statistics
        $report .= "\nSummary:\n";
        $report .= "Critical: " . $severityCounts['critical'] . "\n";
        $report .= "High: " . $severityCounts['high'] . "\n";
        $report .= "Medium: " . $severityCounts['medium'] . "\n";
        $report .= "Low: " . $severityCounts['low'] . "\n";
        
        // Overall status
        $report .= "\nOverall Status: ";
        if ($severityCounts['critical'] > 0) {
            $report .= "CRITICAL - Immediate action required\n";
        } elseif ($severityCounts['high'] > 0) {
            $report .= "WARNING - High priority issues found\n";
        } elseif ($severityCounts['medium'] > 0) {
            $report .= "CAUTION - Medium priority issues found\n";
        } else {
            $report .= "SECURE - No critical/high issues found\n";
        }
        
        return $report;
    }
}
