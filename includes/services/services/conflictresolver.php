<?php
namespace CMS\Services;

use CMS\Core\CRDT;

class ConflictResolver {
    private $crdt;

    public function __construct() {
        $this->crdt = new CRDT();
    }

    public function resolveConflicts(array $localState, array $remoteState): array {
        // First merge remote state
        $merged = $this->crdt->merge($remoteState);
        
        // Then apply local changes
        foreach ($localState as $key => $value) {
            $merged = $this->crdt->update($key, $value);
        }
        
        return $merged;
    }

    public function getCurrentState(): array {
        return $this->crdt->getState();
    }

    public function reset(): void {
        $this->crdt = new CRDT();
    }
}
