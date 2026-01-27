<?php
/**
 * Block Registry - Manages available block types
 */
class BlockRegistry {
    private static $instance = null;
    private $handlers = [];

    private function __construct() {
        // Register default block handlers
        $this->register('text', new TextBlockHandler());
        $this->register('image', new ImageBlockHandler());
        $this->register('video', new VideoBlockHandler());
    }

    public static function getInstance(): BlockRegistry {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(string $type, BlockHandler $handler): void {
        $this->handlers[$type] = $handler;
    }

    public function getHandler(string $type): ?BlockHandler {
        return $this->handlers[$type] ?? null;
    }

    public function getSupportedTypes(): array {
        return array_keys($this->handlers);
    }

    public function renderEdit(string $type, array $blockData): string {
        if ($handler = $this->getHandler($type)) {
            return $handler->renderEdit($blockData);
        }
        return '';
    }

    public function renderPreview(string $type, array $blockData): string {
        if ($handler = $this->getHandler($type)) {
            return $handler->renderPreview($blockData);
        }
        return '';
    }
}
