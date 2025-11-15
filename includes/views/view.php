<?php

namespace Includes\Views;

class View {
    protected $basePath;
    protected $data = [];

    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/') . '/';
    }

    public function render($view, $data = []) {
        $this->data = $data;
        $viewPath = $this->basePath . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: $viewPath");
        }

        extract($this->data);
        ob_start();
        require_once $viewPath;
        return ob_get_clean();
    }

    public function with($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }
}
