<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Notification Template System
 * Handles storage and rendering of notification templates with variable substitution
 */
class NotificationTemplate {
    /**
     * @var array Static cache of loaded templates [template_id => NotificationTemplate]
     */
    private static $cache = [];
    /**
     * @var int Template ID from database
     */
    private $id;

    /**
     * @var string Template name/identifier
     */
    private $name;

    /**
     * @var array Template content by channel [channel => content]
     */
    private $content = [];

    /**
     * @var array Available template variables
     */
    private $variables = [];

    /**
     * Create a template instance for testing purposes
     * @param array $content Template content by channel
     * @param array $variables Available template variables
     * @return self
     */
    public static function createForTesting(array $content = [], array $variables = []): self {
        $template = new self();
        $template->content = $content;
        $template->variables = $variables;
        return $template;
    }

    /**
     * Load template by ID
     * @param int $templateId
     * @return self
     * @throws Exception If template not found
     */
    public static function loadById(int $templateId): self {
        // Return cached instance if available
        if (isset(self::$cache[$templateId])) {
            return self::$cache[$templateId];
        }

        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT * FROM notification_templates WHERE template_id = ?");
        $stmt->execute([$templateId]);
        
        if (!$template = $stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception("Notification template not found with ID: $templateId");
        }

        $instance = new self();
        $instance->id = $template['template_id'];
        $instance->name = $template['name'];
        $instance->content = [
            'subject' => $template['subject_template'],
            'body' => $template['body_template']
        ];
        $instance->variables = json_decode($template['variables'], true);
        
        // Cache the instance
        self::$cache[$templateId] = $instance;
        
        return $instance;
    }

    /**
     * Render template with provided variables
     * @param array $variables Key-value pairs for substitution
     * @param string $channel Notification channel (email/web/mobile)
     * @return string Rendered content
     */
    public function render(array $variables, string $channel = 'web'): string {
        // Validate all required variables are provided
        $missing = array_diff_key(array_flip($this->variables), $variables);
        if (!empty($missing)) {
            throw new Exception('Missing required template variables: ' . implode(', ', array_keys($missing)));
        }

        // Process subject and body templates
        $rendered = [];
        foreach ($this->content as $type => $template) {
            $rendered[$type] = preg_replace_callback('/\{\{(\w+(?:\.\w+)*)\}\}/',
                function($matches) use ($variables) {
                    return $this->getVariableValue($variables, $matches[1]);
                },
                $template
            );
        }

        return $rendered['body'];
    }
    
    protected function getVariableValue(array $vars, string $path) {
        $parts = explode('.', $path);
        $value = $vars;
        
        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return '{{' . $path . '}}';
            }
            $value = $value[$part];
        }
        
        if (is_array($value)) {
            return implode(', ', array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $value));
        }
        
        return $value;
    }

    /**
     * Get all available variables for this template
     * @return array
     */
    public function getAvailableVariables(): array {
        return $this->variables;
    }

    /**
     * Save template to database
     * @return bool
     */
    public function save(): bool {
        // Clear cache for this template
        if (isset(self::$cache[$this->id])) {
            unset(self::$cache[$this->id]);
        }
        
        // Implementation will handle database operations
    }
}
