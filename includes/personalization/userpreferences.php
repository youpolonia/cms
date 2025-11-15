<?php

class UserPreferences {
    protected $pdo;
    protected $auditLogEnabled = true;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPreferences(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT preferences FROM user_preferences WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return json_decode($stmt->fetchColumn(), true) ?? [];
    }

    public function updatePreferences(int $userId, array $preferences): bool {
        $current = $this->getPreferences($userId);
        $merged = array_merge($current, $preferences);
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_preferences (user_id, preferences) 
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE preferences = VALUES(preferences)"
        );

        try {
            $this->pdo->beginTransaction();
            
            $result = $stmt->execute([
                $userId, 
                json_encode($merged)
            ]);

            if ($this->auditLogEnabled) {
                $this->logPreferenceChange($userId, $current, $merged);
            }

            $this->pdo->commit();
            return $result;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    protected function logPreferenceChange(int $userId, array $old, array $new) {
        $changes = [];
        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $old)) {
                $changes[$key] = ['action' => 'added', 'value' => $value];
            } elseif ($old[$key] !== $value) {
                $changes[$key] = [
                    'action' => 'updated', 
                    'old' => $old[$key],
                    'new' => $value
                ];
            }
        }

        if (!empty($changes)) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO personalization_metrics 
                 (user_id, event_type, event_data)
                 VALUES (?, 'preference_change', ?)"
            );
            $stmt->execute([
                $userId,
                json_encode($changes)
            ]);
        }
    }

    public static function createContentProcessor(\PDO $pdo): callable {
        $prefs = new self($pdo);
        return function($content, $context) use ($prefs) {
            if (isset($context['user_id'])) {
                $context['user_preferences'] = $prefs->getPreferences($context['user_id']);
            }
            return $content;
        };
    }
}
