<?php
/**
 * Asset Manager for Theme System
 */
class AssetManager {
    protected $themePath;
    protected $assets = [
        'css' => [],
        'js' => []
    ];

    public function __construct($themePath) {
        $this->themePath = $themePath;
    }

    public function addCss($file) {
        $this->assets['css'][] = $file;
    }

    public function addJs($file) {
        $this->assets['js'][] = $file;
    }

    public function renderCss() {
        $output = '';
        foreach ($this->assets['css'] as $css) {
            $output .= '
<link rel="stylesheet" href="'.
$this->getAssetUrl($css).'">';
        }
        return $output;
    }

    public function renderJs() {
        $output = '';
        foreach ($this->assets['js'] as $js) {
            $output .= '
<script src="'.
$this->getAssetUrl($js).'"></script>';
        }
        return $output;
    }

    protected function getAssetUrl($file) {
        return THEMES_DIR . basename($this->themePath) . '/assets/' . ltrim($file, '/');
    }
}
