<?php
require_once __DIR__ . '/../core/database.php';

class SearchService {
    protected $db;
    protected $tenantId;

    public function __construct($tenantId) {
        $this->tenantId = $tenantId;
        $this->db = \core\Database::connection();
    }

    public function search($term, $limit = 10) {
        $results = [];
        
        // Search contents table
        $query = "SELECT id, title, content 
                 FROM contents 
                 WHERE tenant_id = :tenant_id 
                 AND status = 'published'
                 AND deleted_at IS NULL
                 AND (title LIKE :term OR content LIKE :term)
                 LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':tenant_id' => $this->tenantId,
            ':term' => "%$term%",
            ':limit' => $limit
        ]);
        
        $results['contents'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    public function searchAllTables($term, $limit = 5) {
        $results = [];
        
        // Search multiple tables with tenant isolation
        $tables = ['contents', 'pages', 'posts'];
        
        foreach ($tables as $table) {
            $query = "SELECT id, title, content 
                     FROM $table 
                     WHERE tenant_id = :tenant_id 
                     AND (title LIKE :term OR content LIKE :term)
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':tenant_id' => $this->tenantId,
                ':term' => "%$term%", 
                ':limit' => $limit
            ]);
            
            $results[$table] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $results;
    }
}
