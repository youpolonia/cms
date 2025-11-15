<?php
declare(strict_types=1);

class ReportGenerator {
    private const N8N_WEBHOOK_URL = 'https://n8n.example.com/webhook/analytics';
    
    public static function generateDailyReport(int $tenantId): bool {
        $metrics = DB::select(
            "SELECT metric_name, 
                    COUNT(*) as count,
                    AVG(JSON_EXTRACT(metric_value, '$.value')) as avg_value
             FROM tenant_analytics 
             WHERE tenant_id = ? 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
             GROUP BY metric_name",
            [$tenantId]
        );

        $report = [
            'tenant_id' => $tenantId,
            'date' => date('Y-m-d'),
            'metrics' => $metrics
        ];

        return self::sendToN8N($report);
    }

    private static function sendToN8N(array $data): bool {
        $ch = curl_init(self::N8N_WEBHOOK_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status === 200;
    }
}
