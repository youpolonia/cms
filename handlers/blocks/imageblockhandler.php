<?php
require_once __DIR__ . '/../blockhandler.php';

class ImageBlockHandler extends BaseBlockHandler implements ImageBlockHandler {
    public function __construct() {
        parent::__construct('image');
    }

    public function renderEdit(array $blockData): string {
        $src = htmlspecialchars($blockData['src'] ?? '');
        $alt = htmlspecialchars($blockData['alt'] ?? '');
        return <<<HTML
        <div class="image-block-edit">
            <input type="text" name="src" value="{$src}" placeholder="Image URL">
            <input type="text" name="alt" value="{$alt}" placeholder="Alt text">
            <button class="media-picker">Select Image</button>
        </div>
        HTML;
    }

    public function renderPreview(array $blockData): string {
        $src = htmlspecialchars($blockData['src'] ?? '');
        $alt = htmlspecialchars($blockData['alt'] ?? '');
        return <<<HTML
        <div class="image-block-preview">
            <img src="{$src}" alt="{$alt}">
        </div>
        HTML;
    }

    public
 function validateImage(array $imageData): bool {
        return !empty($imageData['src']) && 
               filter_var($imageData['src'], FILTER_VALIDATE_URL);
    }

    public function getThumbnail(array $imageData): string {
        return $imageData['src'] ?? '';
    }
}
