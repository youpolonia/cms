<?php
/**
 * Content Fields Data Access Layer
 */
class ContentFields {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Add a field to a content type
     */
    public function addField($content_type_id, $name, $machine_name, $field_type, $settings = [], $is_required = false, $weight = 0) {
        $stmt = $this->db->prepare("INSERT INTO content_fields 
            (content_type_id, name, machine_name, field_type, settings, is_required, weight) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $content_type_id,
            $name,
            $machine_name,
            $field_type,
            json_encode($settings),
            $is_required,
            $weight
        ]);
    }

    /**
     * Get all fields for a content type
     */
    public function getFieldsForType($content_type_id) {
        $stmt = $this->db->prepare("SELECT * FROM content_fields 
            WHERE content_type_id = ? 
            ORDER BY weight, name");
        $stmt->execute([$content_type_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get field by ID
     */
    public function getFieldById($id) {
        $stmt = $this->db->prepare("SELECT * FROM content_fields WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update field
     */
    public function updateField($id, $name, $machine_name, $field_type, $settings = [], $is_required = false, $weight = 0) {
        $stmt = $this->db->prepare("UPDATE content_fields 
            SET name = ?, machine_name = ?, field_type = ?, 
                settings = ?, is_required = ?, weight = ? 
            WHERE id = ?");
        return $stmt->execute([
            $name,
            $machine_name,
            $field_type,
            json_encode($settings),
            $is_required,
            $weight,
            $id
        ]);
    }

    /**
     * Delete field
     */
    public function deleteField($id) {
        $stmt = $this->db->prepare("DELETE FROM content_fields WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Reorder fields
     */
    public function reorderFields($content_type_id, $new_order) {
        $this->db->beginTransaction();
        try {
            foreach ($new_order as $weight => $field_id) {
                $stmt = $this->db->prepare("UPDATE content_fields 
                    SET weight = ? 
                    WHERE id = ? AND content_type_id = ?");
                $stmt->execute([$weight, $field_id, $content_type_id]);
            }
            return $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
