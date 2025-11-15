<?php

interface BlockHandler {
    /**
     * Render block for editor interface
     * @return string HTML
     */
    public function renderEdit(): string;

    /**
     * Render block for frontend display
     * @return string HTML
     */
    public function renderPreview(): string;

    /**
     * Prepare block data for storage
     * @return array JSON-serializable data
     */
    public function serialize(): array;

    /**
     * Restore block data from storage
     * @param array $data Serialized data
     */
    public function deserialize(array $data): void;
}
