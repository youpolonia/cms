<?php
require_once __DIR__ . '/../blockhandler.php';

class VideoBlockHandler extends BaseBlockHandler implements VideoBlockHandler {
    public function __construct() {
        parent::__construct('video');
    }

    public function renderEdit(array $blockData): string {
        $url = htmlspecialchars($blockData['url'] ?? '');
        $caption = htmlspecialchars($blockData['caption'] ?? '');
        return <<<HTML
        <div class="video-block-edit">
            <input type="text" name="url" value="{$url}" placeholder="Video URL">
            <input type="text" name="caption" value="{$caption}" placeholder="Caption">
            <button class="media-picker">Select Video</button>
        </div>
        HTML;
    }

    public function renderPreview(array $blockData): string {
        $url = htmlspecialchars($blockData['url'] ?? '');
        $caption = htmlspecialchars($blockData['caption'] ?? '');
        $embedCode = $this->getEmbedCode($blockData);
        return <<<HTML
        <div class="video-block-preview">
            {$embedCode}
            <p class="video-caption">{$caption}</p>
        </div>
        HTML;
    }

    public
 function validateVideo(array $videoData): bool {
        return !empty($videoData['url']) && 
               filter_var($videoData['url'], FILTER_VALIDATE_URL);
    }

    public function getEmbedCode(array $videoData): string {
        $url = htmlspecialchars($videoData['url'] ?? '');
        // Simple iframe embed - would be extended for different providers
        return <<<HTML
        <iframe src="{$url}" frameborder="0" allowfullscreen></iframe>
        HTML;
    }
}
