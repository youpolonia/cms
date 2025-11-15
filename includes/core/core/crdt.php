<?php
namespace CMS\Core;

class CRDT {
    private $state = [];
    private $timestamps = [];

    public function merge(array $remoteState): array {
        foreach ($remoteState as $key => $value) {
            if (!isset($this->state[$key]) || 
                $this->timestamps[$key] < $value['timestamp']) {
                $this->state[$key] = $value['value'];
                $this->timestamps[$key] = $value['timestamp'];
            }
        }
        return $this->getState();
    }

    public function update(string $key, $value): array {
        $timestamp = microtime(true);
        $this->state[$key] = $value;
        $this->timestamps[$key] = $timestamp;
        return $this->getState();
    }

    public function getState(): array {
        $result = [];
        foreach ($this->state as $key => $value) {
            $result[$key] = [
                'value' => $value,
                'timestamp' => $this->timestamps[$key]
            ];
        }
        return $result;
    }
}
