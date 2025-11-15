<?php

/**
 * ContentPageModel
 * 
 * Model for accessing and manipulating content_pages table data
 */
class ContentPageModel {
    protected $db;
    protected $table = 'content_pages';
    protected $tenantService;
    protected $permissionService;
    
    /**
     * Constructor
     */
    public function __construct(TenantService $tenantService, PermissionService $permissionService) {
        require_once __DIR__ . '/../includes/database/connection.php';
        $this->db = \core\Database::connection();
        $this->tenantService = $tenantService;
        $this->permissionService = $permissionService;
    }
    
    /**
     * Get a content page by ID with permission check
     */
    public function getById($id) {
        if (!$this->permissionService->can('view_content')) {
            throw new PermissionDeniedException('View content permission required');
        }

        try {
            $query = "SELECT * FROM {$this->table}
                     WHERE id = :id
                     AND tenant_id = :tenant_id
                     LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':tenant_id' => $this->tenantService->getCurrentTenantId()
            ]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error fetching content page: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get published content with optimized query
     */
    public function getPublishedPage($id) {
        if (!$this->permissionService->can('view_published_content')) {
            throw new PermissionDeniedException('View published content permission required');
        }

        try {
            $query = "SELECT id, title, slug, content, updated_at 
                     FROM {$this->table}
                     WHERE id = :id
                     AND current_status = 'published'
                     AND tenant_id = :tenant_id
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':tenant_id' => $this->tenantService->getCurrentTenantId()
            ]);
            
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error fetching published page: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create content with validation
     */
    public function create(array $data) {
        if (!$this->permissionService->can('create_content')) {
            throw new PermissionDeniedException('Create content permission required');
        }

        $this->validateContent($data);

        try {
            $data['tenant_id'] = $this->tenantService->getCurrentTenantId();
            
            $fields = array_keys($data);
            $placeholders = array_map(fn($field) => ":{$field}", $fields);
            
            $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                      VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($data as $field => $value) {
                $stmt->bindValue(":{$field}", $value);
            }
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating content: " . $e->getMessage());
            throw new DatabaseException("Failed to create content");
        }
    }

    /**
     * Content validation
     */
    protected function validateContent(array $data) {
        if (empty($data['title'])) {
            throw new ValidationException('Title is required');
        }
        
        if (strlen($data['title']) > 255) {
            throw new ValidationException('Title too long');
        }
        
        if (empty($data['content'])) {
            throw new ValidationException('Content is required');
        }
    }
}
