<?php
/**
 * AI Content Generation Token Handler
 * Specialized token management for content generation workflows
 */
class AIContentTokenHandler {
    const MODEL_TOKEN_LIMITS = [
        'openai' => 4000,
        'huggingface' => 8000,
        'gemini' => 32000
    ];

    private $modelType;
    private $currentUsage = 0;

    public function __construct(string $modelType) {
        if (!array_key_exists($modelType, self::MODEL_TOKEN_LIMITS)) {
            throw new InvalidArgumentException("Unsupported model type: $modelType");
        }
        $this->modelType = $modelType;
    }

    /**
     * Tracks token usage during content generation
     */
    public function trackUsage(int $inputTokens, int $outputTokens): void {
        $this->currentUsage += ($inputTokens + $outputTokens);
        
        if ($this->exceedsThreshold()) {
            $this->handleThresholdExceeded();
        }
    }

    /**
     * Checks if current usage exceeds safe threshold
     */
    public function exceedsThreshold(): bool {
        $threshold = $this->getThreshold();
        return ($this->currentUsage > ($threshold * 0.75));
    }

    /**
     * Gets current threshold based on model type
     */
    public function getThreshold(): int {
        return self::MODEL_TOKEN_LIMITS[$this->modelType];
    }

    /**
     * Handles threshold exceeded scenario
     */
    private function handleThresholdExceeded(): void {
        $tokenManager = TokenManager::getInstance();
        $tokenManager->triggerEmergencyState();

        // Log specific AI content generation emergency
        file_put_contents(__DIR__.'/../logs/quota_errors.md',
            date('Y-m-d H:i:s')." - AI Content Token Threshold Exceeded for {$this->modelType}\n",
            FILE_APPEND);
    }

    /**
     * Estimates tokens for content generation task
     */
    public static function estimateContentTask(string $content, string $taskType): array {
        $length = strlen($content);
        $multipliers = [
            'generation' => 1.5,
            'scoring' => 0.8,
            'fact_check' => 1.2
        ];

        $estimate = $length * ($multipliers[$taskType] ?? 1.0);
        return [
            'estimate' => ceil($estimate),
            'safe_limit' => self::MODEL_TOKEN_LIMITS['openai'] * 0.75
        ];
    }
}
