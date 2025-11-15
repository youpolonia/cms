<?php
namespace Core;

class View {
    protected $templatePath;
    protected $data = [];
    protected $layout = null;
    protected $partials = [];

    public function __construct($templatePath = '') {
        $this->templatePath = rtrim($templatePath, '/') . '/';
    }

    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }

    public function addPartial($name, $template) {
        $this->partials[$name] = $template;
        return $this;
    }

    public function render($template, $data = []) {
        $this->data = array_merge($this->data, $data);
        $content = $this->renderTemplate($template);

        if ($this->layout) {
            $this->data['content'] = $content;
            return $this->renderTemplate($this->layout);
        }

        return $content;
    }

    protected function renderTemplate($template) {
        $filePath = $this->templatePath . $template . '.php';

        if (!file_exists($filePath)) {
            throw new \Exception("Template file not found: {$filePath}");
        }

        extract($this->data);
        ob_start();
        require_once $filePath;
        return ob_get_clean();
    }

    public function partial($name) {
        if (!isset($this->partials[$name])) {
            throw new \Exception("Partial not found: {$name}");
        }
        return $this->renderTemplate($this->partials[$name]);
    }
}
