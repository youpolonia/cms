<?php
require_once __DIR__ . '/../blockhandler.php';

class TextBlockHandler extends BaseBlockHandler implements TextBlockHandler {
    public function __construct() {
        parent::__construct('text');
    }

    public function renderEdit(array $blockData): string {
        $content = htmlspecialchars($blockData['content'] ?? '');
        return <<<HTML
        <div class="text-block-edit">
            <textarea name="content">{$content}</textarea>
        </div>
        HTML;
    }

    public function renderPreview(array $blockData): string {
        $content = nl2br(htmlspecialchars($blockData['content'] ?? ''));
        return <<<HTML
        <div class="text-block-preview">{$content}</div>
        HTML;
    }

    public function validateText(string $text): bool {
        return mb_strlen($text) <= 5000; // Max 5000 chars
    }
}
