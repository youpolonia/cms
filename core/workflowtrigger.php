<?php
/**
 * Base Workflow Trigger Class
 */
class WorkflowTrigger {
    protected string $type;
    protected array $conditions = [];
    protected array $config = [];

    public function __construct(array $config = []) {
        $this->config = $config;
        $this->type = $config['type'] ?? 'base';
        $this->conditions = $config['conditions'] ?? [];
    }

    public function validate(): bool {
        return !empty($this->type);
    }

    public function execute(): void {
        if (!$this->validate()) {
            throw new Exception('Invalid trigger configuration');
        }
    }

    public function toArray(): array {
        return [
            'type' => $this->type,
            'conditions' => $this->conditions,
            'config' => $this->config
        ];
    }
}

/**
 * System Event Trigger
 */
class SystemEventTrigger extends WorkflowTrigger {
    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->type = 'system_event';
    }

    public function validate(): bool {
        return parent::validate() && 
               isset($this->config['event_name']);
    }

    public function execute(): void {
        parent::execute();
        // Implementation for system event trigger
    }
}

/**
 * Scheduled Trigger
 */
class ScheduledTrigger extends WorkflowTrigger {
    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->type = 'scheduled';
    }

    public function validate(): bool {
        return parent::validate() && 
               isset($this->config['schedule_type']) && 
               in_array($this->config['schedule_type'], ['daily', 'weekly', 'interval']);
    }
}

/**
 * Webhook Trigger
 */
class WebhookTrigger extends WorkflowTrigger {
    public function __construct(array $config = []) {
        parent::__construct($config);
        $this->type = 'webhook';
    }

    public function validate(): bool {
        return parent::validate() && 
               isset($this->config['webhook_token']);
    }
}
