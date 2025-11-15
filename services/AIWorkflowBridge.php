<?php

namespace App\Services;

use App\Services\AI\AIService;
use App\Services\WorkflowAutomation;

class AIWorkflowBridge
{
    public function __construct(
        private AIService $aiService,
        private WorkflowAutomation $workflow
    ) {}

    public function registerAITriggers(): void
    {
        $this->workflow->addTrigger('ai_content_generation', 
            fn($context) => $this->validateAIContext($context),
            [$this, 'handleContentGeneration']
        );

        $this->workflow->addTrigger('ai_classification',
            fn($context) => $this->validateAIContext($context),
            [$this, 'handleClassification']
        );

        $this->workflow->addTrigger('ai_moderation',
            fn($context) => $this->validateAIContext($context),
            [$this, 'handleModeration']
        );
    }

    private function validateAIContext(array $context): bool
    {
        return isset($context['provider']) && 
               isset($context['prompt']) &&
               $this->aiService->getProvider($context['provider']);
    }

    public function handleContentGeneration(array $context): void
    {
        $provider = $this->aiService->getProvider($context['provider']);
        $result = $provider->generateContent($context['prompt']);
        $this->workflow->executeManualTrigger('ai_content_result', $result);
    }

    public function handleClassification(array $context): void
    {
        $provider = $this->aiService->getProvider($context['provider']);
        $result = $provider->classifyText($context['text']);
        $this->workflow->executeManualTrigger('ai_classification_result', $result);
    }

    public function handleModeration(array $context): void
    {
        $provider = $this->aiService->getProvider($context['provider']);
        $result = $provider->moderateContent($context['content']);
        $this->workflow->executeManualTrigger('ai_moderation_result', $result);
    }
}
