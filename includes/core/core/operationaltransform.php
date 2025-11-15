<?php
namespace CMS\Core;

class OperationalTransform {
    public function transform(array $operation1, array $operation2): array {
        // Handle concurrent operations
        if ($operation1['type'] === 'insert' && $operation2['type'] === 'insert') {
            return $this->transformInserts($operation1, $operation2);
        } elseif ($operation1['type'] === 'delete' && $operation2['type'] === 'delete') {
            return $this->transformDeletes($operation1, $operation2);
        } else {
            return $this->transformMixed($operation1, $operation2);
        }
    }

    private function transformInserts(array $op1, array $op2): array {
        if ($op1['position'] < $op2['position']) {
            return $op1;
        } elseif ($op1['position'] > $op2['position']) {
            return ['type' => 'insert', 'position' => $op1['position'] + strlen($op2['text']), 'text' => $op1['text']];
        } else {
            // Tie-breaker using client ID
            return $op1['clientId'] < $op2['clientId'] ? $op1 : $op2;
        }
    }

    private function transformDeletes(array $op1, array $op2): array {
        // Deletes are idempotent - no transformation needed
        return $op1;
    }

    private function transformMixed(array $op1, array $op2): array {
        if ($op1['type'] === 'insert' && $op2['type'] === 'delete') {
            if ($op1['position'] <= $op2['position']) {
                return $op1;
            } else {
                return ['type' => 'insert', 'position' => $op1['position'] - strlen($op2['text']), 'text' => $op1['text']];
            }
        } else {
            // op1 is delete, op2 is insert
            if ($op1['position'] < $op2['position']) {
                return $op1;
            } else {
                return ['type' => 'delete', 'position' => $op1['position'] + strlen($op2['text']), 'text' => $op1['text']];
            }
        }
    }
}
