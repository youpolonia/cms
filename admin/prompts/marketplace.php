<?php
require_once __DIR__ . '/../../core/remotefetcher.php';
require_once __DIR__ . '/../../core/promptimporter.php';

class PromptMarketplace {
    const MARKETPLACE_URL = 'https://marketplace.example.com/api/v1/prompts';

    public static function render(): string {
        try {
            $prompts = RemoteFetcher::fetchIndex(self::MARKETPLACE_URL);
            $html = '<div class="prompt-marketplace">';
            $html .= '<h2>Prompt Marketplace</h2>';
            $html .= '<div class="prompt-grid">';

            foreach ($prompts['prompts'] as $prompt) {
                $html .= self::renderPromptCard($prompt);
            }

            $html .= '</div></div>';
            return $html;
        } catch (Exception $e) {
            return '<div class="alert alert-error">Error loading marketplace: ' .
                   htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    private static function renderPromptCard(array $prompt): string {
        return '
<div class="prompt-card" data-id="' . htmlspecialchars($prompt['id']) . '">
            <h3>' . htmlspecialchars($prompt['name']) . '</h3>
            <p>' . htmlspecialchars($prompt['description']) . '</p>
            <div class="prompt-meta">
                <span>Version: ' . htmlspecialchars($prompt['version']) . '</span>
                <button class="btn-import"
                        onclick="importPrompt(\'' . htmlspecialchars($prompt['id']) . '\')">
                    Import
                </button>
            </div>
        </div>';
    }

    public static function handleAjax(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $promptId = $data['id'] ?? '';
            $content = RemoteFetcher::fetchPrompt($data['url'] ?? '');

            if (PromptImporter::importPrompt($promptId, $content)) {
                echo json_encode(['success' => true]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}
