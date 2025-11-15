<?php

class ThemeEngine {
    protected $themePath;
    protected $currentTheme;
    protected $templatePaths = [];

    public function __construct(string $themePath) {
        $this->themePath = rtrim($themePath, '/');
    }

    public function registerTheme(string $name, array $config): void {
        $this->templatePaths[$name] = [
            'path' => $this->themePath . '/' . $name . '/templates',
            'config' => $config
        ];
    }

    public function setTheme(string $name): void {
        if (!isset($this->templatePaths[$name])) {
            throw new InvalidArgumentException("Theme {$name} not registered");
        }
        $this->currentTheme = $name;
    }

    public function render(string $template, array $data = []): string {
        if (!$this->currentTheme) {
            throw new RuntimeException('No theme selected');
        }

        $templatePath = $this->templatePaths[$this->currentTheme]['path'] . '/' . $template;
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template {$template} not found in theme {$this->currentTheme}");
        }

        extract($data);
        ob_start();
        require_once $templatePath;
        return ob_get_clean();
    }

    public function extend(string $parent, string $child): void {
        // Implement template inheritance logic
    }
}
