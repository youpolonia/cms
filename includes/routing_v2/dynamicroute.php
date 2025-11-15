<?php

namespace Includes\RoutingV2;

class DynamicRoute extends Route {
    private array $params = [];
    private string $pattern;

    public function __construct(string $method, string $pattern, $handler) {
        parent::__construct($method, $pattern, $handler);
        $this->pattern = $pattern;
    }

    public function matches(string $method, string $path): bool {
        if ($this->method !== $method) {
            return false;
        }

        $patternParts = explode('/', trim($this->pattern, '/'));
        $pathParts = explode('/', trim($path, '/'));
        
        if (count($patternParts) !== count($pathParts)) {
            return false;
        }

        $this->params = [];
        
        foreach ($patternParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $paramName = trim($part, '{}');
                $this->params[$paramName] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) {
                return false;
            }
        }

        return true;
    }

    public function getParams(): array {
        return $this->params;
    }
}
