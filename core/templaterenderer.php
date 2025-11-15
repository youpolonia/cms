<?php
/**
 * Template Renderer for CMS
 * Supports layout inheritance, sections, and variable injection
 */
class TemplateRenderer {
    protected $templatePaths = [];
    protected $sections = [];
    protected $currentSection = null;
    protected $variables = [];
    protected $layout = null;

    /**
     * Add template search path
     */
    public function addPath(string $path): void {
        $this->templatePaths[] = rtrim($path, '/');
    }

    /**
     * Set variables available in all templates
     */
    public function share(array $variables): void {
        $this->variables = array_merge($this->variables, $variables);
    }

    /**
     * Render a template with variables
     */
    public function render(string $template, array $variables = []): string {
        $this->layout = null;
        $this->sections = [];
        $this->currentSection = null;

        $content = $this->evaluateTemplate($template, $variables);

        if ($this->layout) {
            $content = $this->evaluateTemplate($this->layout, $variables);
        }

        return $content;
    }

    /**
     * Evaluate template with output buffering
     */
    protected function evaluateTemplate(string $template, array $variables = []): string {
        $path = $this->resolveTemplatePath($template);
        if (!$path) {
            throw new \RuntimeException("Template not found: $template");
        }

        extract(array_merge($this->variables, $variables), EXTR_SKIP);
        ob_start();

        try {
            require_once $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Resolve template path from registered paths
     */
    protected function resolveTemplatePath(string $template): ?string {
        foreach ($this->templatePaths as $path) {
            $fullPath = "$path/$template.php";
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }
        return null;
    }

    /**
     * Start a section
     */
    public function startSection(string $name): void {
        if ($this->currentSection) {
            throw new \RuntimeException("Cannot nest sections");
        }
        $this->currentSection = $name;
        ob_start();
    }

    /**
     * End current section
     */
    public function endSection(): void {
        if (!$this->currentSection) {
            throw new \RuntimeException("No section started");
        }
        $this->sections[$this->currentSection] = ob_get_clean();
        $this->currentSection = null;
    }

    /**
     * Show section content
     */
    public function showSection(string $name): void {
        echo $this->sections[$name] ?? '';
    }

    /**
     * Extend a layout template
     */
    public function extend(string $template): void {
        $this->layout = $template;
    }

    /**
     * Escape output
     */
    public function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Render custom field value with formatting
     */
    public function renderCustomField(string $fieldName, array $context = []): string {
        if (!isset($context['content_id'])) {
            return '';
        }

        $fieldValues = CustomFieldManager::getFieldValues($context['content_id']);
        $value = $fieldValues[$fieldName] ?? '';

        // Basic formatting based on field type (can be extended)
        if (str_ends_with($fieldName, '_html')) {
            return $value; // Raw HTML
        } elseif (str_ends_with($fieldName, '_date')) {
            return $this->e(date('Y-m-d', strtotime($value)));
        } elseif (is_array($value)) {
            return $this->e(implode(', ', $value));
        }
        return $this->e($value);
    }

    /**
     * Process template content for custom field tags
     */
    protected function processCustomFields(string $content, array $variables): string {
        return preg_replace_callback(
            '/\{\{custom\.([a-zA-Z0-9_]+)\}\}/',
            function($matches) use ($variables) {
                return $this->renderCustomField($matches[1], $variables);
            },
            $content
        );
    }
}
