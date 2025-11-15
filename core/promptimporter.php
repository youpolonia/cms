<?php
class PromptImporter {
    const PROMPTS_DIR = '/data/prompts/';
    const WORKFLOWS_DIR = '/data/workflow_templates/';

    public static function importPrompt(string $id, string $content): bool {
        if (!is_dir(self::PROMPTS_DIR)) {
            mkdir(self::PROMPTS_DIR, 0755, true);
        }

        require_once __DIR__ . '/tmp_sandbox.php';
        $tempFile = tempnam(cms_tmp_dir(), 'prompt_');
        file_put_contents($tempFile, $content);

        $targetFile = self::PROMPTS_DIR . $id . '.prompt';
        if (!rename($tempFile, $targetFile)) {
            unlink($tempFile);
            throw new Exception("Failed to save prompt file");
        }

        return true;
    }

    public static function importWorkflow(string $id, string $content): bool {
        if (!is_dir(self::WORKFLOWS_DIR)) {
            mkdir(self::WORKFLOWS_DIR, 0755, true);
        }

        require_once __DIR__ . '/tmp_sandbox.php';
        $tempFile = tempnam(cms_tmp_dir(), 'workflow_');
        file_put_contents($tempFile, $content);
        
        $targetFile = self::WORKFLOWS_DIR . $id . '.json';
        if (!rename($tempFile, $targetFile)) {
            unlink($tempFile);
            throw new Exception("Failed to save workflow template");
        }

        return true;
    }

    public static function validatePrompt(string $content): bool {
        $data = json_decode($content, true);
        return json_last_error() === JSON_ERROR_NONE && isset($data['id']);
    }
}
