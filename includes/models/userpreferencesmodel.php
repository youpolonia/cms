<?php
require_once __DIR__ . '/../../config.php';

class UserPreferencesModel {
    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    public function getPreference($userId, $key) {
        $stmt = $this->db->prepare("SELECT preference_value FROM user_preferences 
                                  WHERE user_id = ? AND preference_key = ?");
        $stmt->execute([$userId, $key]);
        return $stmt->fetchColumn();
    }

    public function setPreference($userId, $key, $value) {
        if (!$this->validatePreference($key, $value)) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO user_preferences 
                                  (user_id, preference_key, preference_value) 
                                  VALUES (?, ?, ?)
                                  ON DUPLICATE KEY UPDATE 
                                  preference_value = VALUES(preference_value)");
        return $stmt->execute([$userId, $key, $value]);
    }

    public function getAllPreferences($userId) {
        $stmt = $this->db->prepare("SELECT preference_key, preference_value 
                                  FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function deletePreference($userId, $key) {
        $stmt = $this->db->prepare("DELETE FROM user_preferences 
                                  WHERE user_id = ? AND preference_key = ?");
        return $stmt->execute([$userId, $key]);
    }

    private function validatePreference($key, $value) {
        // Basic validation rules
        if (strlen($key) > 255) {
            error_log("Preference key too long: $key");
            return false;
        }

        if (strlen($value) > 65535) { // TEXT field limit
            error_log("Preference value too long for key: $key");
            return false;
        }

        return true;
    }
}
