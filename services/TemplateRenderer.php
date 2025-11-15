<?php
/**
 * Simple Template Renderer
 */
class TemplateRenderer {
    private string $templateDir;

    public function __construct(string $templateDir) {
        $this->templateDir = rtrim($templateDir, '/') . '/';
    }

    public function extend(string $template): void {
        $templatePath = $this->templateDir . $template;
        
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template not found: $templatePath");
        }

        extract($this->data);
        require_once $templatePath;
    }

    public function __set(string $name, $value): void {
        $this->data[$name] = $value;
    }

    public function __get(string $name) {
        return $this->data[$name] ?? null;
    }
}
