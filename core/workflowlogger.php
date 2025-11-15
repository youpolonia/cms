<?php
class WorkflowLogger {
    private $logDir = 'storage/workflow_logs';
    private $fallbackLogDir = 'data/fallbacks';

    public function startLog($executionId, $workflowId) {
        $logPath = $this->getLogPath($executionId);
        $logData = [
            'workflow_id' => $workflowId,
            'start_time' => date('c'),
            'status' => 'running',
            'steps' => []
        ];
        
        file_put_contents($logPath, json_encode($logData, JSON_PRETTY_PRINT));
    }

    public function logStep($executionId, $stepId, $result) {
        $logPath = $this->getLogPath($executionId);
        $logData = json_decode(file_get_contents($logPath), true);
        
        $logData['steps'][$stepId] = [
            'timestamp' => date('c'),
            'status' => $result['status'],
            'execution_time' => $result['execution_time'],
            'output_vars' => $result['output_vars'] ?? [],
            'error' => $result['error'] ?? null
        ];

        file_put_contents($logPath, json_encode($logData, JSON_PRETTY_PRINT));
    }

    public function finalizeLog($executionId, $status, $error = null) {
        $logPath = $this->getLogPath($executionId);
        $logData = json_decode(file_get_contents($logPath), true);
        
        $logData['end_time'] = date('c');
        $logData['status'] = $status;
        if ($error) {
            $logData['error'] = $error;
        }

        file_put_contents($logPath, json_encode($logData, JSON_PRETTY_PRINT));
    }

    public function logFallbackEvent($trigger, $attempts, $status, $executionTime, $context = []) {
        $timestamp = date('Y-m-d_His');
        $logPath = $this->getFallbackLogPath($timestamp);
        
        $logData = [
            'timestamp' => date('c'),
            'trigger' => $trigger,
            'attempts' => $attempts,
            'status' => $status,
            'execution_time' => $executionTime,
            'context' => $context,
            'fallback_content_used' => $this->getFallbackContentUsed($trigger, $context),
            'execution_path' => $this->traceExecutionPath()
        ];
        
        file_put_contents($logPath, json_encode($logData, JSON_PRETTY_PRINT));
        
        // Also log to main workflow log if executionId is available
        if (isset($context['executionId'])) {
            $this->logStep($context['executionId'], 'fallback_'.$trigger, [
                'status' => $status,
                'execution_time' => $executionTime,
                'output_vars' => ['fallback_content' => $this->getFallbackContentUsed($trigger, $context)]
            ]);
        }
    }

    private function getFallbackContentUsed($trigger, $context) {
        $contentMap = [
            'post' => 'default_post.txt',
            'page' => 'default_page.txt',
            'error' => 'default_error.html'
        ];
        
        return $contentMap[$trigger] ?? 'unknown';
    }

    private function traceExecutionPath() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $path = [];
        
        foreach ($trace as $call) {
            if (isset($call['class']) && $call['class'] !== 'WorkflowLogger') {
                $path[] = $call['class'].'::'.$call['function'];
            }
        }
        
        return array_reverse($path);
    }

    private function getLogPath($executionId) {
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        return "{$this->logDir}/{$executionId}.json";
    }

    private function getFallbackLogPath($timestamp) {
        if (!file_exists($this->fallbackLogDir)) {
            mkdir($this->fallbackLogDir, 0755, true);
        }
        return "{$this->fallbackLogDir}/fallback_{$timestamp}.json";
    }
}
