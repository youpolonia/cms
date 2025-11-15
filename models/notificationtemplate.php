<?php
/**
 * Notification Template Model
 * Handles CRUD operations for notification templates
 */
class NotificationTemplate {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all templates
     */
    public function getAll() {
        $query = "SELECT * FROM notification_templates";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get template by ID
     */
    public function getById($template_id) {
        $query = "SELECT * FROM notification_templates WHERE template_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$template_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new template
     */
    public function create($data) {
        $query = "INSERT INTO notification_templates 
                 (name, description, type, subject_template, body_template, variables, channels)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['type'],
            $data['subject_template'],
            $data['body_template'],
            json_encode($data['variables']),
            json_encode($data['channels'])
        ]);
    }

    /**
     * Update template
     */
    public function update($template_id, $data) {
        $query = "UPDATE notification_templates SET
                 name = ?,
                 description = ?,
                 type = ?,
                 subject_template = ?,
                 body_template = ?,
                 variables = ?,
                 channels = ?
                 WHERE template_id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['type'],
            $data['subject_template'],
            $data['body_template'],
            json_encode($data['variables']),
            json_encode($data['channels']),
            $template_id
        ]);
    }

    /**
     * Delete template
     */
    public function delete($template_id) {
        $query = "DELETE FROM notification_templates WHERE template_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$template_id]);
    }

    /**
     * Render template with variables
     * @param string $template The template content with {variable} placeholders
     * @param array $variables Associative array of variables to substitute
     * @return string Rendered template
     */
    public function renderTemplate($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Render notification template by ID with provided data
     * @param int $template_id Template ID
     * @param array $data Data for variable substitution
     * @return array Array with rendered subject and body
     */
    public function render($template_id, $data) {
        $template = $this->getById($template_id);
        if (!$template) {
            return false;
        }

        $variables = json_decode($template['variables'], true);
        $mergedData = array_merge($variables, $data);

        return [
            'subject' => $this->renderTemplate($template['subject_template'], $mergedData),
            'body' => $this->renderTemplate($template['body_template'], $mergedData)
        ];
    }
}
