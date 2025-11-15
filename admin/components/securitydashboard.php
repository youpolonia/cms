<?php
declare(strict_types=1);

class SecurityDashboard {
    private SecurityAuditor $auditor;
    private array $config;

    public function __construct(array $config = []) {
        $this->config = array_merge([
            'scan_interval' => 86400, // Daily scans
            'alert_threshold' => 'high',
            'retain_reports' => 30 // Days
        ], $config);

        $this->auditor = new SecurityAuditor();
    }

    public function renderDashboard(): string {
        $results = $this->auditor->audit();
        $report = $this->auditor->generateReport();

        ob_start();
        ?>        <div class="security-dashboard">
            <h2>Security Dashboard</h2>
            <div class="summary">
                <?= $this->renderSummary($results) ?>
            </div>
            <div class="scan-results">
                <?= $this->renderScanResults($results) ?>
            </div>
            <div class="full-report">
                <pre><?= htmlspecialchars($report) ?></pre>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderSummary(array $results): string {
        $counts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];

        foreach ($results as $result) {
            $counts[$result['severity']]++;
        }

        ob_start();
        ?>        <div class="summary-cards">
            <div class="card critical">
                <h3>Critical</h3>
                <span><?= $counts['critical'] ?></span>
            </div>
            <div class="card high">
                <h3>High</h3>
                <span><?= $counts['high'] ?></span>
            </div>
            <div class="card medium">
                <h3>Medium</h3>
                <span><?= $counts['medium'] ?></span>
            </div>
            <div class="card low">
                <h3>Low</h3>
                <span><?= $counts['low'] ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderScanResults(array $results): string {
        ob_start();
        ?>        <table class="scan-results">
            <thead>
                <tr>
                    <th>Severity</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr class="<?= $result['severity'] ?>">
                    <td><?= ucfirst($result['severity']) ?></td>
                    <td><?= $result['type'] ?></td>
                    <td><?= $result['file'] ?? 'N/A' ?></td>
                    <td><?= $result['message'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    public function scheduleScans(): void {
        // Implementation for n8n integration
        $lastScan = $this->getLastScanTime();
        if (time() - $lastScan > $this->config['scan_interval']) {
            $this->runScheduledScan();
        }
    }

    private function getLastScanTime(): int {
        $scanFile = '../logs/security_scans.json';
        if (file_exists($scanFile)) {
            $data = json_decode(file_get_contents($scanFile), true);
            return $data['last_scan'] ?? 0;
        }
        return 0;
    }

    private function runScheduledScan(): void {
        $results = $this->auditor->audit();
        $this->storeScanResults($results);
        $this->alertIfNeeded($results);
        $this->updateLastScanTime();
    }

    private function storeScanResults(array $results): void {
        $scanFile = '../logs/security_scans.json';
        $data = file_exists($scanFile) ? json_decode(file_get_contents($scanFile), true) : [];
        
        $data['scans'][] = [
            'timestamp' => time(),
            'results' => $results
        ];
        
        // Keep only last 30 days of scans
        $data['scans'] = array_slice($data['scans'], -$this->config['retain_reports']);
        
        file_put_contents($scanFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function updateLastScanTime(): void {
        $scanFile = '../logs/security_scans.json';
        $data = file_exists($scanFile) ? json_decode(file_get_contents($scanFile), true) : [];
        $data['last_scan'] = time();
        file_put_contents($scanFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function alertIfNeeded(array $results): void {
        foreach ($results as $result) {
            if ($result['severity'] === $this->config['alert_threshold']) {
                $this->sendAlert($result);
                break;
            }
        }
    }

    private function sendAlert(array $result): void {
        $webhookUrl = 'https://n8n.example.com/webhook/security-alert';
        $payload = [
            'timestamp' => time(),
            'severity' => $result['severity'],
            'type' => $result['type'],
            'location' => $result['file'] ?? 'N/A',
            'message' => $result['message']
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
