<?php
class SystemAlert {
    public static function get_alerts(int $tenant_id = null, int $limit = 20, int $offset = 0): array {
        $alerts = [];
        $where = $tenant_id ? "WHERE tenant_id = $tenant_id AND resolved = 0" : "WHERE resolved = 0";
        $query = "SELECT * FROM system_alerts $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        // Execute query and return results
        $result = db_query($query);
        while ($row = db_fetch_assoc($result)) {
            $alerts[] = $row;
        }
        return $alerts;
    }

    public static function resolve_alert(int $alert_id): bool {
        return db_query("UPDATE system_alerts SET resolved = 1 WHERE id = $alert_id");
    }

    public static function create_alert(string $message, string $type, int $tenant_id = null): bool {
        $tenant = $tenant_id ?? 'NULL';
        return db_query("INSERT INTO system_alerts (message, type, tenant_id) VALUES ('$message', '$type', $tenant)");
    }
}
