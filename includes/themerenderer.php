<?php
/**
 * Theme Rendering Engine
 */
class ThemeRenderer {
    protected $themePath;
    protected $config;
    protected $contentPipeline;

    public function __construct($themePath, $config) {
        $this->themePath = $themePath;
        $this->config = $config;
        $this->contentPipeline = new ContentPipeline();
    }

    public function render($template, $content = []) {
        $templateFile = $this->themePath.'/'.$this->config['templates'][$template];
        
        if (!file_exists($templateFile)) {
            throw new Exception("Template not found: $template");
        }

        // Process content through pipeline
        $processedContent = $this->contentPipeline->process($content, [
            'template' => $template
        ]);

        // Extract content to variables
        extract($processedContent);

        // Start output buffering
        ob_start();
        require_once $templateFile;
        return ob_get_clean();
    }

    public function getAssetUrl($type, $file) {
        return THEMES_DIR.basename($this->themePath).'/assets/'.$type.'/'.$file;
    }
}
