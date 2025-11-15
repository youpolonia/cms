<?php

abstract class ContentTemplate {
    protected string $name;
    protected string $description;
    protected array $variables = [];
    protected array $tenantOverrides = [];
    
    public function __construct(
        protected ?int $tenantId = null
    ) {}

    abstract public function getSystemPrompt(): string;
    
    public function applyVariables(array $data): string {
        $template = $this->getSystemPrompt();
        foreach ($this->variables as $var => $default) {
            $value = $data[$var] ?? $default;
            $template = str_replace("{{$var}}", $value, $template);
        }
        return $template;
    }

    public function addTenantOverride(int $tenantId, array $overrides): void {
        $this->tenantOverrides[$tenantId] = $overrides;
    }

    public function getTenantOverride(int $tenantId): ?array {
        return $this->tenantOverrides[$tenantId] ?? null;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }
}
