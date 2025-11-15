<?php
require_once __DIR__ . '/../core/database.php';

class WorkerSchedule {
    private static string $table = 'worker_schedules';
    
    public int $id;
    public int $worker_id;
    public string $start_time;
    public string $end_time;
    public string $status;
    public string $created_at;
    public string $updated_at;

    private function __construct() {}

    public static function create(array $data): self {
        $required = ['worker_id', 'start_time', 'end_time', 'status'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        if (!in_array($data['status'], ['scheduled', 'in_progress', 'completed', 'canceled'])) {
            throw new InvalidArgumentException('Invalid status value');
        }

        $db = \core\Database::connection();
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("
                INSERT INTO " . self::$table . " 
                (worker_id, start_time, end_time, status, created_at, updated_at)
                VALUES (:worker_id, :start_time, :end_time, :status, NOW(), NOW())
            ");
            
            $stmt->execute([
                ':worker_id' => $data['worker_id'],
                ':start_time' => $data['start_time'],
                ':end_time' => $data['end_time'],
                ':status' => $data['status']
            ]);
            
            $newId = $db->lastInsertId();
            $db->commit();
            
            return self::getById($newId);
        } catch (PDOException $e) {
            $db->rollBack();
            throw new RuntimeException("Schedule creation failed: " . $e->getMessage());
        }
    }

    public static function getById(int $id): ?self {
        require_once __DIR__ . '/../core/database.php';
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? self::hydrate($result) : null;
    }

    public function update(array $data): void {
        $updatable = ['start_time', 'end_time', 'status'];
        $updates = [];
        $params = [':id' => $this->id];

        foreach ($updatable as $field) {
            if (isset($data[$field])) {
                if ($field === 'status' && !in_array($data[$field], ['scheduled', 'in_progress', 'completed', 'canceled'])) {
                    throw new InvalidArgumentException('Invalid status value');
                }
                $updates[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($updates)) {
            return;
        }

        require_once __DIR__ . '/../core/database.php';
        $db = \core\Database::connection();
        try {
            $db->beginTransaction();
            
            $stmt = $db->prepare("
                UPDATE " . self::$table . " 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = :id
            ");
            
            $stmt->execute($params);
            $db->commit();
            
            // Refresh object properties
            foreach ($data as $key => $value) {
                if (in_array($key, $updatable)) {
                    $this->$key = $value;
                }
            }
        } catch (PDOException $e) {
            $db->rollBack();
            throw new RuntimeException("Schedule update failed: " . $e->getMessage());
        }
    }

    public static function delete(int $id): bool {
        require_once __DIR__ . '/../core/database.php';
        $db = \core\Database::connection();
        try {
            $stmt = $db->prepare("DELETE FROM " . self::$table . " WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new RuntimeException("Schedule deletion failed: " . $e->getMessage());
        }
    }

    private static function hydrate(array $data): self {
        $schedule = new self();
        $schedule->id = (int)$data['id'];
        $schedule->worker_id = (int)$data['worker_id'];
        $schedule->start_time = $data['start_time'];
        $schedule->end_time = $data['end_time'];
        $schedule->status = $data['status'];
        $schedule->created_at = $data['created_at'];
        $schedule->updated_at = $data['updated_at'];
        return $schedule;
    }
}
