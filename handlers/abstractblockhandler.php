<?php

require_once __DIR__ . '/blockhandler.php';

abstract class AbstractBlockHandler implements BlockHandler {
    protected string $id;
    protected int $position = 0;
    protected array $data = [];

    public function __construct(string $id, int $position = 0) {
        $this->id = $id;
        $this->position = $position;
    }

    public function renderEdit(): string {
        return '
<div class="block-editor" data-block-id="' . htmlspecialchars(
$this->id) . '">' . 
               $this->renderEditContent() . 
               '
</div>';
    }

    public
 function renderPreview(): string {
        return '
<div class="block-preview" data-block-id="' . htmlspecialchars(
$this->id) . '">' . 
               $this->renderPreviewContent() . 
               '
</div>';
    }

    public
 function serialize(): array {
        return [
            'type' => $this->getType(),
            'data' => $this->data,
            'meta' => [
                'id' => $this->id,
                'position' => $this->position
            ]
        ];
    }

    public function deserialize(array $data): void {
        $this->data = $data['data'] ?? [];
        $this->id = $data['meta']['id'] ?? uniqid();
        $this->position = $data['meta']['position'] ?? 0;
    }

    abstract protected function getType(): string;
    abstract protected function renderEditContent(): string;
    abstract protected function renderPreviewContent(): string;
}
