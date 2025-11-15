<?php
namespace Core;

class Response
{
    public function __construct(
        public readonly string $content,
        public readonly int $status = 200,
        public readonly array $headers = [],
        private readonly ?SecurityService $securityService = null
    ) {}

    public function send(): void
    {
        http_response_code($this->status);
        
        // Add security headers if service is available
        $response = $this;
        if ($this->securityService) {
            $response = $this->securityService->applySecurityHeaders($this);
        }

        // Add configured headers
        foreach ($response->headers as $name => $value) {
            header("$name: $value");
        }

        echo $response->content;
    }

    public function withHeader(string $name, string $value): self
    {
        $headers = $this->headers;
        $headers[$name] = $value;
        return new self(
            $this->content,
            $this->status,
            $headers,
            $this->securityService
        );
    }
}
