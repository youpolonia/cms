<?php
class WorkflowStepProcessor {
    private $step;
    private $context;
    private $variableSubstitutor;
    private $aiClient;

    public function __construct($step, &$context) {
        $this->step = $step;
        $this->context = &$context;
        $this->variableSubstitutor = new VariableSubstitutor();
        $this->aiClient = new AIClient();
    }

    public function execute() {
        try {
            $startTime = microtime(true);
            $processedInputs = $this->processInputs();
            
            switch ($this->step['type']) {
                case 'condition':
                    $result = $this->processCondition($processedInputs);
                    break;
                case 'ai_action':
                    try {
                        $result = $this->processAIAction($processedInputs);
                    } catch (Exception $e) {
                        if (isset($this->step['fallback'])) {
                            $result = $this->processFallback($e, $processedInputs);
                        } else {
                            throw $e;
                        }
                    }
                    break;
                case 'api_call':
                    $result = $this->processAPICall($processedInputs);
                    break;
                case 'copywriter_step':
                    $result = $this->processCopywriterStep($processedInputs);
                    break;
                case 'metadata_step':
                    $result = $this->processMetadataStep($processedInputs);
                    break;
                case 'translator_step':
                    $result = $this->processTranslatorStep($processedInputs);
                    break;
                case 'ui_step':
                    $result = $this->processUIStep($processedInputs);
                    break;
                default:
                    throw new Exception("Unknown step type: {$this->step['type']}");
            }

            return [
                'status' => 'success',
                'output_vars' => $result,
                'execution_time' => microtime(true) - $startTime
            ];
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'execution_time' => microtime(true) - $startTime
            ];
        }
    }

    private function processInputs() {
        $processed = [];
        foreach ($this->step['inputs'] as $key => $value) {
            $processed[$key] = $this->variableSubstitutor->substitute($value, $this->context);
        }
        return $processed;
    }

    private function processCondition($inputs) {
        // Evaluate condition logic
        $result = $this->safeCompareExpression($inputs['expression']);
        return ['condition_result' => $result !== null ? $result : false];
    }

    private function safeCompareExpression(string $expr): ?bool {
        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*(==|!=|<=|>=|<|>)\s*(\d+(?:\.\d+)?)\s*$/', $expr, $matches)) {
            $left = (float)$matches[1];
            $operator = $matches[2];
            $right = (float)$matches[3];
            
            switch ($operator) {
                case '==': return $left == $right;
                case '!=': return $left != $right;
                case '<': return $left < $right;
                case '<=': return $left <= $right;
                case '>': return $left > $right;
                case '>=': return $left >= $right;
                default: return null;
            }
        }
        error_log('Unsafe expression');
        return null;
    }

    private function processAIAction($inputs) {
        $startTime = microtime(true);
        $timeout = $inputs['timeout'] ?? 30; // Default 30 second timeout
        
        try {
            $response = $this->aiClient->execute(
                $inputs['provider'],
                $inputs['model'],
                $inputs['prompt'],
                $inputs['temperature'] ?? 0.7
            );
            
            // Check for null/empty response
            if (empty($response)) {
                throw new Exception("AI returned empty response");
            }
            
            return ['ai_response' => $response];
        } catch (Exception $e) {
            // Check for timeout
            if (microtime(true) - $startTime >= $timeout) {
                $e = new Exception("AI request timed out after {$timeout} seconds");
            }
            
            if (isset($this->step['fallback'])) {
                return $this->processFallback($e, $inputs);
            }
            throw $e;
        }
    }

    private function processFallback($e, $inputs) {
        $fallback = $this->step['fallback'];
        $this->logFallback($e, $fallback['type']);
        
        switch ($fallback['type']) {
            case 'use_template':
                return $this->handleTemplateFallback($fallback, $inputs);
            case 'use_static':
                return $this->handleStaticFallback($fallback);
            case 'retry':
                return $this->handleRetryFallback($inputs);
            case 'skip':
                return $this->handleSkipFallback();
            default:
                throw new Exception("Unknown fallback type: {$fallback['type']}");
        }
    }
    
    private function logFallback($exception, $fallbackType) {
        $logEntry = sprintf(
            "[%s] Fallback triggered (%s): %s\n",
            date('Y-m-d H:i:s'),
            $fallbackType,
            $exception->getMessage()
        );
        file_put_contents('logs/fallbacks.log', $logEntry, FILE_APPEND);
    }
    
    private function handleTemplateFallback($fallback, $inputs) {
        $templatePath = "data/fallbacks/{$fallback['template']}";
        if (!file_exists($templatePath)) {
            throw new Exception("Fallback template not found: {$templatePath}");
        }
        
        $template = file_get_contents($templatePath);
        $processed = $this->variableSubstitutor->substitute($template, $inputs);
        
        return ['ai_response' => $processed];
    }
    
    private function handleStaticFallback($fallback) {
        return ['ai_response' => $fallback['value']];
    }
    
    private function handleRetryFallback($inputs) {
        sleep(1); // Wait 1 second before retry
        return $this->processAIAction($inputs);
    }
    
    private function handleSkipFallback() {
        return ['ai_response' => null, 'skipped' => true];
    }

    private function processAPICall($inputs) {
        // Implement API call logic
        return ['api_response' => []];
    }

    private function processCopywriterStep($inputs) {
        $prompt = "Generate {$this->step['tone']} {$this->step['length']} copy in {$this->step['style']} style: " .
                 $inputs['content'];
        $response = $this->aiClient->execute(
            $inputs['provider'] ?? 'openai',
            $inputs['model'] ?? 'gpt-3.5-turbo',
            $prompt,
            $inputs['temperature'] ?? 0.7
        );
        return ['copy_response' => $response];
    }

    private function processMetadataStep($inputs) {
        $prompt = "Generate SEO metadata for: {$inputs['content']} " .
                 "Keywords: " . implode(', ', $inputs['keywords']) . " " .
                 "Character limit: {$inputs['character_limit']}";
        $response = $this->aiClient->execute(
            $inputs['provider'] ?? 'openai',
            $inputs['model'] ?? 'gpt-3.5-turbo',
            $prompt,
            $inputs['temperature'] ?? 0.7
        );
        return ['metadata_response' => $response];
    }

    private function processTranslatorStep($inputs) {
        $prompt = "Translate this {$inputs['formality']} text from " .
                 "{$inputs['source_lang']} to {$inputs['target_lang']}: " .
                 $inputs['content'];
        $response = $this->aiClient->execute(
            $inputs['provider'] ?? 'openai',
            $inputs['model'] ?? 'gpt-3.5-turbo',
            $prompt,
            $inputs['temperature'] ?? 0.7
        );
        return ['translation_response' => $response];
    }

    private function processUIStep($inputs) {
        $prompt = "Generate {$inputs['component_type']} UI description " .
                 "following {$inputs['style_guide']}: " .
                 $inputs['requirements'];
        $response = $this->aiClient->execute(
            $inputs['provider'] ?? 'openai',
            $inputs['model'] ?? 'gpt-3.5-turbo',
            $prompt,
            $inputs['temperature'] ?? 0.7
        );
        return ['ui_response' => $response];
    }
}
