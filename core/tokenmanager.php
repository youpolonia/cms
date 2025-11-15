<?php
/**
 * Enhanced Token Management for AI Content Generation
 * Implements safeguards from phase11_plan.md
 */
class TokenManager {
    private static $instance;
    private $currentUsage = 0;
    private $fallbackActive = false;
    private $emergencyState = false;

    // Prevent direct instantiation
    private function __construct() {}

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Estimates token usage for content generation task
     */
    public static function estimateTaskTokens(string $taskType, int $contentLength): array {
        $estimates = [
            'input' => 0,
            'output' => 0,
            'overhead' => 0
        ];

        switch($taskType) {
            case 'content_generation':
                $estimates['input'] = ceil($contentLength * 1.2);
                $estimates['output'] = ceil($contentLength * 1.5);
                $estimates['overhead'] = 1500;
                break;
            case 'quality_scoring':
                $estimates['input'] = ceil($contentLength * 0.8);
                $estimates['output'] = 500;
                $estimates['overhead'] = 800;
                break;
            default:
                $estimates['input'] = $contentLength;
                $estimates['output'] = $contentLength;
                $estimates['overhead'] = 1000;
        }

        return $estimates;
    }

    /**
     * Checks if task can proceed based on token estimates
     */
    public static function canProceed(array $estimates): bool {
        $total = array_sum($estimates);
        $threshold = self::getTokenThreshold();

        return ($total < ($threshold * 0.75));
    }

    /**
     * Implements automatic chunking for large content
     */
    public static function chunkContent(string $content, int $maxChunkSize = 5000): array {
        $chunks = [];
        $length = strlen($content);
        $numChunks = ceil($length / $maxChunkSize);

        for ($i = 0; $i < $numChunks; $i++) {
            $offset = $i * $maxChunkSize;
            $chunks[] = substr($content, $offset, $maxChunkSize);
        }

        return $chunks;
    }

    /**
     * Gets current token threshold based on system state
     */
    private static function getTokenThreshold(): int {
        // Default threshold can be overridden in config
        return defined('TOKEN_THRESHOLD') ? TOKEN_THRESHOLD : 30000;
    }

    /**
     * Emergency procedures for token overflow
     */
    public function triggerEmergencyState(): void {
        $this->emergencyState = true;
        // Log to emergency state file
        file_put_contents(__DIR__.'/../logs/emergency_state.md',
            date('Y-m-d H:i:s')." - Token emergency triggered\n", FILE_APPEND);
    }
}
