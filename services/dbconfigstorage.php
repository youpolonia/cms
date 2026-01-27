<?php
class DbConfigStorage implements ConfigStorageInterface
{
    private static function getConnection(): PDO
    {
        static $conn = null;
        if ($conn === null) {
            $conn = \core\Database::connection();
        }
        return $conn;
    }

    public function load(): array
    {
        try {
            $stmt = self::getConnection()->query("SELECT config_key, config_value FROM config_storage");
            $config = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $config[$row['config_key']] = json_decode($row['config_value'], true);
            }
            return $config;
        } catch (PDOException $e) {
            error_log("DbConfigStorage load error: " . $e->getMessage());
            return [];
        }
    }

    public function save(array $config): bool
    {
        try {
            $conn = self::getConnection();
            $conn->beginTransaction();
            
            foreach ($config as $key => $value) {
                $stmt = $conn->prepare("
                    INSERT INTO config_storage (config_key, config_value)
                    VALUES (:key, :value)
                    ON CONFLICT (config_key) DO UPDATE SET config_value = EXCLUDED.config_value
                ");
                $stmt->execute([
                    ':key' => $key,
                    ':value' => json_encode($value)
                ]);
            }
            
            return $conn->commit();
        } catch (PDOException $e) {
            error_log("DbConfigStorage save error: " . $e->getMessage());
            return false;
        }
    }
}
