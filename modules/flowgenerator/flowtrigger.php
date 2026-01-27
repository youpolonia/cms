<?php
class FlowTrigger {
    public static function handleEvent(string $eventType, array $context): void {
        $workflows = FlowRegistry::getAllWorkflows();
        
        foreach ($workflows as $workflow) {
            if ($workflow['trigger_event'] === $eventType) {
                self::processWorkflow($workflow, $context);
            }
        }
    }

    private static function processWorkflow(array $workflow, array $context): void {
        $payload = self::buildPayload($workflow, $context);
        FlowSender::sendToN8n($workflow['n8n_webhook'], $payload);
    }

    private static function buildPayload(array $workflow, array $context): array {
        $payload = [];
        
        foreach ($workflow['payload_template'] as $key => $template) {
            $payload[$key] = self::resolveTemplate($template, $context);
        }
        
        return $payload;
    }

    private static function resolveTemplate(string $template, array $context): string {
        return preg_replace_callback('/\{\{(\w+)\}\}/', 
            function($matches) use ($context) {
                return $context[$matches[1]] ?? $matches[0];
            }, 
            $template
        );
    }
}
