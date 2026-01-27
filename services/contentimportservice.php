<?php
/**
 * Content Import Service - Handles importing content from various formats
 */
class ContentImportService {
    private $handlers = [];
    private $validator;
    private $sanitizer;

    public function __construct() {
        $this->validator = new ContentValidator();
        $this->sanitizer = new ContentSanitizer();
    }

    /**
     * Register a new import handler
     */
    public function registerHandler(string $format, ContentImportHandlerInterface $handler): void {
        $this->handlers[$format] = $handler;
    }

    /**
     * Import content from file
     */
    public function import(string $filePath, string $format, int $maxSize = 10485760): array {
        if (!isset($this->handlers[$format])) {
            throw new InvalidArgumentException("No handler registered for format: $format");
        }

        // Security checks
        if (!file_exists($filePath)) {
            throw new RuntimeException("File not found: $filePath");
        }

        $fileSize = filesize($filePath);
        if ($fileSize > $maxSize) {
            throw new RuntimeException("File size exceeds maximum allowed ($maxSize bytes)");
        }

        // Get file content with size check
        $rawContent = file_get_contents($filePath);
        if ($rawContent === false) {
            throw new RuntimeException("Failed to read file: $filePath");
        }

        // Special handling for XML
        if ($format === 'xml') {
            libxml_disable_entity_loader(true);
        }

        $parsed = $this->handlers[$format]->parse($rawContent);
        
        $validated = $this->validator->validate($parsed);
        $sanitized = $this->sanitizer->sanitize($validated);
        
        return $this->reconstructRelationships($sanitized);
    }

    /**
     * Reconstruct content relationships
     */
    private function reconstructRelationships(array $content): array {
        try {
            $relationshipBuilder = new RelationshipBuilder();
            return $relationshipBuilder->process($content);
        } catch (Exception $e) {
            error_log("Relationship reconstruction failed: " . $e->getMessage());
            throw $e;
        }
    }
}

interface ContentImportHandlerInterface {
    public function parse(string $content): array;
}

class ContentValidator {
    public function validate(array $content): array {
        // Basic validation logic
        if (empty($content)) {
            throw new InvalidArgumentException("Empty content");
        }
        return $content;
    }
}

class ContentSanitizer {
    public function sanitize(array $content): array {
        // Basic sanitization
        array_walk_recursive($content, function(&$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        });
        return $content;
    }
}

class RelationshipBuilder {
    public function process(array $content): array {
        // Relationship reconstruction logic
        if (isset($content['relationships'])) {
            // Process relationships
        }
        return $content;
    }
}
