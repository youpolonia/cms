<?php
/**
 * API Request/Response Transformer
 * 
 * Handles tenant-specific transformations for API requests and responses
 */

class ApiTransformer {
    private $tenantId;
    private $transformations = [];

    public function __construct($tenantId) {
        $this->tenantId = $tenantId;
        $this->loadTransformations();
    }

    public function transformRequest(array $request) {
        foreach ($this->transformations['request'] as $transformer) {
            $request = $transformer->transform($request);
        }
        return $request;
    }

    public function transformResponse(array $response) {
        foreach ($this->transformations['response'] as $transformer) {
            $response = $transformer->transform($response);
        }
        return $response;
    }

    private function loadTransformations() {
        // Default transformations
        $this->transformations = [
            'request' => [
                new FieldNameTransformer(),
                new DataTypeTransformer()
            ],
            'response' => [
                new FieldNameTransformer(true),
                new DataFormatTransformer()
            ]
        ];

        // Tenant-specific overrides
        $tenantFile = __DIR__ . "/transformations/{$this->tenantId}.php";
        if (file_exists($tenantFile)) {
            $tenantTransformations = require_once $tenantFile;
            $this->transformations = array_merge_recursive(
                $this->transformations,
                $tenantTransformations
            );
        }
    }
}

interface TransformerInterface {
    public function transform(array $data): array;
}

class FieldNameTransformer implements TransformerInterface {
    private $reverse;

    public function __construct($reverse = false) {
        $this->reverse = $reverse;
    }

    public function transform(array $data): array {
        $mappings = $this->getFieldMappings();
        $result = [];

        foreach ($data as $key => $value) {
            $newKey = $this->reverse 
                ? array_search($key, $mappings) 
                : ($mappings[$key] ?? $key);
            
            if ($newKey !== false) {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    protected function getFieldMappings(): array {
        return [
            'user_id' => 'userId',
            'created_at' => 'createdAt'
        ];
    }
}

class DataTypeTransformer implements TransformerInterface {
    public function transform(array $data): array {
        foreach ($data as $key => $value) {
            if (is_numeric($value) && strpos($key, 'id') !== false) {
                $data[$key] = (int)$value;
            }
        }
        return $data;
    }
}

class DataFormatTransformer implements TransformerInterface {
    public function transform(array $data): array {
        foreach ($data as $key => $value) {
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                $data[$key] = date('Y-m-d\TH:i:s\Z', strtotime($value));
            }
        }
        return $data;
    }
}
