<?php
/**
 * Workflow History Tracking System
 * JSON-based state transition persistence
 */
class WorkflowHistory {
    /**
     * MIGRATION VERSION SYSTEM
     *
     * Version Format: 00XX (4-digit zero-padded)
     * Dependency Chain:
     * - Base Workflows (0001)
     * → Workflow Steps (0010)
     * → Transitions (0016)
     * → Webhooks (0017)
     * → Variable History (0018)
     * → Schedules (0019)
     *
     * Version Locking Rules:
     * 1. Parent versions must exist before child versions
     * 2. Circular dependencies are prohibited
     * 3. Schema changes require new minor versions
     */
    const MIGRATION_SEQUENCE = [
        16 => 'Migration_CreateWorkflowTransitionsTable',
        17 => 'Migration_CreateWorkflowWebhooksTable',
        18 => 'Migration_CreateWorkflowVariableHistoryTable',
        19 => 'Migration_CreateWorkflowSchedulesTable'
    ];

    const STORAGE_PATH = __DIR__ . '/workflow_history.json';
    
    private static $instance;
    private $history = [];

    private function __construct() {
        $this->loadHistory();
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addEntry(string $workflowId, string $fromState, string $toState): void {
        $entry = [
            'timestamp' => (new DateTime())->format(DateTime::ATOM),
            'workflow_id' => $workflowId,
            'from_state' => $fromState,
            'to_state' => $toState
        ];

        $this->history[] = $entry;
        $this->saveToFile();
    }

    public function getHistory(?string $workflowId = null, ?DateTime $from = null, ?DateTime $to = null, ?string $state = null): array {
        $filtered = $this->history;

        if ($workflowId) {
            $filtered = array_filter($filtered, fn($e) => $e['workflow_id'] === $workflowId);
        }

        if ($from || $to) {
            $filtered = array_filter($filtered, function($e) use ($from, $to) {
                $ts = new DateTime($e['timestamp']);
                return (!$from || $ts >= $from) && (!$to || $ts <= $to);
            });
        }

        if ($state) {
            $filtered = array_filter($filtered, fn($e) => $e['from_state'] === $state || $e['to_state'] === $state);
        }

        return $filtered;
    }

    private function loadHistory(): void {
        if (file_exists(self::STORAGE_PATH)) {
            $content = file_get_contents(self::STORAGE_PATH);
            $this->history = json_decode($content, true) ?: [];
        }
    }

    private function saveToFile(): void {
        require_once __DIR__ . '/../../../core/tmp_sandbox.php';
        $tempPath = tempnam(cms_tmp_dir(), 'wfhist');
        file_put_contents($tempPath, json_encode($this->history, JSON_PRETTY_PRINT));
        rename($tempPath, self::STORAGE_PATH);
    }
}
