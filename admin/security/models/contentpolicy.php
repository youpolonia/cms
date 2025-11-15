<?php
namespace Admin\Security\Models;

use Database;
use Core\Security\SecureSession;

class ContentPolicy extends Policy
{
    public function hasPermission(int $userId, string $permission): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_permissions 
            WHERE user_id = ? AND permission = ?
        ");
        $stmt->execute([$userId, $permission]);
        return (bool)$stmt->fetchColumn();
    }

    public function canViewContent(int $userId, string $type): bool
    {
        return $this->canAccessSecurityPanel($userId) || 
               $this->hasPermission($userId, "content.$type.view");
    }

    public function canCreateContent(int $userId, string $type): bool
    {
        return $this->canAccessSecurityPanel($userId) || 
               $this->hasPermission($userId, "content.$type.create");
    }

    public function canUpdateContent(int $userId, int $contentId, string $type): bool
    {
        // Check if user owns the content
        $stmt = $this->db->prepare("
            SELECT 1 FROM content 
            WHERE id = ? AND type = ? AND user_id = ?
        ");
        $stmt->execute([$contentId, $type, $userId]);
        if ($stmt->fetchColumn()) {
            return true;
        }
        
        return $this->canAccessSecurityPanel($userId) || 
               $this->hasPermission($userId, "content.$type.update");
    }

    public function canDeleteContent(int $userId, int $contentId, string $type): bool
    {
        // Check if user owns the content
        $stmt = $this->db->prepare("
            SELECT 1 FROM content 
            WHERE id = ? AND type = ? AND user_id = ?
        ");
        $stmt->execute([$contentId, $type, $userId]);
        if ($stmt->fetchColumn()) {
            return true;
        }
        
        return $this->canAccessSecurityPanel($userId) || 
               $this->hasPermission($userId, "content.$type.delete");
    }
}
