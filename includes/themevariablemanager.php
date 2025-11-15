<?php
/**
 * ThemeVariableManager - Handles theme variables with tenant/theme isolation
 * Pure PHP 8.1+ implementation, FTP-deployable
 */

class ThemeVariableManager {
    private static $instance = null;
    private $db;

    private function __construct() {
        $this->db = $this->getDatabaseConnection();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getDatabaseConnection(): PDO {
        require_once __DIR__.'/../core/database.php';
        return \core\Database::connection();
    }

    public function getVariable(string $name, $default = null, ?string $theme = null) {
        $tenantId = $this->getCurrentTenantId();
        $theme = $theme ?? $this->getCurrentTheme();

        $stmt = $this->db->prepare(
            "SELECT var_value, var_type FROM theme_variables 
            WHERE tenant_id = ? AND theme = ? AND var_name = ?"
        );
        $stmt->execute([$tenantId, $theme, $name]);
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $this->castToType($row['var_value'], $row['var_type']);
        }

        return $default;
    }

    public function setVariable(string $name, $value, ?string $type = null, ?string $theme = null) {
        $tenantId = $this->getCurrentTenantId();
        $theme = $theme ?? $this->getCurrentTheme();

        $stmt = $this->db->prepare(
            "INSERT INTO theme_variables 
            (tenant_id, theme, var_name, var_value, var_type) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            var_value = VALUES(var_value), 
            var_type = VALUES(var_type),
            updated_at = CURRENT_TIMESTAMP"
        );

        return $stmt->execute([
            $tenantId,
            $theme,
            $name,
            is_scalar($value) ? $value : json_encode($value),
            $type ?? $this->detectType($value)
        ]);
    }

    public function getAllVariables(?string $theme = null) {
        $tenantId = $this->getCurrentTenantId();
        $theme = $theme ?? $this->getCurrentTheme();

        $stmt = $this->db->prepare(
            "SELECT var_name, var_value, var_type FROM theme_variables 
            WHERE tenant_id = ? AND theme = ?"
        );
        $stmt->execute([$tenantId, $theme]);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['var_name']] = $this->castToType($row['var_value'], $row['var_type']);
        }
        return $result;
    }

    private function castToType($value, ?string $type) {
        if ($type === 'json') {
            return json_decode($value, true);
        } elseif ($type === 'number') {
            return is_numeric($value) ? $value + 0 : $value;
        }
        return $value;
    }

    private function detectType($value): string {
        if (is_array($value) || is_object($value)) {
            return 'json';
        } elseif (is_numeric($value)) {
            return 'number';
        }
        return 'string';
    }

    private function getCurrentTenantId(): string {
        // Assumes tenant identification is handled elsewhere
        return $_SESSION['tenant_id'] ?? 'default';
    }

    private function getCurrentTheme(): string {
        // Assumes theme identification is handled elsewhere
        return $_SESSION['current_theme'] ?? 'default';
    }
}
