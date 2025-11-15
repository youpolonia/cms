<?php

require_once __DIR__ . '/../../core/database.php';

class AIProviderService {
    private $db;
    
    public function __construct() {
        $this->db = \core\Database::connection();
    }
    
    public function createProviderConfig($config) {
        $stmt = $this->db->prepare("
            INSERT INTO ai_provider_configs 
            (provider_name, api_key, base_url, model_name, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $config['provider_name'],
            $config['api_key'],
            $config['base_url'] ?? null,
            $config['model_name'] ?? null,
            $config['is_active'] ?? true
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function updateProviderConfig($id, $config) {
        $stmt = $this->db->prepare("
            UPDATE ai_provider_configs SET
            provider_name = ?,
            api_key = ?,
            base_url = ?,
            model_name = ?,
            is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $config['provider_name'],
            $config['api_key'],
            $config['base_url'] ?? null,
            $config['model_name'] ?? null,
            $config['is_active'] ?? true,
            $id
        ]);
    }
    
    public function deleteProviderConfig($id) {
        $stmt = $this->db->prepare("DELETE FROM ai_provider_configs WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function listProviderConfigs() {
        $stmt = $this->db->query("SELECT * FROM ai_provider_configs");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProviderConfig($id) {
        $stmt = $this->db->prepare("SELECT * FROM ai_provider_configs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
