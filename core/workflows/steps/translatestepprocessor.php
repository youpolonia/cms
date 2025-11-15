<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/../../translationservice.php';
require_once __DIR__ . '/../workflowstepprocessor.php';

class TranslateStepProcessor extends WorkflowStepProcessor {
    public function process(array $step, array $context): array {
        $translationService = new TranslationService();
        
        $text = $context[$step['input_var'] ?? ''] ?? '';
        $targetLang = $step['target_lang'] ?? 'en';
        
        if (empty($text)) {
            throw new InvalidArgumentException('No input text provided for translation');
        }

        $translated = $translationService->translate(
            $text,
            $targetLang
        );

        return [
            $step['output_var'] => $translated
        ];
    }

    public function getSupportedType(): string {
        return 'ai-translate';
    }
}
